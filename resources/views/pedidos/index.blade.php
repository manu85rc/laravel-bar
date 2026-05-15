@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto mt-10 px-4">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-semibold text-gray-900">
                    Pedidos
                </h1>
                <a href="{{ route('pedidos.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Nuevo Pedido
                </a>
            </div>
        </div>
        <div class="p-6">
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 border rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if ($pedidos->isEmpty())
                <p class="text-gray-500">No hay pedidos registrados.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mesa</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Camarero</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th scope="col" class="relative px-6 py-3">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($pedidos as $pedido)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $pedido->id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        Mesa {{ $pedido->mesa->numero }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if ($pedido->camarero)
                                            {{ $pedido->camarero->name }}
                                        @else
                                            <span class="text-gray-500">Sin asignar</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $pedido->estado == 'pendiente' ? 'bg-yellow-100 text-yellow-800' : $pedido->estado == 'preparando' ? 'bg-blue-100 text-blue-800' : $pedido->estado == 'listo' ? 'bg-green-100 text-green-800' : $pedido->estado == 'servido' ? 'bg-indigo-100 text-indigo-800' : 'bg-red-100 text-red-800' }}">
                                            {{ ucfirst($pedido->estado) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${{ number_format($pedido->total, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('pedidos.show', $pedido) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Ver</a>
                                        @if (auth()->user()->isAdmin() || $pedido->estado === 'pendiente')
                                            <form action="{{ route('pedidos.updateEstado', $pedido) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de cambiar el estado?');">
                                                @csrf
                                                @method('PUT')
                                                <select name="estado" class="ml-2 border border-gray-300 rounded-md px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                                    <option value="pendiente" {{ $pedido->estado == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                                    <option value="preparando" {{ $pedido->estado == 'preparando' ? 'selected' : '' }}>Preparando</option>
                                                    <option value="listo" {{ $pedido->estado == 'listo' ? 'selected' : '' }}>Listo</option>
                                                    <option value="servido" {{ $pedido->estado == 'servido' ? 'selected' : '' }}>Servido</option>
                                                    <option value="pagado" {{ $pedido->estado == 'pagado' ? 'selected' : '' }}>Pagado</option>
                                                </select>
                                                <button type="submit" class="ml-2 bg-indigo-600 text-white px-3 py-1 rounded hover:bg-indigo-700">Cambiar</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection