<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition-shadow {{ $producto->disponible ? '' : 'opacity-70' }}">
    <div class="relative">
        @if ($producto->foto_path)
            <img src="{{ $producto->fotoUrl() }}" alt="{{ $producto->nombre }}" class="w-full h-48 object-cover">
        @else
            <div class="w-full h-48 bg-gradient-to-br from-indigo-100 to-purple-100 dark:from-indigo-900/20 dark:to-purple-900/20 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-indigo-300 dark:text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
            </div>
        @endif
        @if (!$producto->disponible)
            <div class="absolute top-2 right-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full">
                Agotado
            </div>
        @endif
    </div>
    <div class="p-5">
        <div class="flex items-start justify-between">
            <div class="flex-1 min-w-0 mr-3">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $producto->nombre }}</h3>
                @if ($producto->descripcion)
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 line-clamp-2">{{ $producto->descripcion }}</p>
                @endif
            </div>
            <span class="text-lg font-bold text-indigo-600 dark:text-indigo-400 whitespace-nowrap">
                ${{ number_format($producto->precio, 0) }}
            </span>
        </div>
    </div>
</div>
