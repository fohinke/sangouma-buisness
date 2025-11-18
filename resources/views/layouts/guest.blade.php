<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans text-gray-900 antialiased">
        @if(session('success'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
                 class="fixed top-4 right-4 z-50 bg-green-600 text-white px-4 py-2 rounded shadow">
                {{ session('success') }}
            </div>
        @endif
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <div>
                <a href="/">
                    <div class="inline-flex items-center justify-center px-6 py-2 rounded-full bg-white text-indigo-700 text-xl font-semibold border border-indigo-500 shadow-lg">
                        SANGOUMA
                    </div>
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
        @livewireScripts
    </body>
</html>
