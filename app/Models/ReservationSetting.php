<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationSetting extends Model
{
    protected $table = 'reservation_settings';

    protected $fillable = [
        'intervalo_minutos',
        'max_personas',
    ];

    public static function getInstance()
    {
        $instance = self::firstOrCreate([], [
            'intervalo_minutos' => 15,
            'max_personas' => 20,
        ]);
        $instance->initDays();

        return $instance;
    }

    public function daySettings()
    {
        return $this->hasMany(ReservationDaySetting::class);
    }

    public function initDays(): void
    {
        foreach (range(1, 7) as $dia) {
            $this->daySettings()->firstOrCreate(
                ['dia' => $dia],
                [
                    'activo' => true,
                    'hora_apertura_dia' => '12:00',
                    'hora_cierre_dia' => '16:00',
                    'hora_apertura_noche' => '20:00',
                    'hora_cierre_noche' => '23:00',
                ]
            );
        }

        $this->load('daySettings');
    }

    public function getDaySettings(int $day): ?ReservationDaySetting
    {
        return $this->daySettings->firstWhere('dia', $day);
    }

    public function generateTimeSlots(?int $day = null): array
    {
        if ($day !== null) {
            $daySettings = $this->getDaySettings($day);
            if (! $daySettings || ! $daySettings->activo) {
                return [];
            }

            return $daySettings->generateTimeSlots((int) $this->intervalo_minutos);
        }

        $allSlots = [];
        for ($d = 1; $d <= 7; $d++) {
            $slots = $this->generateTimeSlots($d);
            $allSlots = array_merge($allSlots, $slots);
        }

        return array_values(array_unique($allSlots));
    }

    public function getTimeSlotGroups(?int $day = null): array
    {
        if ($day !== null) {
            $daySettings = $this->getDaySettings($day);
            if (! $daySettings || ! $daySettings->activo) {
                return [];
            }

            return $daySettings->getTimeSlotGroups((int) $this->intervalo_minutos);
        }

        $allGroups = [];
        for ($d = 1; $d <= 7; $d++) {
            $groups = $this->getTimeSlotGroups($d);
            $allGroups = array_merge($allGroups, $groups);
        }

        return $allGroups;
    }

    public function isDiaActivo(int $dayNumber): bool
    {
        $daySettings = $this->getDaySettings($dayNumber);

        return $daySettings && $daySettings->activo;
    }

    public static function getDiasSemana(): array
    {
        return [
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
            7 => 'Domingo',
        ];
    }
}
