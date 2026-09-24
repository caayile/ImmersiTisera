<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Magang Dosen</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @viteReactRefresh
    @vite(['resources/js/main.jsx'])
</head>
<body class="bg-cream text-ink">
    <div id="root"></div>
</body>
</html>
