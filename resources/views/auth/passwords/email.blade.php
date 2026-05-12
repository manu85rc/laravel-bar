@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto mt-10 bg-white rounded-lg shadow-md overflow-hidden">
    <div class="px-6 py-4">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold text-gray-800">Restablecer contraseña</h2>
            <a href="{{ route('login') }}" class="text-sm text-indigo-600 hover:text-indigo-500">
                Volver al inicio de sesión
            </a>
        </div>

        @if (session('status'))
            <div class="mb-4 p-4 bg-green-100 border rounded">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-100 border rounded">
                <ul class="list-disc list-inside text-sm text-red-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="mb-4">
                <label for="email" class="block text-gray-700 text-sm font-bold mb-2">
                    Correo electrónico
                </label>
                <input type="email"
                       name="email"
                       id="email"
                       autocomplete="email"
                       required
                       class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                       value="{{ old('email') }}">
                @if ($errors->has('email'))
                    <span class="text-red-500 text-sm block mt-1">{{ $errors->first('email') }}</span>
                @endif
            </div>

            <div class="mt-6">
                <button type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded focus:outline-none focus:shadow-outline">
                    Enviar enlace de restablecimiento
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
