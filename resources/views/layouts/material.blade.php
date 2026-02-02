<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes">
    <title>Material Theme - @yield('title', 'Dashboard')</title>

    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700,300|Material+Icons" rel="stylesheet" type="text/css">

    <link rel="stylesheet" href="/vendor/material-dashboard/css/demo.css">
    <link rel="stylesheet" href="/vendor/material-dashboard/css/app.component.css">
    <link rel="stylesheet" href="/vendor/material-dashboard/css/navbar.component.css">
    <link rel="stylesheet" href="/vendor/material-dashboard/css/sidebar.component.css">
    <link rel="stylesheet" href="/vendor/material-dashboard/css/footer.component.css">
    
    <!-- Mobile Responsive CSS -->
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">

    <style>
        /* Mobile Responsive Styles */
        @media (max-width: 768px) {
            .wrapper .sidebar {
                width: 260px;
                transform: translateX(-260px);
                transition: transform 0.3s ease;
                position: fixed;
                z-index: 1050;
            }
            
            .wrapper .sidebar.active {
                transform: translateX(0);
            }
            
            .main-panel {
                width: 100% !important;
                margin-left: 0 !important;
            }
            
            .container-fluid {
                padding-left: 15px !important;
                padding-right: 15px !important;
            }
            
            .card {
                margin-bottom: 15px !important;
            }
            
            .card-body {
                padding: 15px !important;
            }
            
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            table {
                font-size: 0.875rem !important;
            }
            
            .btn {
                padding: 8px 12px !important;
                font-size: 0.875rem !important;
            }
            
            .form-control {
                font-size: 14px !important;
            }
            
            .navbar .navbar-brand {
                font-size: 18px !important;
            }
            
            .content {
                padding: 15px 0 !important;
            }
        }
        
        @media (max-width: 576px) {
            .container-fluid {
                padding-left: 10px !important;
                padding-right: 10px !important;
            }
            
            .card-body {
                padding: 10px !important;
            }
            
            table {
                font-size: 0.75rem !important;
            }
            
            .btn {
                padding: 6px 10px !important;
                font-size: 0.75rem !important;
            }
        }
    </style>

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
