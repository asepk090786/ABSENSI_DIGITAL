@extends('layouts.app', ['page' => _('Login')])

@section('content')
<div class="row" style="margin-top:60px; margin-bottom:40px;">
    <div class="col s12 m6 offset-m3 l4 offset-l4">
        <div class="card" style="border-radius:8px;">
            <div class="card-content">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                    <span class="card-title" style="margin:0;">Masuk</span>
                </div>
                <p class="grey-text text-darken-1" style="margin-bottom:16px;">Login untuk mengelola absensi digital.</p>

                <form method="post" action="{{ route('login') }}">
                    @csrf

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" class="@error('email') invalid @enderror" required autofocus>
                        @error('email')
                            <span class="helper-text red-text">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input id="password" type="password" name="password" class="@error('password') invalid @enderror" required>
                        @error('password')
                            <span class="helper-text red-text">{{ $message }}</span>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary" style="width:100%;">Masuk</button>
                </form>

                <div class="section" style="margin-top:12px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px; font-size:12px; color:var(--muted);">
                        <span>Admin demo: admin@example.com / password</span>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" style="color:var(--primary);">Lupa password?</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
