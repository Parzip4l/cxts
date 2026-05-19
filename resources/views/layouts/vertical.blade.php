<!DOCTYPE html>
<html lang="en" @yield('html-attribute')>

<head>
    @include('layouts.partials/title-meta')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @include('layouts.partials/head-css')
</head>

<body>

    <div class="app-wrapper">

        @include('layouts.partials/sidebar')

        @include('layouts.partials/topbar')

        <div class="page-content">

            <div class="container-fluid">

                @yield('content')

            </div>

            @include('layouts.partials/footer')
        </div>

    </div>

    @auth
        @include('layouts.partials.floating-assistant')
    @endauth

    @include('layouts.partials/vendor-scripts')


</body>

</html>
