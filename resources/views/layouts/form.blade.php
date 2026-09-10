<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Pendaftaran') Imersi</title>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
</head>
<body class="bg-bg text-ink">
<main class="mx-auto max-w-4xl px-5 py-8">
    @if(session('status'))
        <div class="mb-4 rounded-2xl bg-primary/15 px-4 py-3 text-sm text-primary-dark">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-4 rounded-2xl bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
    @endif
    @yield('content')
</main>
</body>
</html>
