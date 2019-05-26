@extends('admin.layouts.main')

@section('title', 'Package Manager')

@section('content')
    <div class="row">
        <div class="col">
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.setting.service.cache') }}">
                            Installed packages
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.setting.service.view') }}">
                            Packages list
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
@endsection