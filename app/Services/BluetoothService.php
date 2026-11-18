<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class BluetoothService
{
    private bool $available;

    public function __construct()
    {
        $this->available = $this->checkBlueZ();
    }

    public function isAvailable(): bool
    {
        return $this->available;
    }

    public function scanDevices(): array
    {
        if (! $this->available) {
            return [];
        }

        $devices = [];

        try {
            // Activate discovery mode
            $this->run(['bluetoothctl', 'scan', 'on'], 3);
            sleep(12);
            $this->run(['bluetoothctl', 'scan', 'off'], 3);

            $output = $this->run(['bluetoothctl', 'devices']);
            $lines = array_filter(explode("\n", $output));

            foreach ($lines as $line) {
                $line = trim($line);
                if (preg_match('/^Device\s+([A-F0-9:]{17})\s+(.+)$/i', $line, $matches)) {
                    $mac = $matches[1];
                    $name = $matches[2];
                    $info = $this->getDeviceInfo($mac);

                    if ($this->isPrinterDevice($mac, $name, $info)) {
                        $devices[] = [
                            'mac' => $mac,
                            'name' => $name,
                            'paired' => $info['paired'] ?? false,
                            'connected' => $info['connected'] ?? false,
                            'rfcomm' => $this->findRfcommForMac($mac),
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Bluetooth scan failed', ['error' => $e->getMessage()]);
        }

        return $devices;
    }

    public function getDeviceInfo(string $mac): array
    {
        $info = [
            'paired' => false,
            'trusted' => false,
            'connected' => false,
            'class' => null,
        ];

        if (! $this->available) {
            return $info;
        }

        try {
            $output = $this->run(['bluetoothctl', 'info', $mac]);

            if (str_contains($output, 'Paired: yes')) {
                $info['paired'] = true;
            }
            if (str_contains($output, 'Trusted: yes')) {
                $info['trusted'] = true;
            }
            if (str_contains($output, 'Connected: yes')) {
                $info['connected'] = true;
            }
            if (preg_match('/Class:\s*(0x[0-9A-Fa-f]+)/', $output, $classMatch)) {
                $info['class'] = $classMatch[1];
            }
        } catch (\Exception $e) {
            Log::warning('Failed to get device info', ['mac' => $mac, 'error' => $e->getMessage()]);
        }

        return $info;
    }

    public function pair(string $mac, ?string $pin = null): array
    {
        if (! $this->available) {
            return ['success' => false, 'error' => 'BlueZ no está instalado en el servidor'];
        }

        try {
            // Configure agent
            $this->run(['bluetoothctl', 'agent', 'on'], 5);
            $this->run(['bluetoothctl', 'default-agent'], 5);

            if ($pin) {
                // Pair with PIN using interactive process
                $process = new Process(['bluetoothctl', 'pair', $mac]);
                $process->setTimeout(30);
                $process->start();

                // Wait for PIN prompt, then send PIN
                usleep(2000000); // 2 seconds
                $process->write($pin."\n");
                $process->wait();

                if (! $process->isSuccessful()) {
                    throw new ProcessFailedException($process);
                }
            } else {
                $this->run(['bluetoothctl', 'pair', $mac], 30);
            }

            $this->run(['bluetoothctl', 'trust', $mac], 10);
            $this->run(['bluetoothctl', 'connect', $mac], 15);

            $rfcommPath = $this->bindRfcomm($mac);

            return [
                'success' => true,
                'rfcomm' => $rfcommPath,
            ];
        } catch (ProcessFailedException $e) {
            Log::error('Bluetooth pair failed', ['mac' => $mac, 'error' => $e->getMessage()]);

            return ['success' => false, 'error' => 'Error al emparejar: '.$e->getMessage()];
        }
    }

    public function bindRfcomm(string $mac): ?string
    {
        $existingRfcomm = $this->findRfcommForMac($mac);
        if ($existingRfcomm) {
            return $existingRfcomm;
        }

        $nextChannel = $this->getNextRfcommChannel();

        try {
            $this->run(['rfcomm', 'bind', "/dev/rfcomm{$nextChannel}", $mac], 10);

            return "/dev/rfcomm{$nextChannel}";
        } catch (ProcessFailedException $e) {
            Log::error('RFCOMM bind failed', ['mac' => $mac, 'error' => $e->getMessage()]);

            return null;
        }
    }

    public function unbindRfcomm(int $channel): bool
    {
        try {
            $this->run(['rfcomm', 'release', "/dev/rfcomm{$channel}"], 10);

            return true;
        } catch (ProcessFailedException $e) {
            Log::warning('RFCOMM unbind failed', ['channel' => $channel, 'error' => $e->getMessage()]);

            return false;
        }
    }

    public function listBoundDevices(): array
    {
        $devices = [];
        $paths = glob('/dev/rfcomm*');

        if ($paths === false) {
            return [];
        }

        foreach ($paths as $path) {
            $channel = (int) basename($path, 'rfcomm');
            $devices[] = [
                'path' => $path,
                'channel' => $channel,
            ];
        }

        return $devices;
    }

    private function findRfcommForMac(string $mac): ?string
    {
        $mac = strtolower($mac);
        $output = '';

        try {
            $output = $this->run(['rfcomm', 'show'], 5);
        } catch (\Exception $e) {
            return null;
        }

        $lines = explode("\n", $output);
        $currentDevice = null;
        $currentMac = null;

        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match('/^rfcomm(\d+)/', $line, $channelMatch)) {
                if ($currentMac && strtolower($currentMac) === $mac) {
                    return $currentDevice;
                }
                $currentDevice = '/dev/rfcomm'.$channelMatch[1];
                $currentMac = null;
            }
            if (preg_match('/([A-F0-9:]{17})/i', $line, $macMatch)) {
                $currentMac = $macMatch[1];
            }
        }

        if ($currentMac && strtolower($currentMac) === $mac) {
            return $currentDevice;
        }

        return null;
    }

    private function getNextRfcommChannel(): int
    {
        $bound = $this->listBoundDevices();
        $used = array_map(fn ($d) => $d['channel'], $bound);

        for ($i = 0; $i < 10; $i++) {
            if (! in_array($i, $used)) {
                return $i;
            }
        }

        return 0;
    }

    private function isPrinterDevice(string $mac, string $name, array $info): bool
    {
        $printerKeywords = [
            'printer', 'print', 'pos', 'receipt', 'ticket',
            'epson', 'bixolon', 'star', 'citizen', 'zebra',
            'imprimir', 'impresora', 'termica', 'thermal',
            'tm-t', 'tm-p', 'sp-', 'mc-',
        ];

        $nameLower = strtolower($name);
        foreach ($printerKeywords as $keyword) {
            if (str_contains($nameLower, $keyword)) {
                return true;
            }
        }

        // Check Bluetooth device class: major class 0x06 = Printer
        if ($info['class']) {
            $classHex = hexdec(substr($info['class'], 2));
            $majorClass = ($classHex >> 8) & 0x1F;
            if ($majorClass === 0x06) {
                return true;
            }
        }

        return false;
    }

    private function checkBlueZ(): bool
    {
        try {
            $this->run(['which', 'bluetoothctl'], 5);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function run(array $command, int $timeout = 10): string
    {
        $process = new Process($command);
        $process->setTimeout($timeout);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process->getOutput();
    }
}
