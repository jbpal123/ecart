<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - @yield('title')</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body>
    <div class="wrapper">
        @include('admin.layouts.nav')
        
        <div class="container-fluid">
            <div class="row">
                <main class="col-md-9 ms-sm-auto px-md-4 py-4">
                    {{-- @include('shared.alerts') --}}
                    @yield('content')
                </main>
            </div>
        </div>
    </div>
</body>
</html>