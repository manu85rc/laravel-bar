@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <div class="border-4 border-dashed border-gray-200 rounded-lg h-full">
            <div class="mb-6">
                <h2 class="text-2xl font-semibold text-gray-900">Editar Perfil</h2>
            </div>

            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nombre</label>
                    <input type="text" name="name" id="name"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                           value="{{ old('name', auth()->user()->name) }}" required>
                </div>

                <div>
                    <label for="foto" class="block text-sm font-medium text-gray-700">Foto de perfil (opcional)</label>
                    @if (auth()->user()->profile_photo_path)
                        <div class="mt-2 mb-2">
                            <img src="{{ auth()->user()->profilePhotoUrl() }}" alt="Foto de perfil"
                                 class="w-24 h-24 rounded-full object-cover border">
                        </div>
                        <div class="flex items-center space-x-2 mb-2">
                            <input type="checkbox" name="eliminar_foto" id="eliminar_foto" value="1"
                                   class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                            <label for="eliminar_foto" class="text-sm text-red-600">Eliminar foto actual</label>
                        </div>
                    @endif
                    <input type="file" name="foto" id="foto" accept="image/jpeg,image/png,image/gif,image/svg+xml"
                           class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-600 file:text-white hover:file:bg-indigo-700">
                    <p class="mt-1 text-xs text-gray-500">Formatos aceptados: JPG, PNG, GIF, SVG (máx. 2MB)</p>
                </div>

                <div class="flex items-center justify-between">
                    <a href="{{ url('/dashboard') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
                    <button type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
