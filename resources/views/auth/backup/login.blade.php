@extends('layouts.app')

@section('title','Login - Absensi Digital')

@section('content')
    <div class="d-flex flex-row align-items-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-5">
                    <div class="card mt-5 shadow-lg">
                        <div class="card-body p-4">
                            <h4 class="card-title mb-4">Masuk ke Absensi Digital</h4>

                            @if($errors->any())
                                <div class="alert alert-danger">{{ $errors->first() }}</div>
                            @endif

                            <form method="POST" action="{{ route('login.post') }}">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input name="email" type="email" value="{{ old('email') }}" required class="form-control" placeholder="you@example.com" />
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Password</label>
                                    <input name="password" type="password" required class="form-control" placeholder="••••••••" />
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                                        <label class="form-check-label" for="remember">Ingat saya</label>
                                    </div>
                                    <button class="btn btn-primary">Masuk</button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
