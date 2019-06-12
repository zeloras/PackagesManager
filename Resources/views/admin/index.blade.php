@extends('admin.layouts.main')

@section('title', trans('packagesmanager::admin/sidenav.name'))

@section('content')
<section class="box-typical container">
    <header class="box-typical-header">
        <div class="tbl-row">
            <div class="tbl-cell tbl-cell-title">
                <ul class="navbar-nav mr-auto d-flex flex-row">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.packages.list') }}">
                            {{ trans('packagesmanager::admin/main.installed_packages') }}
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
                <h3>{{ trans('packagesmanager::admin/main.packages_list') }}</h3>
            </div>
        </div>
    </header>
    <div class="box-typical-body">
        <div class="table-responsive">
            @if(isset($list))
                @include('packagesmanager::admin.components.table', $list)
            @endif
        </div>
    </div>
</section>
@endsection