<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="theme">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name') }}</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
    <!-- Loading Progress Bar -->
    <div x-data="loadingBar" 
         x-show="isLoading" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed top-0 left-0 right-0 z-50 h-1"
         style="display: none;">
        <div class="h-full bg-blue-500 dark:bg-blue-400 transition-all duration-300 ease-out"
             :style="`width: ${progress}%`"></div>
    </div>

    <!-- Navbar -->
    @include('components.navbar')
    
    <!-- Main Content -->
    <main class="min-h-screen">
        {{ $slot }}
    </main>
</body>
</html>
