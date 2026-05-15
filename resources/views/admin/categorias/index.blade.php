@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <div class="border-4 border-dashed border-gray-200 rounded-lg h-full">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-semibold text-gray-900">Gestión de Categorías</h2>
                <a href="{{ route('admin.categorias.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                    Nueva Categoría
                </a>
            </div>

            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if ($categorias->isEmpty())
                <p class="text-gray-500 text-center py-8">No hay categorías principales. ¡Crea una nueva!</p>
            @else
                <div class="space-y-4">
                    @foreach ($categorias as $categoria)
                        <div class="border rounded-lg p-4 mb-4 hover:shadow-md transition-shadow">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                        {{ strtoupper(substr($categoria->name,0,1)) }}
                                    </div>
                                </div>
                                <div class="ml-4 w-0 flex-1">
                                    <div class="flex items-baseline space-x-2">
                                        <h3 class="text-lg font-medium text-gray-900">{{ $categoria->name }}</h3>
                                        @if ($categoria->description)
                                            <span class="text-sm text-gray-500">{{ $categoria->description }}</span>
                                        @endif
                                    </div>
                                    @if ($categoria->children->count() > 0)
                                        <p class="mt-2 text-sm text-gray-500">
                                            Subcategorías: 
                                            @foreach ($categoria->children as $child)
                                                <span class="bg-gray-200 rounded px-2 py-1 text-xs mr-1">{{ $child->name }}</span>
                                            @endforeach
                                        </p>
                                    @endif
                                </div>
                                <div class="ml-4 flex-shrink-0 space-x-2">
                                    <a href="{{ route('admin.categorias.edit', $categoria->id) }}" class="text-sm text-indigo-600 hover:text-indigo-900">Editar</a>
                                    <form action="{{ route('admin.categorias.destroy', $categoria->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar esta categoría y todas sus subcategorías?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm text-red-600 hover:text-red-900">Eliminar</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
