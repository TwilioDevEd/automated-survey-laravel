<html>
    <head>
        <link href="//netdna.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet">
        <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet">
        <link href="{{ asset('assets/css/main.css') }}" rel="stylesheet">
        <title>Automated surveys - @yield('title', 'surveys')</title>
    </head>
    <body>
        <nav class="navbar navbar-default">
            <div class="navbar-header">
                <a class="navbar-brand" href="#">Automated surveys</a>
            </div>
        </nav>
        <div class="container-fluid">
            @yield('content')
        </div>
        <footer class="container text-center">
            Made with <i class="fa fa-heart"></i> by your pals
            <a href="http://www.twilio.com">@twilio</a>
        </footer>
        <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
        <script src="//netdna.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.8.3/underscore-min.js"></script>
        <script src="{{ asset('assets/js/main.js') }}"></script>
        @yield('scripts')
    </body>
</html>
