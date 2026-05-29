@extends('backend.app')
@section('title', 'لوحة تحكم الأدمن')
@section('breadcrumb-title', 'لوحة تحكم الأدمن')
@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard v1</li>    
@endsection

@section('content')
    <section class="content">
        <div class="container-fluid">
            <!-- Info Boxes -->
            @include('backend.dashboard.partials.stats.info-boxes')
            
            <!-- Small Boxes -->
            @include('backend.dashboard.partials.stats.small-boxes')
            
            <!-- Charts -->
            <div class="row">
                <div class="col-md-7">
                    @include('backend.dashboard.partials.charts.line-chart')
                </div>
                <div class="col-md-5">
                    @include('backend.dashboard.partials.charts.pie-chart')
                </div>
            </div>
            
            <!-- Recent Experts Table -->
            @include('backend.dashboard.partials.tables.recent-experts')
            
            <!-- Recent Bookings Table -->
            @include('backend.dashboard.partials.tables.recent-bookings')
            
            <!-- Quick Actions -->
            @include('backend.dashboard.partials.cards.quick-actions')
        </div>
    </section>
@endsection

@push('custom-js')
    @include('backend.dashboard.partials.scripts.dashboard-scripts')
@endpush