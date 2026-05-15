@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto mt-10 bg-white rounded-lg shadow-md overflow-hidden">
    <div class="px-6 py-4">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold text-gray-800">Editar Mesa</h2>
            <a href="{{ route('mesas.index') }}" class="text-sm text-indigo-600 hover:text-indigo-500">
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

        <form method="POST" action="{{ route('mesas.update', $mesa->id) }}">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="numero" class="block text-gray-700 text-sm font-bold mb-2">
                    Número de Mesa
                </label>
                <input type="text"
                       name="numero"
                       id="numero"
                       required
                       class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                       value="{{ old('numero', $mesa->numero) }}">
                @if ($errors->has('numero'))
                    <span class="text-red-500 text-sm block mt-1">{{ $errors->first('numero') }}</span>
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
                       value="{{ old('capacidad', $mesa->capacidad) }}">
                @if ($errors->has('capacidad'))
                    <span class="text-red-500 text-sm block mt-1">{{ $errors->first('capacidad') }}</span>
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
                       value="{{ old('ubicacion', $mesa->ubicacion) }}">
                @if ($errors->has('ubicacion'))
                    <span class="text-red-500 text-sm block mt-1">{{ $errors->first('ubicacion') }}</span>
                @endif
            </div>

            <div class="mb-4">
                <div class="flex items-center">
                    <button type="button"
                            class="ml-2 flex items-center px-3 py-1 text-xs font-medium rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2"
                            onclick="
                                if (this.textContent === 'Sí') {
                                    this.textContent = 'No';
                                    this.classList.remove('bg-green-100', 'text-green-800');
                                    this.classList.add('bg-red-100', 'text-red-800');
                                    document.getElementById('disponible').value = 0;
                                } else {
                                    this.textContent = 'Sí';
                                    this.classList.remove('bg-red-100', 'text-red-800');
                                    this.classList.add('bg-green-100', 'text-green-800');
                                    document.getElementById('disponible').value = 1;
                                }
                            "
                            {{ $mesa->disponible ? 'class="bg-green-100 text-green-800"' : 'class="bg-red-100 text-red-800"' }}>
                        {{ $mesa->disponible ? 'Sí' : 'No' }}
                    </button>
                    <input type="hidden" name="disponible" id="disponible" value="{{ $mesa->disponible ? 1 : 0 }}">
                </div>
                @if ($errors->has('disponible'))
                    <span class="text-red-500 text-sm block mt-1">{{ $errors->first('disponible') }}</span>
                @endif
            </div>

            <div class="mt-6">
                <button type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded focus:outline-none focus:shadow-outline">
                    Actualizar Mesa
                </button>
            </div>
        </form>
    </div>
</div>
@endsection