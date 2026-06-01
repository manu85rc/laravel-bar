@extends('layouts.admin')

@section('content')
<div class="page-top">
    <div>
        <div class="page-title">Configuración de Horarios</div>
        <div class="page-sub">Administra los horarios de día y noche para cada día de la semana</div>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
@endif

@if ($errors->any())
    <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i>
        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<div class="content-max">
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.reservation-settings.update') }}">
                @csrf
                @method('PUT')

                <div style="margin-bottom:20px;display:flex;gap:16px;flex-wrap:wrap;">
                    <div class="form-group" style="margin:0;flex:1;min-width:180px;">
                        <label class="form-label" for="intervalo_minutos">Intervalo entre turnos</label>
                        <select name="intervalo_minutos" id="intervalo_minutos" class="form-select" required>
                            @foreach ([15, 30, 60] as $val)
                                <option value="{{ $val }}" {{ (old('intervalo_minutos', $settings->intervalo_minutos) == $val) ? 'selected' : '' }}>
                                    Cada {{ $val }} minutos
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group" style="margin:0;flex:1;min-width:180px;">
                        <label class="form-label" for="max_personas">Máximo de personas por reserva</label>
                        <input type="number" name="max_personas" id="max_personas" class="form-input"
                            value="{{ old('max_personas', $settings->max_personas) }}" required min="1" max="100">
                    </div>
                </div>

                <div style="border-top:1px solid var(--border-dim);padding-top:16px;">
                    <div style="font-size:13px;font-weight:600;color:var(--text-3);margin-bottom:12px;letter-spacing:0.04em;">Horarios por día</div>

                    @foreach ($diasSemana as $num => $nombre)
                        @php $ds = $daySettings->get($num); @endphp
                        <div class="day-row" style="border:1px solid var(--border-dim);border-radius:6px;margin-bottom:8px;overflow:hidden;">
                            <div class="day-header" style="display:flex;align-items:center;gap:10px;padding:10px 14px;cursor:pointer;user-select:none;background:var(--bg-2);"
                                 onclick="toggleDay({{ $num }})">
                                <label class="form-checkbox" style="margin:0;padding:0;" onclick="event.stopPropagation()">
                                    <input type="hidden" name="dias[{{ $num }}][activo]" value="0">
                                    <input type="checkbox" name="dias[{{ $num }}][activo]" value="1"
                                        {{ old("dias.{$num}.activo", $ds && $ds->activo) ? 'checked' : '' }}
                                        onchange="toggleDayFields({{ $num }}, this.checked)">
                                    <span style="font-weight:600;font-size:14px;">{{ $nombre }}</span>
                                </label>
                                <span id="dayStatus{{ $num }}" style="font-size:12px;color:var(--text-4);margin-left:auto;">
                                    @if ($ds && $ds->activo)
                                        @php
                                            $parts = [];
                                            if ($ds->hora_apertura_dia && $ds->hora_cierre_dia) {
                                                $parts[] = 'Día ' . \Carbon\Carbon::parse($ds->hora_apertura_dia)->format('H:i') . '—' . \Carbon\Carbon::parse($ds->hora_cierre_dia)->format('H:i');
                                            }
                                            if ($ds->hora_apertura_noche && $ds->hora_cierre_noche) {
                                                $parts[] = 'Noche ' . \Carbon\Carbon::parse($ds->hora_apertura_noche)->format('H:i') . '—' . \Carbon\Carbon::parse($ds->hora_cierre_noche)->format('H:i');
                                            }
                                        @endphp
                                        {{ implode(' · ', $parts) }}
                                    @else
                                        Inactivo
                                    @endif
                                </span>
                                <i class="fa-solid fa-chevron-down" id="dayChevron{{ $num }}" style="font-size:11px;color:var(--text-4);transition:transform .2s;"></i>
                            </div>
                            <div id="dayBody{{ $num }}" class="day-body" style="display:none;padding:14px;border-top:1px solid var(--border-dim);">
                                <div style="margin-bottom:12px;">
                                    <div style="font-size:11px;font-weight:600;color:var(--gold);margin-bottom:8px;letter-spacing:0.03em;text-transform:uppercase;">Horario día</div>
                                    <div style="display:flex;gap:12px;flex-wrap:wrap;">
                                        <div class="form-group" style="margin:0;flex:1;min-width:140px;">
                                            <label class="form-label" style="font-size:12px;" for="dias_{{ $num }}_hora_apertura_dia">Apertura día</label>
                                            <input type="time" name="dias[{{ $num }}][hora_apertura_dia]" id="dias_{{ $num }}_hora_apertura_dia" class="form-input"
                                                value="{{ old("dias.{$num}.hora_apertura_dia", $ds && $ds->hora_apertura_dia ? \Carbon\Carbon::parse($ds->hora_apertura_dia)->format('H:i') : '') }}"
                                                onchange="updateDayStatus({{ $num }})">
                                        </div>
                                        <div class="form-group" style="margin:0;flex:1;min-width:140px;">
                                            <label class="form-label" style="font-size:12px;" for="dias_{{ $num }}_hora_cierre_dia">Cierre día</label>
                                            <input type="time" name="dias[{{ $num }}][hora_cierre_dia]" id="dias_{{ $num }}_hora_cierre_dia" class="form-input"
                                                value="{{ old("dias.{$num}.hora_cierre_dia", $ds && $ds->hora_cierre_dia ? \Carbon\Carbon::parse($ds->hora_cierre_dia)->format('H:i') : '') }}"
                                                onchange="updateDayStatus({{ $num }})">
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <div style="font-size:11px;font-weight:600;color:var(--text-3);margin-bottom:8px;letter-spacing:0.03em;text-transform:uppercase;">Horario noche</div>
                                    <div style="display:flex;gap:12px;flex-wrap:wrap;">
                                        <div class="form-group" style="margin:0;flex:1;min-width:140px;">
                                            <label class="form-label" style="font-size:12px;" for="dias_{{ $num }}_hora_apertura_noche">Apertura noche</label>
                                            <input type="time" name="dias[{{ $num }}][hora_apertura_noche]" id="dias_{{ $num }}_hora_apertura_noche" class="form-input"
                                                value="{{ old("dias.{$num}.hora_apertura_noche", $ds && $ds->hora_apertura_noche ? \Carbon\Carbon::parse($ds->hora_apertura_noche)->format('H:i') : '') }}"
                                                onchange="updateDayStatus({{ $num }})">
                                        </div>
                                        <div class="form-group" style="margin:0;flex:1;min-width:140px;">
                                            <label class="form-label" style="font-size:12px;" for="dias_{{ $num }}_hora_cierre_noche">Cierre noche</label>
                                            <input type="time" name="dias[{{ $num }}][hora_cierre_noche]" id="dias_{{ $num }}_hora_cierre_noche" class="form-input"
                                                value="{{ old("dias.{$num}.hora_cierre_noche", $ds && $ds->hora_cierre_noche ? \Carbon\Carbon::parse($ds->hora_cierre_noche)->format('H:i') : '') }}"
                                                onchange="updateDayStatus({{ $num }})">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="form-actions" style="margin-top:16px;">
                    <a href="{{ route('admin.dashboard') }}" class="btn-link">Cancelar</a>
                    <button type="submit" class="btn btn-gold">Guardar configuración</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleDay(num) {
    var body = document.getElementById('dayBody' + num);
    var chevron = document.getElementById('dayChevron' + num);
    var isOpen = body.style.display !== 'none';
    body.style.display = isOpen ? 'none' : 'block';
    chevron.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(180deg)';
}

function toggleDayFields(num, active) {
    var body = document.getElementById('dayBody' + num);
    var inputs = body.querySelectorAll('input, select');
    inputs.forEach(function(el) { el.disabled = !active; });
    updateDayStatus(num);
}

function fmt(name, a, b) {
    if (a && b) return name + ' ' + a + '\u2014' + b;
    return '';
}

function updateDayStatus(num) {
    var checkbox = document.querySelector('input[type="checkbox"][name="dias[' + num + '][activo]"]');
    var status = document.getElementById('dayStatus' + num);
    if (!checkbox || !checkbox.checked) {
        status.innerHTML = 'Inactivo';
        return;
    }
    var dA = document.getElementById('dias_' + num + '_hora_apertura_dia').value;
    var dC = document.getElementById('dias_' + num + '_hora_cierre_dia').value;
    var nA = document.getElementById('dias_' + num + '_hora_apertura_noche').value;
    var nC = document.getElementById('dias_' + num + '_hora_cierre_noche').value;
    var parts = [];
    if (dA && dC) parts.push('D\u00eda ' + dA + '\u2014' + dC);
    if (nA && nC) parts.push('Noche ' + nA + '\u2014' + nC);
    status.innerHTML = parts.length ? parts.join(' \u00b7 ') : 'Sin horarios';
}

document.addEventListener('DOMContentLoaded', function() {
    @foreach ($diasSemana as $num => $nombre)
        (function(num) {
            var checkbox = document.querySelector('input[type="checkbox"][name="dias[' + num + '][activo]"]');
            if (checkbox) {
                toggleDayFields(num, checkbox.checked);
            }
        })({{ $num }});
    @endforeach
});
</script>

<style>
.day-row .day-header:hover {
    background: var(--bg-3) !important;
}
.day-body .form-group input:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}
</style>
@endsection
