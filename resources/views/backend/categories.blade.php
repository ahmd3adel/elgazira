@extends('backend.app')
@section('title', 'إدارة المهن الرئيسية')
@section('breadcrumb-title', 'إدارة المهن')
@section('breadcrumb')
    <li class="breadcrumb-item active">المهن الرئيسية</li>
@endsection

@push('custom-css')
    @include('backend.categories.partials.styles')
@endpush

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-briefcase ml-2"></i>
                    قائمة المهن الرئيسية
                </h3>
                <div class="card-tools">
                    @include('backend.categories.partials.export-buttons')
                    <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#addProfessionModal">
                        <i class="fas fa-plus"></i> إضافة مهنة جديدة
                    </button>
                </div>
            </div>
            <div class="card-body">
                @include('backend.categories.partials.table')
            </div>
        </div>
    </div>
@endsection

{{-- المودالات --}}
@include('backend.categories.partials.modals.add')
@include('backend.categories.partials.modals.edit')

@push('custom-js')
    @include('backend.categories.partials.scripts.datatable')
    @include('backend.categories.partials.scripts.modals')
    @include('backend.categories.partials.scripts.exports')
    @include('backend.categories.partials.scripts.responsive')
@endpush