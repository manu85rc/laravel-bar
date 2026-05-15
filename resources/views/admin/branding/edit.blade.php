@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <div class="border-4 border-dashed border-gray-200 rounded-lg h-full">
            <div class="mb-6">
                <h2 class="text-2xl font-semibold text-gray-900">Branding de la Aplicación</h2>
                <p class="mt-1 text-sm text-gray-600">
                    Personaliza el nombre y logo de tu aplicación.
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

            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('admin.branding.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label for="app_name" class="block text-sm font-medium text-gray-700">Nombre de la Aplicación</label>
                    <input type="text" name="app_name" id="app_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" value="{{ old('app_name', $branding->app_name) }}" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Logo Actual</label>
                    <div class="mt-2">
                        @if ($branding->logoUrl())
                            <img src="{{ $branding->logoUrl() }}" alt="Logo" class="max-w-xs h-auto border rounded">
                        @else
                            <p class="text-sm text-gray-500">No hay logo establecido</p>
                        @endif
                    </div>
                </div>

                <div>
                    <label for="logo" class="block text-sm font-medium text-gray-700">Nuevo Logo</label>
                    <p class="text-sm text-gray-500">Formatos permitidos: JPG, PNG, GIF, SVG (máx 2MB)</p>
                    <input type="file" name="logo" id="logo" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:hover:bg-indigo-100">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Favicon Actual</label>
                    <div class="mt-2">
                        @if ($branding->faviconUrl())
                            <img src="{{ $branding->faviconUrl() }}" alt="Favicon" class="w-16 h-16 border rounded">
                        @else
                            <p class="text-sm text-gray-500">No hay favicon establecido</p>
                        @endif
                    </div>
                </div>

                <div>
                    <label for="favicon" class="block text-sm font-medium text-gray-700">Nuevo Favicon</label>
                    <p class="text-sm text-gray-500">Formatos permitidos: ICO, PNG, JPG, GIF, SVG (máx 2MB)</p>
                    <input type="file" name="favicon" id="favicon" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:hover:bg-indigo-100">
                </div>

                <div class="flex items-center justify-between">
                    <a href="{{ route('admin.dashboard') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                        Actualizar Branding
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
