@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <div class="border-4 border-dashed border-gray-200 rounded-lg h-full">
            <div class="mb-6">
                <h2 class="text-2xl font-semibold text-gray-900">Editar Producto</h2>
            </div>

            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.productos.update', ) }}" method="POST" @method('PUT') class="space-y-6">
                @csrf

                <div>
                    <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre</label>
                    <input type="text" name="nombre" id="nombre" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" value="{{ old('nombre', ->nombre) }}" required>
                </div>

                <div>
                    <label for="descripcion" class="block text-sm font-medium text-gray-700">Descripción (opcional)</label>
                    <textarea name="descripcion" id="descripcion" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('descripcion') }}</textarea>
                </div>

                <div>
                    <label for="precio" class="block text-sm font-medium text-gray-700">Precio</label>
                    <input type="number" name="precio" id="precio" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" value="{{ old('precio') }}" required>
                </div>

                <div>
                    <label for="categoria_id" class="block text-sm font-medium text-gray-700">Categoría (opcional)</label>
                    <select name="categoria_id" id="categoria_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Ninguna</option>
                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria->id }}" {{ old('categoria_id') == $categoria->id ? 'selected' : '' }}>
                                {{ $categoria->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="foto" class="block text-sm font-medium text-gray-700">Foto (opcional)</label>
                    <input type="file" name="foto" id="foto" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-600 file:text-white hover:file:bg-indigo-700">
                    <p class="mt-1 text-xs text-gray-500">Formatos aceptados: JPG, PNG, GIF, SVG (máx. 2MB)</p>
                </div>
                <div class="flex items-center">
                    <div class="flex items-center space-x-2">
                        <input type="checkbox" name="disponible" id="disponible" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" {{ old('disponible', true) ? 'checked' : '' }}>
                        <label for="disponible" class="ml-2 text-sm font-medium text-gray-700">Disponible</label>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <a href="{{ route('admin.productos.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                        Crear Producto
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
