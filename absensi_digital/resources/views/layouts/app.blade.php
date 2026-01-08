<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Absensi Digital') }}</title>

        <!-- Google Icons & Materialize CSS (minimal Material look via CDN) -->
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
        <link href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" rel="stylesheet">

        <style>
            /* small layout tweaks to integrate with existing views */
            body { background: #f5f5f5; }
            main.container { padding-top: 20px; }
            .brand-logo { font-weight: 600; }
            .sidenav .user-view { background: linear-gradient(135deg,#2196F3,#3F51B5); }
        </style>
    </head>
    <body>
        @auth
            <header>
                @include('layouts.navbars.navbar')
            </header>

            <ul id="slide-out" class="sidenav sidenav-fixed">
                @include('layouts.navbars.sidebar')
            </ul>

            <main class="container">
                <div class="row">
                    <div class="col s12">
                        @yield('content')
                    </div>
                </div>
            </main>

            @include('layouts.footer')

            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">@csrf</form>
        @else
            @include('layouts.navbars.navbar')
            <main class="container">
                @yield('content')
            </main>
            @include('layouts.footer')
        @endauth

        <!-- Scripts: jQuery and Materialize JS -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var elems = document.querySelectorAll('.sidenav');
                M.Sidenav.init(elems);
            });
        </script>

        @stack('js')
    </body>
</html>
