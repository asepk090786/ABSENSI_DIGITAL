@extends('layouts.app', ['pageSlug' => 'dashboard'])

@section('title','Dashboard')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-chart">
                <div class="card-header ">
                    <div class="row">
                        <div class="col-sm-6 text-left">
                            <h5 class="card-category">Welcome</h5>
                            <h2 class="card-title">Absensi Digital</h2>
                        </div>
                        <div class="col-sm-6 text-right">
                            <p class="card-text">Anda masuk sebagai {{ auth()->user()->name ?? 'User' }}.</p>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <p>Gunakan menu di samping untuk mengelola jam belajar, agenda, dan absensi.</p>
                </div>
            </div>
        </div>
    </div>

    @if(isset($guru) || isset($siswa) || isset($absensi))
    <div class="row">
        <div class="col-md-4">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5">
                            <div class="icon-big text-center icon-warning">
                                <i class="nc-icon nc-single-02 text-warning"></i>
                            </div>
                        </div>
                        <div class="col-7">
                            <div class="numbers">
                                <p class="card-category">Guru</p>
                                <p class="card-title">{{ $guru ?? 0 }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5">
                            <div class="icon-big text-center icon-info">
                                <i class="nc-icon nc-hat-3 text-info"></i>
                            </div>
                        </div>
                        <div class="col-7">
                            <div class="numbers">
                                <p class="card-category">Siswa</p>
                                <p class="card-title">{{ $siswa ?? 0 }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5">
                            <div class="icon-big text-center icon-success">
                                <i class="nc-icon nc-check-2 text-success"></i>
                            </div>
                        </div>
                        <div class="col-7">
                            <div class="numbers">
                                <p class="card-category">Total Absensi</p>
                                <p class="card-title">{{ $absensi ?? 0 }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
@endsection
