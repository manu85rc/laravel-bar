<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel Bar') }}</title>
    <!-- Tailwind CSS via CDN for simplicity -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Flatpickr for datetime picker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</head>
<body class="antialiased bg-gray-50">
    <div class="min-h-screen">
        @include('layouts.navbar')

        <!-- Main content -->
        <main class="mt-10">
            @yield('content')
        </main>
    </div>
    
    <!-- Alpine JS for mobile menu toggle -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Initialize Flatpickr for datetime inputs -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const datetimeInputs = document.querySelectorAll('input[type="text"][data-flatpickr]');
            datetimeInputs.forEach(input => {
                flatpickr(input, {
                    enableTime: true,
                    dateFormat: "d-m-Y H:i",
                    time_24hr: true,
                    minDate: "today",
                    locale: "es",
                    
                });
            });
        });
    </script>
</body>
</html>