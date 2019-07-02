@extends('admin.layouts.main')

@section('title', \Translate::get('packagesmanager::admin/sidenav.name'))

@section('content')
    <section class="box-typical container">
        <header class="box-typical-header">
            <div class="tbl-row">
                <div class="tbl-cell tbl-cell-title">
                    <ul class="navbar-nav mr-auto d-flex flex-row">
                        <li class="nav-item">
                            <a class="nav-link active" href="{{ route('admin.packages') }}">
                                {{ \Translate::get('packagesmanager::admin/main.packages_list') }}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </header>
    </section>

    <section class="box-typical container pb-3">
        <header class="box-typical-header">
            <div class="tbl-row">
                <div class="tbl-cell tbl-cell-title">
                    <h3>{{ \Translate::get('packagesmanager::admin/main.installed_packages') }}</h3>
                </div>
            </div>
        </header>
        <div class="box-typical-body">
            <div class="table-responsive">
                @if(isset($list) && count($list))
                    @include('packagesmanager::admin.components.table', $list)
                @endif
            </div>
        </div>
    </section>
@endsection