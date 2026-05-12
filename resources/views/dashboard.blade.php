@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto mt-10 px-4">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <h1 class="text-2xl font-semibold text-gray-900">
                Dashboard
            </h1>
        </div>
        <div class="p-6">
            @auth
                <div class="mb-4">
                    <span class="text-sm font-medium text-gray-500">Bienvenido, </span>
                    <span class="text-lg font-medium text-gray-900">{{ auth()->user()->name }}</span>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @if (auth()->user()->isAdmin())
                        <div class="bg-indigo-50 p-4 rounded-lg">
                            <h3 class="text-lg font-medium text-indigo-800">Administrador</h3>
                            <p class="mt-2 text-gray-600">Tienes acceso completo al sistema.</p>
                            <a href="{{ url('/mesas') }}" class="mt-4 inline-block text-indigo-600 font-medium hover:text-indigo-500">
                                Gestionar mesas
                            </a>
                        </div>
                    @else
                        <div class="bg-green-50 p-4 rounded-lg">
                            <h3 class="text-lg font-medium text-green-800">Usuario</h3>
                            <p class="mt-2 text-gray-600">Puedes realizar reservas de mesas.</p>
                            <a href="{{ url('/reservas') }}" class="mt-4 inline-block text-green-600 font-medium hover:text-green-500">
                                Mis reservas
                            </a>
                        </div>
                    @endif
                </div>
            @endauth
        </div>
    </div>
</div>
@endsection
