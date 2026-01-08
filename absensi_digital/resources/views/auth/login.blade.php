@extends('layouts.app', ['page' => _('Login')])

@section('content')
<div class="row" style="margin-top:40px;">
    <div class="col s12 m6 offset-m3 l4 offset-l4">
        <div class="card z-depth-2">
            <div class="card-content">
                <span class="card-title" style="display:flex;align-items:center;gap:8px;">
                    <i class="material-icons">lock</i> Masuk
                </span>
                <p class="grey-text text-darken-1" style="margin-bottom:16px;">Gunakan akun Anda untuk mengakses dashboard absensi.</p>

                <form method="post" action="{{ route('login') }}">
                    @csrf

                    <div class="input-field">
                        <i class="material-icons prefix">email</i>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" class="validate @error('email') invalid @enderror" required autofocus>
                        <label for="email">Email</label>
                        @error('email')
                            <span class="helper-text red-text">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="input-field">
                        <i class="material-icons prefix">vpn_key</i>
                        <input id="password" type="password" name="password" class="validate @error('password') invalid @enderror" required>
                        <label for="password">Password</label>
                        @error('password')
                            <span class="helper-text red-text">{{ $message }}</span>
                        @enderror
                    </div>

                    <button type="submit" class="btn waves-effect waves-light teal" style="width:100%;">Masuk</button>
                </form>

                <div class="section" style="margin-top:12px; display:flex; justify-content:space-between; align-items:center;">
                    <span class="grey-text text-darken-1" style="font-size:12px;">Admin demo: admin@example.com / password</span>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="teal-text text-darken-2" style="font-size:12px;">Lupa password?</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
