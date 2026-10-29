<!--
Polr, a minimalist URL shortening platform.
Copyright (C) 2013-2017 Chaoyi Zha

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
-->
@php
    $logged_in = !empty(session('username'));
    $is_admin = session('role') == 'admin';
    $menu = [
        ['url' => route('index'), 'active' => request()->is('/') || request()->is('shorten*'), 'icon' => 'add_link', 'label' => 'Buat Tautan'],
        ['url' => url('links'), 'active' => request()->is('links*'), 'icon' => 'link', 'label' => 'Tautan Saya'],
    ];
    if ($is_admin) {
        $menu[] = ['url' => route('admin'), 'active' => request()->is('admin*'), 'icon' => 'admin_panel_settings', 'label' => 'Admin'];
    }
@endphp
<!DOCTYPE html>
<html lang="id" ng-app="polr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>@section('title'){{env('APP_NAME')}}@show</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- Leave this for stats --}}
    <meta name="generator" content="Polr {{env('POLR_VERSION')}}" />
    @yield('meta')

    {{-- Favicon --}}
    <link rel="apple-touch-icon" sizes="180x180" href="/mids/assets/img/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/mids/assets/img/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/mids/assets/img/favicon-16x16.png">
    <link rel="shortcut icon" href="/mids/assets/img/favicon.ico">

    {{-- Vendor CSS --}}
    <link href="/mids/lib/@fortawesome/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="/mids/lib/animate.css/animate.min.css" rel="stylesheet">
    <link href="/mids/lib/ionicons/css/ionicons.min.css" rel="stylesheet">
    <link href="/mids/lib/select2/css/select2.min.css" rel="stylesheet">

    {{-- myITS Design System (DashForge) --}}
    <link rel="stylesheet" href="/mids/assets/css/dashforge.css">
    <link rel="stylesheet" href="/mids/assets/css/dashforge.sizexxl.css">
    <link rel="stylesheet" href="/mids/assets/css/mids.css">
    <link rel="stylesheet" href="/mids/assets/css/mids.light.css">
    {{-- toastr setelah DashForge agar .toast Bootstrap 4 tidak menimpa warna notifikasi --}}
    <link href="/css/toastr.min.css" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="/css/app.css" rel="stylesheet">
    @yield('css')
</head>

<body>
    <header class="navbar navbar-header navbar-header-fixed navbar-header-scroll pos-fixed z-index-2 wd-100p t-0 bd-0 shadow-none d-flex justify-content-end">
        <div class="d-flex align-items-center mg-r-20">
            @if ($logged_in)
            <div class="dropdown">
                <a href="" class="btn btn-sm btn-shadow-secondary bd-0 rounded-pill pd-y-2 pd-r-6 pd-l-5 gap-1" data-toggle="dropdown" data-display="static">
                    <div class="d-none d-sm-block mx-wd-100">
                        <p class="tx-medium tx-color-01 tx-13 tx-left text-truncate mg-r-2 mg-b-0 mg-x-5" title="{{ session('username') }}">
                            {{ session('username') }}
                        </p>
                    </div>
                    <div class="avatar avatar-sm wd-30 ht-30"><img src="/mids/assets/img/profile-default.png" class="rounded-circle wd-30 ht-30 bd" alt=""></div>
                </a>
                <div class="allow-focus dropdown-menu dropdown-menu-right overflow-y-auto scrollbar-rounded wd-300-f" style="max-height: calc(100vh - 70px);">
                    <div class="pd-y-15-f d-flex flex-column justify-content-center align-items-center gap-3">
                        <div class="avatar avatar-xl">
                            <img src="/mids/assets/img/profile-default.png" class="rounded-circle" alt="">
                        </div>
                        <div class="media-body wd-100p d-flex flex-column justify-content-center align-items-center">
                            <span class="tx-15 tx-figtree tx-semibold tx-center crop-text-2">{{ session('username') }}</span>
                            <span class="tx-center mg-b-1">{{ $is_admin ? 'Administrator' : (session('role_group') ?: 'Pengguna') }}</span>
                            <span class="tx-13 tx-normal tx-color-03 tx-center crop-text-1 mg-b-0 wd-100p">{{ session('email') }}</span>
                        </div>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a href="https://my.its.ac.id/" class="dropdown-item d-inline-flex align-items-center pd-y-15 tx-figtree tx-medium gap-2">
                        <span class="ms-rounded">arrow_back</span> Ke myITS Portal
                    </a>
                    <a href="{{ route('logout') }}" class="dropdown-item d-inline-flex align-items-center pd-y-15 tx-figtree tx-medium gap-2 tx-danger">
                        <span class="ms-rounded">logout</span> Keluar
                    </a>
                </div>
            </div>
            @else
            <a href="{{ route('login') }}" class="btn btn-sm btn-primary rounded-pill d-inline-flex align-items-center gap-1">
                <span class="ms-rounded tx-18">login</span> Masuk
            </a>
            @endif
        </div>
    </header>

    <div class="pos-fixed t-0 l-0 ht-60 z-index-3 d-inline-flex align-items-center">
        <div class="pos-relative d-flex align-items-center mg-l-10 mg-lg-l-20">
            @if ($logged_in)
            <div class="pos-absolute l-0 d-lg-none">
                <a href="#menu-sidebar" class="btn btn-icon btn-sm off-canvas-menu rounded-pill d-flex align-items-center bd-0 bg-transparent"><span class="sidebar-item-icon ms-rounded tx-18">menu</span></a>
            </div>
            @endif
            <a href="{{ route('index') }}" class="d-flex justify-content-center align-items-center @if ($logged_in) mg-l-45 @else mg-l-10 @endif mg-lg-l-0 gap-1 pd-l-3 rounded-5" style="transition: margin-left .25s ease-in-out;">
                <img src="/mids/assets/img/logo-myits.svg" class="wd-50 mg-t-4 mg-md-t-4 logo-myits" alt="myITS">
                <p class="tx-figtree tx-medium tx-color-01 mg-b-0 text-truncate" style="font-size: 19px; max-width: 170px;" title="{{ env('APP_NAME') }}">{{ env('APP_SHORT_NAME', 'Link Shortener') }}</p>
            </a>
        </div>
    </div>

    @if ($logged_in)
    {{-- Sidebar versi off-canvas mobile --}}
    <div id="menu-sidebar" class="off-canvas d-flex flex-column d-lg-none" style="border: 0px !important;">
        <div class="off-canvas-header bd-b-0 pd-x-20">
            <p class="tx-figtree tx-medium tx-16 tx-color-01 mg-b-0">Menu</p>
            <a href="#" class="close ml-auto"><span class="sidebar-item-icon ms-rounded tx-20">close</span></a>
        </div>
        <div class="off-canvas-body pd-t-5 pd-md-t-10 scrollbar-rounded" style="overflow-y: auto;">
            <ul class="sidebar-nav">
                @include('snippets.sidebar_items', ['menu' => $menu])
            </ul>
        </div>
    </div>

    {{-- Sidebar versi desktop --}}
    <aside class="aside aside-fixed pd-t-60 z-index-2 d-flex flex-column" id="aside-menu">
        <div class="sidebar-nav pd-b-20 scrollbar-rounded" style="overflow-y: auto;">
            @include('snippets.sidebar_items', ['menu' => $menu])
        </div>
    </aside>
    @endif

    <div class="content pd-0">
        <div class="content-body mn-ht-100v d-flex flex-column">
            <div class="container pd-x-0 mg-t-40 mg-sm-t-35">
                {{-- Judul halaman --}}
                <div class="mg-b-10">
                    <div class="d-flex align-items-center justify-content-between @if ($logged_in) pos-static pos-lg-fixed t-0 l-0 ht-60 pd-l-0 pd-lg-l-25 mg-t-5 mg-lg-t-0 mg-b-15 mg-lg-b-0 page-title-bar @else mg-t-30 mg-b-15 @endif" style="min-height: 50px;">
                        <div class="d-flex align-items-center justify-content-start">
                            <h4 class="tx-figtree tx-medium mg-b-0">@yield('page_title')</h4>
                        </div>
                    </div>
                </div>

                @hasSection('page_actions')
                <div class="d-flex justify-content-end flex-wrap gap-2 mg-b-15">
                    @yield('page_actions')
                </div>
                @endif

                {{-- Tempat konten --}}
                @yield('content')
            </div>

            <footer class="mg-t-auto">
                <div class="container pd-x-0 mn-ht-50 d-flex justify-content-between align-items-center flex-wrap flex-sm-nowrap mg-b-10-f">
                    <div class="wd-100p wd-sm-auto d-flex justify-content-center">
                        <span class="tx-medium tx-color-03 tx-13">&copy; {{ date('Y') }} Institut Teknologi Sepuluh Nopember</span>
                    </div>
                    <div class="wd-100p wd-sm-auto d-flex justify-content-center">
                        <img src="/mids/assets/img/advhum-blue.png" height="60" alt="ITS Advancing Humanity">
                    </div>
                </div>
            </footer>
        </div>
    </div>

    {{-- Load JavaScript dependencies --}}
    <script src="/js/constants.js"></script>
    <script src="/mids/lib/jquery/jquery.min.js"></script>
    <script src="/mids/lib/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="/mids/lib/js-cookie/js.cookie.js"></script>
    <script src="/mids/lib/select2/js/select2.min.js"></script>
    <script src="/mids/assets/js/mids.js"></script>
    <script src="/js/angular.min.js"></script>
    <script src="/js/toastr.min.js"></script>
    <script src="/js/base.js"></script>

    <script>
    // Ikon Material Symbols jangan ikut diterjemahkan browser
    $('.ms-rounded').attr('translate', 'no');

    @if (Session::has('info'))
        toastr["info"](`{{ str_replace('`', '\`', session('info')) }}`, "Info")
    @endif
    @if (Session::has('error'))
        toastr["error"](`{{str_replace('`', '\`', session('error')) }}`, "Error")
    @endif
    @if (Session::has('warning'))
        toastr["warning"](`{{ str_replace('`', '\`', session('warning')) }}`, "Warning")
    @endif
    @if (Session::has('success'))
        toastr["success"](`{{ str_replace('`', '\`', session('success')) }}`, "Success")
    @endif

    @if (count($errors) > 0)
        // Handle validation errors
        @foreach ($errors->all() as $error)
            toastr["error"](`{{ str_replace('`', '\`', $error) }}`, "Error")
        @endforeach
    @endif
    </script>

    @yield('js')
</body>
</html>
