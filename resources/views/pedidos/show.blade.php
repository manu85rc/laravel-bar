@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto mt-10 px-4">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-semibold text-gray-900">
                    Pedido #{{ $pedido->id }}
                </h1>
                <div class="flex items-center space-x-3">
                    <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $pedido->estado == 'pendiente' ? 'bg-yellow-100 text-yellow-800' : $pedido->estado == 'preparando' ? 'bg-blue-100 text-blue-800' : $pedido->estado == 'listo' ? 'bg-green-100 text-green-800' : $pedido->estado == 'servido' ? 'bg-indigo-100 text-indigo-800' : 'bg-red-100 text-red-800' }}">
                        {{ ucfirst($pedido->estado) }}
                    </span>
                    @if (auth()->user()->isAdmin() || $pedido->estado !== 'pagado')
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
                            <button type="submit" class="ml-2 bg-indigo-600 text-white px-3 py-1 rounded hover:bg-indigo-700">
                                Cambiar Estado
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
        <div class="p-6">
            <div class="mb-4">
                <h2 class="text-xl font-semibold text-gray-900 mb-2">Información del Pedido</h2>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <p class="text-sm text-gray-500">Mesa:</p>
                        <p class="text-lg font-medium text-gray-900">Mesa {{ $pedido->mesa->numero }} ({{ $pedido->mesa->capacidad }} personas)</p>
                        @if ($pedido->mesa->ubicacion)
                            <p class="text-sm text-gray-500 mt-1">Ubicación: {{ $pedido->mesa->ubicacion }}</p>
                        @endif
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Camarero:</p>
                        <p class="text-lg font-medium text-gray-900">
                            @if ($pedido->camarero)
                                {{ $pedido->camarero->name }}
                            @else
                                <span class="text-gray-500">Sin asignar</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Fecha y Hora:</p>
                        <p class="text-lg font-medium text-gray-900">{{ $pedido->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Total:</p>
                        <p class="text-lg font-bold text-gray-900">${{ number_format($pedido->total, 2) }}</p>
                    </div>
                </div>
            </div>

            @if ($pedido->productos->isNotEmpty())
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Productos</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio Unitario</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($pedido->productos as $producto)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $producto->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">{{ $producto->pivot->cantidad }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">${{ number_format($producto->pivot->precio_unitario, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">${{ number_format($producto->pivot->cantidad * $producto->pivot->precio_unitario, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <th scope="col" colspan="3" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">TOTAL</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">${{ number_format($pedido->total, 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @else
                <p class="text-gray-500">No hay productos en este pedido.</p>
            @endif
        </div>
    </div>
</div>
@endsection