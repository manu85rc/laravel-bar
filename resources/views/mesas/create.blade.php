@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto mt-10 bg-white rounded-lg shadow-md overflow-hidden">
    <div class="px-6 py-4">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold text-gray-800">Crear Mesa</h2>
            <a href="{{ route('mesas.index') }}" class="text-sm text-indigo-600 hover:text-indigo-500">
                Volver a la lista
            </a>
        </div>

        @if (->any())
            <div class="mb-4 p-4 bg-red-100 border rounded">
                <ul class="list-disc list-inside text-sm text-red-700">
                    @foreach (->all() as )
                        <li>{{  }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('mesas.store') }}">
            @csrf

            <div class="mb-4">
                <label for="numero" class="block text-gray-700 text-sm font-bold mb-2">
                    Número de Mesa
                </label>
                <input type="text"
                       name="numero"
                       id="numero"
                       required
                       class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                       value="{{ old('numero') }}">
                @if (->has('numero'))
                    <span class="text-red-500 text-sm block mt-1">{{ ->first('numero') }}</span>
                @endif
            </div>

            <div class="mb-4">
                <label for="capacidad" class="block text-gray-700 text-sm font-bold mb-2">
                    Capacidad
                </label>
                <input type="number"
                       name="capacidad"
                       id="capacidad"
                       required
                       min="1"
                       class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                       value="{{ old('capacidad') }}">
                @if (->has('capacidad'))
                    <span class="text-red-500 text-sm block mt-1">{{ ->first('capacidad') }}</span>
                @endif
            </div>

            <div class="mb-4">
                <label for="ubicacion" class="block text-gray-700 text-sm font-bold mb-2">
                    Ubicación (opcional)
                </label>
                <input type="text"
                       name="ubicacion"
                       id="ubicacion"
                       class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                       value="{{ old('ubicacion') }}">
                @if (->has('ubicacion'))
                    <span class="text-red-500 text-sm block mt-1">{{ ->first('ubicacion') }}</span>
                @endif
            </div>

            <div class="mb-4">
                <div class="flex items-center">
                    <input type="checkbox"
                           name="disponible"
                           id="disponible"
                           class="form-checkbox h-4 w-4 text-indigo-600"
                           {{ old('disponible', true) ? 'checked' : '' }}>
                    <label for="disponible" class="ml-2 block text-sm text-gray-700">
                        Disponible
                    </label>
                </div>
                @if (->has('disponible'))
                    <span class="text-red-500 text-sm block mt-1">{{ ->first('disponible') }}</span>
                @endif
            </div>

            <div class="mt-6">
                <button type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded focus:outline-none focus:shadow-outline">
                    Crear Mesa
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
