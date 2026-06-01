@extends('layouts.admin')

@section('content')
<div class="page-top">
    <div>
        <div class="page-title">Editar Reserva</div>
        <div class="page-sub">Modifica los datos de la reserva</div>
    </div>
    <a href="{{ route('reservas.index') }}" class="btn btn-ghost"><i class="fa-solid fa-arrow-left"></i> Volver</a>
</div>

@if ($errors->any())
    <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
@if (session('status'))
    <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> {{ session('status') }}</div>
@endif

@php
    $reservaFecha = \Carbon\Carbon::parse($reserva->fecha_hora);
    $fechaVal = $reservaFecha->format('Y-m-d');
    $horaVal = $reservaFecha->format('H:i');
    $dow = (int) $reservaFecha->format('N');
@endphp

<div class="content-max">
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('reservas.update', $reserva) }}">
                @csrf @method('PUT')
                <div class="form-grid">
                    @if (auth()->user()->isStaff())
                    <div class="form-group full">
                        <label class="form-label" for="mesa_id">Mesa</label>
                        <select name="mesa_id" id="mesa_id" class="form-select">
                            <option value="">Selecciona una mesa</option>
                            @foreach ($mesas as $mesa)
                                <option value="{{ $mesa->id }}" {{ old('mesa_id', $reserva->mesa_id) == $mesa->id ? 'selected' : '' }}>
                                    Mesa {{ $mesa->numero }} ({{ $mesa->capacidad }} pers.) {{ $mesa->ubicacion ? '— ' . $mesa->ubicacion : '' }}
                                </option>
                            @endforeach
                        </select>
                        @if ($errors->has('mesa_id'))<span class="form-error">{{ $errors->first('mesa_id') }}</span>@endif
                    </div>
                    @endif
                    <div class="form-group">
                        <label class="form-label" for="fecha">Fecha</label>
                        <input type="date" name="fecha" id="fecha" class="form-input"
                            value="{{ old('fecha', $fechaVal) }}" required min="{{ date('Y-m-d') }}">
                        @if ($errors->has('fecha'))<span class="form-error">{{ $errors->first('fecha') }}</span>@endif
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="hora">Hora</label>
                        <select name="hora" id="hora" class="form-select" required>
                            <option value="">Selecciona un horario</option>
                        </select>
                        @if ($errors->has('hora'))<span class="form-error">{{ $errors->first('hora') }}</span>@endif
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="numero_personas">Personas</label>
                        <input type="number" name="numero_personas" id="numero_personas" class="form-input"
                            value="{{ old('numero_personas', $reserva->numero_personas) }}" required min="1" max="{{ $reservationSettings->max_personas }}">
                        @if ($errors->has('numero_personas'))<span class="form-error">{{ $errors->first('numero_personas') }}</span>@endif
                    </div>
                    <div class="form-group full">
                        <label class="form-label" for="observaciones">Observaciones</label>
                        <textarea name="observaciones" id="observaciones" rows="3" class="form-textarea">{{ old('observaciones', $reserva->observaciones) }}</textarea>
                        @if ($errors->has('observaciones'))<span class="form-error">{{ $errors->first('observaciones') }}</span>@endif
                    </div>
                    <div class="form-group full">
                        <label class="form-label" for="estado">Estado</label>
                        <select name="estado" id="estado" class="form-select">
                            <option value="pendiente" {{ old('estado', $reserva->estado) == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                            <option value="confirmada" {{ old('estado', $reserva->estado) == 'confirmada' ? 'selected' : '' }}>Confirmada</option>
                            <option value="completada" {{ old('estado', $reserva->estado) == 'completada' ? 'selected' : '' }}>Completada</option>
                            <option value="cancelada" {{ old('estado', $reserva->estado) == 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                        </select>
                        @if ($errors->has('estado'))<span class="form-error">{{ $errors->first('estado') }}</span>@endif
                    </div>
                </div>
                <div class="form-actions">
                    <a href="{{ route('reservas.index') }}" class="btn-link">Cancelar</a>
                    <button type="submit" class="btn btn-gold">Actualizar Reserva</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
var daySlots = @json($daySlotsJson);
function populateTimeSlots(selectEl, dayOfWeek, selectedValue) {
    selectEl.innerHTML = '<option value="">Selecciona un horario</option>';
    var data = daySlots[dayOfWeek];
    if (!data || !data.activo) { selectEl.disabled = true; return; }
    selectEl.disabled = false;
    data.groups.forEach(function(group) {
        if (group.label) {
            var og = document.createElement('optgroup');
            og.label = group.label;
            group.slots.forEach(function(slot) {
                var opt = document.createElement('option');
                opt.value = slot; opt.textContent = slot;
                if (selectedValue && slot === selectedValue) opt.selected = true;
                og.appendChild(opt);
            });
            selectEl.appendChild(og);
        } else {
            group.slots.forEach(function(slot) {
                var opt = document.createElement('option');
                opt.value = slot; opt.textContent = slot;
                if (selectedValue && slot === selectedValue) opt.selected = true;
                selectEl.appendChild(opt);
            });
        }
    });
}
function updateSlots() {
    var val = document.getElementById('fecha').value;
    if (!val) return;
    var dow = new Date(val + 'T12:00:00').getDay();
    var selected = document.getElementById('hora').value || @json($horaVal);
    populateTimeSlots(document.getElementById('hora'), dow === 0 ? 7 : dow, selected);
}
document.addEventListener('DOMContentLoaded', function() {
    updateSlots();
    document.getElementById('fecha').addEventListener('change', updateSlots);
});
</script>
@endsection
