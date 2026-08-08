@extends('layouts.app', ['pageSlug' => 'setting-pengaturan'])

@section('title','Pengaturan')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Pengaturan</h3>
    </div>

    <div class="card">
        <div class="card-body">
            <ul class="nav nav-tabs mb-3" id="pengaturanTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <a href="{{ route('setting.absensi') }}" class="nav-link {{ request()->routeIs('setting.absensi*') ? 'active' : '' }}" id="absensi-tab">Pengaturan Absensi</a>
                </li>
                <li class="nav-item" role="presentation">
                    <a href="{{ route('setting.menu') }}" class="nav-link {{ request()->routeIs('setting.menu*') ? 'active' : '' }}" id="menu-tab">Pengaturan Menu</a>
                </li>
            </ul>

            <div class="tab-content" id="pengaturanTabContent">
                <div class="tab-pane fade {{ request()->routeIs('setting.absensi*') ? 'show active' : '' }}" id="absensi" role="tabpanel">
                    @include('setting.absensi', ['settings' => $absensiSettings ?? []])
                </div>
                <div class="tab-pane fade {{ request()->routeIs('setting.menu*') ? 'show active' : '' }}" id="menu" role="tabpanel">
                    @include('setting.menu', ['menuVisibility' => $menuVisibility ?? [], 'standardGuruMenus' => $standardGuruMenus ?? [], 'standardSiswaMenus' => $standardSiswaMenus ?? []])
                </div>
            </div>
        </div>
    </div>
@endsection
