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
@endsection
