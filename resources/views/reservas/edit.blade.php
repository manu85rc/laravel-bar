@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto mt-10 bg-white rounded-lg shadow-md overflow-hidden">
    <div class="px-6 py-4">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold text-gray-800">Editar Reserva</h2>
            <a href="{{ route('reservas.index') }}" class="text-sm text-indigo-600 hover:text-indigo-500">
                Volver a la lista
            </a>
        </div>

        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-100 border rounded">
                <ul class="list-disc list-inside text-sm text-red-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('status'))
            <div class="mb-4 p-4 bg-green-100 border rounded">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('reservas.update', $reserva) }}">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="mesa_id" class="block text-gray-700 text-sm font-bold mb-2">
                    Mesa
                </label>
                <select name="mesa_id" id="mesa_id"
                        class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="">Selecciona una mesa</option>
                    @foreach ($mesas as $mesa)
                        <option value="{{ $mesa->id }}"
                                {{ old('mesa_id', $reserva->mesa_id) == $mesa->id ? 'selected' : '' }}>
                            Mesa {{ $mesa->numero }} ({{ $mesa->capacidad }} personas) - {{ $mesa->ubicacion ?? 'Sin ubicación' }}
                        </option>
                    @endforeach
                </select>
                @if ($errors->has('mesa_id'))
                    <span class="text-red-500 text-sm block mt-1">{{ $errors->first('mesa_id') }}</span>
                @endif
            </div>

            <div class="mb-4">
                <label for="fecha_hora" class="block text-gray-700 text-sm font-bold mb-2">
                    Fecha y Hora
                </label>
                <input type="text"
                       name="fecha_hora"
                       id="fecha_hora"
                       required
                       placeholder="dd-mm-aaaa HH:MM"
                       class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                       value="{{ old('fecha_hora', $reserva->fecha_hora) }}"
                       data-flatpickr>
                @if ($errors->has('fecha_hora'))
                    <span class="text-red-500 text-sm block mt-1">{{ $errors->first('fecha_hora') }}</span>
                @endif
            </div>

            <div class="mb-4">
                <label for="numero_personas" class="block text-gray-700 text-sm font-bold mb-2">
                    Número de Personas
                </label>
                <input type="number"
                       name="numero_personas"
                       id="numero_personas"
                       required
                       min="1"
                       class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                       value="{{ old('numero_personas', $reserva->numero_personas) }}">
                @if ($errors->has('numero_personas'))
                    <span class="text-red-500 text-sm block mt-1">{{ $errors->first('numero_personas') }}</span>
                @endif
            </div>

            <div class="mb-4">
                <label for="observaciones" class="block text-gray-700 text-sm font-bold mb-2">
                    Observaciones (opcional)
                </label>
                <textarea name="observaciones" id="observaciones" rows="3"
                        class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500">{{ old('observaciones', $reserva->observaciones) }}</textarea>
                @if ($errors->has('observaciones'))
                    <span class="text-red-500 text-sm block mt-1">{{ $errors->first('observaciones') }}</span>
                @endif
            </div>

            <div class="mb-4">
                <label for="estado" class="block text-gray-700 text-sm font-bold mb-2">
                    Estado
                </label>
                <select name="estado" id="estado"
                        class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="pendiente" {{ old('estado', $reserva->estado) == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                    <option value="confirmada" {{ old('estado', $reserva->estado) == 'confirmada' ? 'selected' : '' }}>Confirmada</option>
                    <option value="completada" {{ old('estado', $reserva->estado) == 'completada' ? 'selected' : '' }}>Completada</option>
                    <option value="cancelada" {{ old('estado', $reserva->estado) == 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                </select>
                @if ($errors->has('estado'))
                    <span class="text-red-500 text-sm block mt-1">{{ $errors->first('estado') }}</span>
                @endif
            </div>

            <div class="mt-6">
                <button type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded focus:outline-none focus:shadow-outline">
                    Actualizar Reserva
                </button>
            </div>
        </form>
    </div>
</div>
@endsection