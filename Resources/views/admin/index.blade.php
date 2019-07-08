@extends('admin.layouts.main')

@section('title', \Translate::get('module_packagesmanager::admin/sidenav.name'))

@section('content')
    {{--

    <section class="box-typical container">
    <header class="box-typical-header">
        <div class="tbl-row">
            <div class="tbl-cell tbl-cell-title">
                <ul class="navbar-nav mr-auto d-flex flex-row">
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('admin.packages') }}">
                            {{ \Translate::get('module_packagesmanager::admin/main.packages_list') }}
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </header>
</section> --}}

<section class="box-typical">
    <div class="box-typical-body">
        <section class="tabs-section tab-section__no-border">
            <div class="tabs-section-nav">
                <div class="tbl">
                    <ul class="nav" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active show" href="#tabs-2-tab-1" role="tab" data-toggle="tab" aria-selected="true">
                                <span class="nav-link-in">
                                    {{ \Translate::get('module_packagesmanager::admin/main.installed_official_packages') }}
                                </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#tabs-2-tab-2" role="tab" data-toggle="tab" aria-selected="false">
                                <span class="nav-link-in">
                                    {{ \Translate::get('module_packagesmanager::admin/main.installed_unofficial_packages') }}
                                </span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div><!--.tabs-section-nav-->

            <div class="tab-content">
                <div role="tabpanel" class="tab-pane fade in active show" id="tabs-2-tab-1">
                    <div class="table-responsive">
                        @if(isset($list))
                            @include('packagesmanager::admin.components.table', $list)
                        @endif
                    </div>
                </div><!--.tab-pane-->
                <div role="tabpanel" class="tab-pane fade in active show" id="tabs-2-tab-2">

                </div><!--.tab-pane-->
            </div><!--.tab-content-->
        </section>
    </div>
</section>
@endsection