@extends('layouts.app')

@section('title','Dashboard')

@section('content')
    <div class="card">
        <div class="card-body">
            <h4 class="card-title">Dashboard</h4>
            <p class="card-text">Anda masuk sebagai {{ auth()->user()->name ?? 'User' }}.</p>
        </div>
    </div>
@endsection
