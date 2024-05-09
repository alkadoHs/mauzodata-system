<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        <!-- Styles -->
        @vite('resources/css/filament/app/theme.css')
    </head>
    <body class="font-figtree antialiased  h-dvh bg-slate-950 text-white/70">
        <header class="py-3">
            <nav>
                <ul class="flex justify-between px-8">
                    <li>Mauzodata/li>

                    <li>Menu</li>
                </ul>
            </nav>
        </header>

        <section class="h-32 bg-green-950/50">
            <header>
                <h1 class="text-3xl font-bold text text-center">The business intelligent software to power your whole work.</h1>
            </header>
        </section>
    </body>
</html>
