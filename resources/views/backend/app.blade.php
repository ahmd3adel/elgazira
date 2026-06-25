<!DOCTYPE html>
<html>
@include('backend.layouts.partials.styles')
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

@include('backend.layouts.partials.navbar')

<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="index3.html" class="brand-link">
        <img src="{{ asset('assets/backend/dist/img/logo.png')}}" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">{{config('app.name')}}</span>
    </a>
    @include('backend.layouts.partials.sidebar')
</aside>

<div class="content-wrapper" style="min-height: calc(100vh - 120px); display: flex; flex-direction: column;">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"> @yield('breadcrumb-title')</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        @yield('breadcrumb')
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards (اختيارية) -->
    @hasSection('stats_title')
    <div class="row mb-3" id="statsCards">
        <div class="col-12 col-sm-6 col-md-4">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-primary elevation-1">
                    @yield('stats_icon', '<i class="fas fa-chart-line"></i>') 
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">إجمالي @yield('stats_title')</span>
                    <span class="info-box-number" id="totalCount">0</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-success elevation-1"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">@yield('stats_title') النشطة</span>
                    <span class="info-box-number" id="activeCount">0</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-ban"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">@yield('stats_title') المعطلة</span>
                    <span class="info-box-number" id="inactiveCount">0</span>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Main content - ياخذ المساحة المتبقية -->
    <section class="content" style="flex: 1;">
        @yield('content')
    </section>
</div>

<footer class="main-footer" style="position: relative; margin-top: 0; background: #fff; border-top: 1px solid #dee2e6;">
    <strong>Copyright &copy; 2014-2019 <a href="http://adminlte.io">AdminLTE.io</a>.</strong>
    All rights reserved.
    <div class="float-right d-none d-sm-inline-block">
        <b>Version</b> 3.0.0-rc.1
    </div>
</footer>

<aside class="control-sidebar control-sidebar-dark"></aside>
</div>

<!-- ========== الـ Scripts الأساسية ========== -->
<script src="{{ asset('assets/backend/plugins/jquery/jquery.min.js')}}"></script>
<script src="{{ asset('assets/backend/plugins/jquery-ui/jquery-ui.min.js')}}"></script>
<script>
    $.widget.bridge('uibutton', $.ui.button)
</script>

<script src="{{ asset('assets/backend/plugins/bootstrap/js/bootstrap.bundle.min.js')}}"></script>

<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
<!-- تحميل مكتبة Moment.js من السيرفر العالمي (CDN) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="{{ asset('assets/backend/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js')}}"></script>
<script src="{{ asset('assets/backend/dist/js/adminlte.js')}}"></script>
<script src="{{ asset('assets/backend/dist/js/demo.js')}}"></script>

@if(Route::currentRouteName() == 'admin.dashboard' || request()->is('admin/dashboard*'))
    @endif

@stack('custom-js')
<!-- في نهاية body، قبل @stack('custom-js') -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

<script>
    // إعدادات toastr
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-left",
        "timeOut": "5000",
        "extendedTimeOut": "2000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };
    
    // عرض رسائل الجلسة (Session)
    @if(session('success'))
        toastr.success('{{ session('success') }}');
    @endif
    
    @if(session('error'))
        toastr.error('{{ session('error') }}');
    @endif
    
    @if($errors->any())
        @foreach($errors->all() as $error)
            toastr.error('{{ $error }}');
        @endforeach
    @endif
</script>
</body>
</html>