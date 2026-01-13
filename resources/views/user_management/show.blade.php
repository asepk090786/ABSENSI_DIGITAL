@extends('layouts.app')

@section('title','Detail Pengguna')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Detail Pengguna</h4>
                <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <tbody>
                            <tr><th width="200">Nama</th><td>{{ $user->name }}</td></tr>
                            <tr><th>NIP</th><td>{{ $user->nip ?? '-' }}</td></tr>
                            <tr><th>Username</th><td>{{ $user->username }}</td></tr>
                            <tr><th>Email</th><td>{{ $user->email ?? '-' }}</td></tr>
                            <tr><th>Peran</th><td>{{ $user->role->role_name ?? '-' }}</td></tr>
                            <tr><th>Jenis Kelamin</th><td>{{ $user->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td></tr>
                            <tr><th>Status</th><td>{!! $user->is_active ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Nonaktif</span>' !!}</td></tr>
                            <tr><th>Terhubung</th>
                                <td>
                                    @if($user->guru_id)
                                        Guru (ID: {{ $user->guru_id }})
                                    @elseif($user->siswa_id)
                                        Siswa (ID: {{ $user->siswa_id }})
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr><th>Dibuat</th><td>{{ $user->created_at }}</td></tr>
                            <tr><th>Diperbarui</th><td>{{ $user->updated_at }}</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 d-flex gap-2">
                    <form action="{{ route('users.activate', $user->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-warning">
                            {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                        </button>
                    </form>
                    <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Yakin hapus akun ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
