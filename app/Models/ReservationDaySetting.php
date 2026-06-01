<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ReservationDaySetting extends Model
{
    protected $table = 'reservation_day_settings';

    protected $fillable = [
        'dia',
        'activo',
        'hora_apertura_dia',
        'hora_cierre_dia',
        'hora_apertura_noche',
        'hora_cierre_noche',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function reservationSetting()
    {
        return $this->belongsTo(ReservationSetting::class);
    }

    public function generateTimeSlots(int $intervaloMinutos): array
    {
        $slots = [];
        $interval = max(15, $intervaloMinutos);

        foreach ($this->getPeriods() as $period) {
            $start = Carbon::parse($period['start']);
            $end = Carbon::parse($period['end']);
            while ($start <= $end) {
                $slots[] = $start->format('H:i');
                $start->addMinutes($interval);
            }
        }

        return $slots;
    }

    public function getTimeSlotGroups(int $intervaloMinutos): array
    {
        $interval = max(15, $intervaloMinutos);
        $groups = [];

        foreach ($this->getPeriods() as $key => $period) {
            $slots = [];
            $start = Carbon::parse($period['start']);
            $end = Carbon::parse($period['end']);
            while ($start <= $end) {
                $slots[] = $start->format('H:i');
                $start->addMinutes($interval);
            }
            $label = $key === 'dia' ? 'Día' : 'Noche';
            $groups[] = ['label' => $label, 'slots' => $slots];
        }

        return $groups;
    }

    private function getPeriods(): array
    {
        $periods = [];

        if ($this->hora_apertura_dia && $this->hora_cierre_dia) {
            $periods['dia'] = ['start' => $this->hora_apertura_dia, 'end' => $this->hora_cierre_dia];
        }
        if ($this->hora_apertura_noche && $this->hora_cierre_noche) {
            $periods['noche'] = ['start' => $this->hora_apertura_noche, 'end' => $this->hora_cierre_noche];
        }

        return $periods;
    }
}
