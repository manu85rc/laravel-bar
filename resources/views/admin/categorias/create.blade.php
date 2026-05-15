@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <div class="border-4 border-dashed border-gray-200 rounded-lg h-full">
            <div class="mb-6">
                <h2 class="text-2xl font-semibold text-gray-900">Crear Nueva Categoría</h2>
                <p class="mt-1 text-sm text-gray-600">
                    Las categorías principales no tienen padre. Las subcategorías pertenecen a una categoría principal.
                </p>
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

            <form action="{{ route('admin.categorias.store') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nombre</label>
                    <input type="text" name="name" id="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" value="{{ old('name') }}" required>
                </div>

                <div>
                    <label for="parent_id" class="block text-sm font-medium text-gray-700">Categoría Padre (opcional)</label>
                    <select name="parent_id" id="parent_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Ninguna (categoría principal)</option>
                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria->id }}" {{ old('parent_id') == $categoria->id ? 'selected' : '' }}>
                                {{ $categoria->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Descripción (opcional)</label>
                    <textarea name="description" id="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('description') }}</textarea>
                </div>

                <div class="flex items-center justify-between">
                    <a href="{{ route('admin.categorias.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                        Crear Categoría
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
