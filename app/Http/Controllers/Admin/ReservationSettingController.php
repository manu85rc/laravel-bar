<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReservationSetting;
use Illuminate\Http\Request;

class ReservationSettingController extends Controller
{
    public function edit()
    {
        $settings = ReservationSetting::getInstance();
        $diasSemana = ReservationSetting::getDiasSemana();
        $daySettings = $settings->daySettings->keyBy('dia');

        return view('admin.reservation_settings.edit', compact('settings', 'diasSemana', 'daySettings'));
    }

    public function update(Request $request)
    {
        $rules = [
            'intervalo_minutos' => 'required|integer|min:15|max:120',
            'max_personas' => 'required|integer|min:1|max:100',
            'dias' => 'required|array',
        ];

        foreach (range(1, 7) as $dia) {
            $rules["dias.{$dia}.activo"] = 'boolean';
            $rules["dias.{$dia}.hora_apertura_dia"] = 'nullable|date_format:H:i';
            $rules["dias.{$dia}.hora_cierre_dia"] = 'nullable|date_format:H:i';
            $rules["dias.{$dia}.hora_apertura_noche"] = 'nullable|date_format:H:i';
            $rules["dias.{$dia}.hora_cierre_noche"] = 'nullable|date_format:H:i';
        }

        $request->validate($rules);

        $settings = ReservationSetting::getInstance();
        $settings->update([
            'intervalo_minutos' => $request->intervalo_minutos,
            'max_personas' => $request->max_personas,
        ]);

        foreach (range(1, 7) as $dia) {
            $dayData = $request->input("dias.{$dia}");
            $activo = filter_var($dayData['activo'] ?? false, FILTER_VALIDATE_BOOLEAN);

            if (! $activo) {
                $settings->getDaySettings($dia)->update([
                    'activo' => false,
                    'hora_apertura_dia' => null,
                    'hora_cierre_dia' => null,
                    'hora_apertura_noche' => null,
                    'hora_cierre_noche' => null,
                ]);

                continue;
            }

            $diaNombre = ReservationSetting::getDiasSemana()[$dia];

            $aperturaDia = $dayData['hora_apertura_dia'] ?? null;
            $cierreDia = $dayData['hora_cierre_dia'] ?? null;
            $aperturaNoche = $dayData['hora_apertura_noche'] ?? null;
            $cierreNoche = $dayData['hora_cierre_noche'] ?? null;

            $hasDia = $aperturaDia && $cierreDia;
            $hasNoche = $aperturaNoche && $cierreNoche;

            if (! $hasDia && ! $hasNoche) {
                return back()->withInput()->withErrors([
                    "dias.{$dia}.hora_apertura_dia" => "{$diaNombre}: Debes completar al menos un horario (día o noche).",
                ]);
            }

            if ($hasDia) {
                if ($cierreDia <= $aperturaDia) {
                    return back()->withInput()->withErrors(["dias.{$dia}.hora_cierre_dia" => "{$diaNombre}: El cierre del día debe ser después de la apertura."]);
                }
            }

            if ($hasNoche) {
                if ($cierreNoche <= $aperturaNoche) {
                    return back()->withInput()->withErrors(["dias.{$dia}.hora_cierre_noche" => "{$diaNombre}: El cierre de la noche debe ser después de la apertura."]);
                }
            }

            if (! $hasDia) {
                $aperturaDia = null;
                $cierreDia = null;
            }
            if (! $hasNoche) {
                $aperturaNoche = null;
                $cierreNoche = null;
            }

            $settings->getDaySettings($dia)->update([
                'activo' => true,
                'hora_apertura_dia' => $aperturaDia,
                'hora_cierre_dia' => $cierreDia,
                'hora_apertura_noche' => $aperturaNoche,
                'hora_cierre_noche' => $cierreNoche,
            ]);
        }

        return redirect()->route('admin.reservation-settings.edit')
            ->with('success', 'Configuración de horarios actualizada.');
    }
}
