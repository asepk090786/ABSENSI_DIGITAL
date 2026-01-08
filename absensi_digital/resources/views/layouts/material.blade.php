<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Material Theme - @yield('title', 'Dashboard')</title>

    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700,300|Material+Icons" rel="stylesheet" type="text/css">

    <link rel="stylesheet" href="/vendor/material-dashboard/css/demo.css">
    <link rel="stylesheet" href="/vendor/material-dashboard/css/app.component.css">
    <link rel="stylesheet" href="/vendor/material-dashboard/css/navbar.component.css">
    <link rel="stylesheet" href="/vendor/material-dashboard/css/sidebar.component.css">
    <link rel="stylesheet" href="/vendor/material-dashboard/css/footer.component.css">

    @stack('styles')
</head>
<body>
    <div class="wrapper">
        @includeWhen(View::exists('partials.sidebar'),'partials.sidebar')

        <div class="main-panel">
            @includeWhen(View::exists('partials.navbar'),'partials.navbar')

            <div class="content">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </div>

            @includeWhen(View::exists('partials.footer'),'partials.footer')
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.1/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.6.1/js/bootstrap.min.js"></script>
    @stack('scripts')
</body>
</html>
