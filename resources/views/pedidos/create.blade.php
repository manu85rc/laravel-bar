@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto mt-10 bg-white rounded-lg shadow-md overflow-hidden">
    <div class="px-6 py-4">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold text-gray-800">Nuevo Pedido</h2>
            <a href="{{ route('pedidos.index') }}" class="text-sm text-indigo-600 hover:text-indigo-500">
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

        <form method="POST" action="{{ route('pedidos.store') }}">
            @csrf

            <div class="mb-4">
                <label for="mesa_id" class="block text-gray-700 text-sm font-bold mb-2">
                    Mesa
                </label>
                <select name="mesa_id" id="mesa_id"
                        class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="">Selecciona una mesa</option>
                    @foreach ($mesas as $mesa)
                        <option value="{{ $mesa->id }}"
                                {{ old('mesa_id') == $mesa->id ? 'selected' : '' }}>
                            Mesa {{ $mesa->numero }} ({{ $mesa->capacidad }} personas) - {{ $mesa->ubicacion ?? 'Sin ubicación' }}
                        </option>
                    @endforeach
                </select>
                @if ($errors->has('mesa_id'))
                    <span class="text-red-500 text-sm block mt-1">{{ $errors->first('mesa_id') }}</span>
                @endif
            </div>

            <div class="mb-4">
                <label for="productos" class="block text-gray-700 text-sm font-bold mb-2">
                    Productos
                </label>
                <div id="productos-container">
                    <div class="producto-item flex items-center mb-2" id="producto-template" style="display: none;">
                        <select name="productos[]" class="flex-1 mr-2 block w-full pl-3 pr-10 py-2 text-base border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm producto-select">
                            <option value="">Selecciona un producto</option>
                            @foreach ($productos as $producto)
                                <option value="{{ $producto->id }}">{{ $producto->nombre }} - ${{ number_format($producto->precio, 2) }}</option>
                            @endforeach
                        </select>
                        <input type="number" name="cantidad[]" class="w-20 mr-2 border border-gray-300 rounded-md py-2 px-3 text-center focus:outline-none focus:ring-2 focus:ring-indigo-500 cantidad-input" min="1" value="1">
                        <button type="button" class="remove-producto bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">-</button>
                    </div>
                    <div class="producto-item flex items-center mb-2">
                        <select name="productos[]" class="flex-1 mr-2 block w-full pl-3 pr-10 py-2 text-base border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm producto-select">
                            <option value="">Selecciona un producto</option>
                            @foreach ($productos as $producto)
                                <option value="{{ $producto->id }}">{{ $producto->nombre }} - ${{ number_format($producto->precio, 2) }}</option>
                            @endforeach
                        </select>
                        <input type="number" name="cantidad[]" class="w-20 mr-2 border border-gray-300 rounded-md py-2 px-3 text-center focus:outline-none focus:ring-2 focus:ring-indigo-500 cantidad-input" min="1" value="1">
                        <button type="button" class="remove-producto bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">-</button>
                    </div>
                </div>
                <button type="button" id="add-producto" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                    + Añadir Producto
                </button>
                @if ($errors->has('productos'))
                    <span class="text-red-500 text-sm block mt-1">{{ $errors->first('productos') }}</span>
                @endif
            </div>

            <div class="mt-6">
                <button type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded focus:outline-none focus:shadow-outline">
                    Crear Pedido
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const addButton = document.getElementById('add-producto');
        const container = document.getElementById('productos-container');
        const template = document.getElementById('producto-template');

        addButton.addEventListener('click', function() {
            const clone = template.cloneNode(true);
            clone.style.display = 'flex';
            container.appendChild(clone);
        });

        container.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-producto')) {
                e.target.closest('.producto-item').remove();
            }
        });
    });
</script>
@endpush