<?php

namespace App\Services;

use App\Models\MercadoPagoSetting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MercadoPagoService
{
    private const API_URL = 'https://api.mercadopago.com/v1/payments/search';

    private const CACHE_SECONDS = 60;

    /** Dominios del checkout/dash de MP según el site_id de la cuenta. */
    private const DOMINIOS = [
        'MLA' => 'mercadopago.com.ar',
        'MLB' => 'mercadopago.com.br',
        'MLC' => 'mercadopago.cl',
        'MCO' => 'mercadopago.com.co',
        'MEC' => 'mercadopago.com.mx',
        'MPE' => 'mercadopago.com.pe',
        'MPT' => 'mercadopago.pt',
        'MRU' => 'mercadopago.com.uy',
    ];

    /** Etiquetas legibles por tipo de pago de Mercado Pago. */
    private const TIPOS = [
        'bank_transfer' => 'Transferencia',
        'account_money' => 'Dinero en cuenta',
        'credit_card' => 'Tarjeta de crédito',
        'debit_card' => 'Tarjeta de débito',
        'prepaid_card' => 'Tarjeta prepaga',
        'ticket' => 'Pago en efectivo',
        'atm' => 'Cajero automático',
        'digital_currency' => 'Moneda digital',
        'digital_wallet' => 'Billetera virtual',
    ];

    /**
     * Últimos movimientos de la cuenta Mercado Pago, filtrados por los
     * estados configurados. Respuesta cacheada 60 s para no golpear la
     * API con el polling en tiempo real de /pedidos.
     *
     * @return array<int, array<string, mixed>>
     */
    public function movimientos(?MercadoPagoSetting $settings = null): array
    {
        $settings ??= MercadoPagoSetting::getInstance();

        if (! $settings->access_token) {
            return [];
        }

        $limite = max(1, min($settings->limite ?? 50, 100));
        $estados = array_values($settings->estados ?? []);
        $cacheKey = 'mp.movimientos.'.md5($settings->access_token.'|'.$limite.'|'.implode(',', $estados));

        $pagos = Cache::remember($cacheKey, self::CACHE_SECONDS, function () use ($settings, $limite, $estados) {
            try {
                $respuesta = Http::withToken($settings->access_token)
                    ->timeout(10)
                    ->get(self::API_URL, [
                        'criteria' => 'desc',
                        'limit' => $limite,
                    ]);

                if (! $respuesta->ok()) {
                    Log::warning('Mercado Pago API respondió '.$respuesta->status().': '.$respuesta->body());

                    return [];
                }

                return collect($respuesta->json('results', []))
                    ->filter(fn (array $pago) => empty($estados) || in_array($pago['status'] ?? '', $estados, true))
                    ->values()
                    ->all();
            } catch (\Throwable $e) {
                Log::warning('Error consultando Mercado Pago: '.$e->getMessage());

                return [];
            }
        });

        // La normalización se hace fuera del cache: sólo se guardan arrays
        // escalares, ya que Laravel 13 no permite deserializar objetos (Carbon).
        return collect($pagos)
            ->map(fn (array $pago) => $this->normalizar($pago))
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $pago
     * @return array<string, mixed>
     */
    private function normalizar(array $pago): array
    {
        $estado = (string) ($pago['status'] ?? '');
        $monto = (float) ($pago['transaction_amount'] ?? 0);
        $metodo = trim(($pago['payment_method_id'] ?? '').' '.($pago['payment_type_id'] ?? ''));
        $tipoPago = (string) ($pago['payment_type_id'] ?? '');
        $moneda = (string) ($pago['currency_id'] ?? 'ARS');

        [$pagador, $pagadorEmail, $pagadorDocumento] = $this->datosPagador($pago);

        return [
            'id' => $pago['id'] ?? null,
            'fecha' => Carbon::parse($pago['date_created'] ?? now()),
            'estado' => $estado,
            'estado_label' => MercadoPagoSetting::ESTADOS[$estado] ?? ucfirst($estado),
            'metodo' => $metodo !== '' ? $metodo : '—',
            'metodo_label' => self::TIPOS[$tipoPago] ?? ($metodo !== '' ? ucfirst($metodo) : '—'),
            'pagador' => $pagador,
            'pagador_email' => $pagadorEmail,
            'pagador_documento' => $pagadorDocumento,
            'tipo' => in_array($estado, ['refunded', 'charged_back'], true) || (float) ($pago['transaction_amount_refunded'] ?? 0) > 0
                ? 'egreso'
                : 'ingreso',
            'descripcion' => $pago['description'] ?? ($pago['external_reference'] ?? '—'),
            'monto' => $monto,
            'moneda' => $moneda,
            'detalle_url' => $this->urlDetalle($pago),
        ];
    }

    /**
     * Nombre, email y documento del pagador. Mercado Pago no siempre informa
     * nombre y apellido (p. ej. en transferencias), por eso se usa el nombre
     * del titular de la tarjeta o, en su defecto, el email.
     *
     * @param  array<string, mixed>  $pago
     * @return array{0: string, 1: ?string, 2: ?string}
     */
    private function datosPagador(array $pago): array
    {
        $payer = (array) ($pago['payer'] ?? []);
        $titular = (array) ($pago['card']['cardholder'] ?? []);

        $nombre = trim(($payer['first_name'] ?? '').' '.($payer['last_name'] ?? ''));
        if ($nombre === '') {
            $nombre = trim((string) ($titular['name'] ?? ''));
        }

        $email = trim((string) ($payer['email'] ?? '')) ?: null;

        $documento = null;
        $identificacion = (array) ($payer['identification'] ?? []);
        if (! empty($identificacion['number'])) {
            $documento = trim(($identificacion['type'] ?? '').' '.$identificacion['number']);
        }

        $pagador = $nombre !== '' ? $nombre : ($email ?? '—');

        return [$pagador, $email, $documento];
    }

    /**
     * @param  array<string, mixed>  $pago
     */
    private function urlDetalle(array $pago): ?string
    {
        if (empty($pago['id'])) {
            return null;
        }

        $site = strtoupper((string) ($pago['site_id'] ?? ''));
        $dominio = self::DOMINIOS[$site] ?? 'mercadopago.com';

        return "https://www.{$dominio}/admin/payments/{$pago['id']}";
    }
}
