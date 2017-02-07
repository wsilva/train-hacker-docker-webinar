<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>@yield('title')</title>
    <link rel="stylesheet" type="text/css" href="/assets/bootstrap.min.css">
</head>
<body>
    <div class="container">
        @yield('content')
    </div>
    <footer class="bs-docs-footer center">
        <hr>
        <div class="container">
            <div class="row text-center">
                <h4>IP: {{ $ipaddr }} | Hostname: {{ $hostname }} </h4>
            </div>
        </div>
    </footer>
    <script type="text/javascript" src="/assets/jquery.js"></script>
    <script type="text/javascript" src="/assets/bootstrap.min.js"></script>
</body>
</html> 