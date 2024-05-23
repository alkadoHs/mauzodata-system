<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        {{-- Metatags --}}
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Software ya mauzo itumikayo kuendesha biashara kwa njia ya kielektroniki au kidijitali, Inasimamia mauzo, matumizi, maduka au store zaidi ya moja, madeni n.k" />
        <meta name="theme-color" content="#030712" />
        <meta property="og:title" content="Mauzodata sales software" />
        <meta property="og:description" content="Software ya mauzo itumikayo kusimamia biashara kielektroniki." />
        <meta property="og:image" content="{{ asset('open-graph.png') }}"  />
        <meta property="og:image:alt" content="Mauzodata dashboard showing sales analytics" />
        <meta name="twitter:title" content="Mauzodata Sales Software" />
        <meta name="twitter:description" content="Software ya mauzo itumikayo kusimamia biashara kielektroniki." />
        <meta name="twitter:url" content="https://www.mauzodata.com/?src=twitter" />
        <meta name="twitter:image:src" content="{{ asset('open-graph.png') }}" />
        <meta name="twitter:image:alt" content="Mauzodata dashboard showing sales analytics" />
        <meta name="twitter:creator" content="@mauzodata" />
        <link rel="apple-touch-startup-image" href="{{ asset('mauzodata.svg')}}" media="orientation: portrait" />
        <link rel="apple-touch-startup-image" href="{{ asset('mauzodata.svg')}}" media="orientation: landscape" />
        <link rel="manifest" href="/app.webmanifest">

        <title>Mauzodata Sales System</title>

        <style>
            .bg-image {
                background-image: url("/images/hero-pattern.svg");
                background-repeat: no-repeat;
                background-position: right;
            }
        </style>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter+Tight:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased text-black/50 dark:bg-gray-950 font-sans">
        <div class="text-lg w-full max-w-2xl mx-auto px-6 lg:max-w-7xl my-4 bg-image">
            <header class="">
                <nav class="flex justify-end gap-6 items-center">
                   <div class="mr-auto flex gap-6 items-center">
                       <a class="inline-block bg-kado-50 rounded-full" href="/">
                           <svg width="70" height="70" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                                <!-- Circle -->
                                <circle cx="100" cy="100" r="90" stroke="black" stroke-width="5" fill="none"/>
                                <!-- Stylized M -->
                                <text x="50" y="135" font-family="Inter Tight, sans-serif" font-size="100" fill="black" id="letterM">M</text>
                                <!-- Incomplete Circle Orbit with Arrow -->
                                <path d="M 170,100 A 70,70 0 0,0 50,100" stroke="black" stroke-width="3" fill="none" id="orbit"/>
                                <polygon points="50,100 45,95 55,95" fill="black" id="arrow"/>
                            </svg>
                        </a>
                        <ul class="bg-gray-900 dark:text-gray-300 flex items-center gap-4 rounded-[2rem] py-4 px-8 border-gray-600">
                            <li>Products</li>
                            <li>Pricing</li>
                            <li>Company</li>
                        </ul>
                   </div>

                    <div>
                        <button class="bg-kado-600 dark:text-gray-300 rounded-[2rem] py-3 px-8">Get started</button>
                        <a href="{{ url('app')}}" class="bg-transparent dark:text-gray-300 rounded-[2rem] py-3 px-8 border border-gray-600">Request demo</a>
                    </div>
                </nav>
            </header>

            <main class="mt-6">
                <h1 class="text-5xl lg:text-7xl font-semibold dark:text-gray-200">
                    Endesha biashara kwa mfumo <span class="text-kado-500"> wa kielektroniki</span>.</h1>

                <p class="text-xl text-gray-400 max-w-md my-2">
                    Kama wewe ni mfanyabiashara basi unajua ni ngumu kiasi gani kuendesha
                    biashara yako kwa kutumia madaftari, tunasuluhisho hilo.
                </p>

                <div class="my-6">
                    <button class="bg-kado-600 dark:text-gray-300 rounded-[2rem] py-3 px-8">Get started</button>
                    <button class="bg-transparent dark:text-gray-300 rounded-[2rem] py-3 px-8 border border-gray-600">Get started</button>
                </div>

                <div class="text-slate-300">
                     
                </div>
            </main>

            <footer class="py-16 text-center text-sm text-black dark:text-white/70">
                {{-- Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }}) --}}
            </footer>
        </div>
    </body>
</html>
