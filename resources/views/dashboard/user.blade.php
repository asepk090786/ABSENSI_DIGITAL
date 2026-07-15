@extends('layouts.app')

@section('title','Dashboard Pengguna')

@section('content')
<div class="welcome-banner">
    <h3><i class="ti ti-user me-2"></i>Dashboard Pengguna</h3>
    <p>Selamat datang, {{ auth()->user()->name ?? 'Pengguna' }}. Anda hanya dapat mengakses fitur yang diizinkan oleh sistem.</p>
</div>
@endsection