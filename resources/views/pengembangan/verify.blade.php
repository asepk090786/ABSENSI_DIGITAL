@extends('layouts.public')

@section('title','Verifikasi Sertifikat')

@section('content')
    @if(!$valid)
        <div class="alert alert-danger">Kode tidak valid</div>
    @else
        <h3>Sertifikat Valid</h3>
        <div class="table-responsive">
            <table class="table table-bordered mt-3">
                <thead>
                    <tr>
                        <th>Tipe</th>
                        <th>Nama</th>
                        <th>Nama Kegiatan</th>
                        <th>Tema Kegiatan</th>
                        <th>Waktu Pelaksanaan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $participant_role ?? 'Peserta' }}</td>
                        <td>{{ $participant_name ?? ($cert->peserta_name ?? ($cert->peserta_type . ' #' . $cert->peserta_id)) }}</td>
                        <td>{{ $item->nama_kegiatan ?? ('Kegiatan #' . $cert->pengembangan_id) }}</td>
                        <td>{{ $item->tema_kegiatan ?? '-' }}</td>
                        <td>
                            @if(!empty($item->tanggal_mulai))
                                {{ optional($item->tanggal_mulai)->format('d-m-Y') }}
                                @if(!empty($item->tanggal_selesai) && $item->tanggal_selesai != $item->tanggal_mulai)
                                    - {{ optional($item->tanggal_selesai)->format('d-m-Y') }}
                                @endif
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            <p><strong>Diterbitkan:</strong> {{ $cert->created_at }}</p>
            <p><strong>Terverifikasi:</strong> {{ $cert->verified_at ? $cert->verified_at->format('Y-m-d H:i:s') : 'Belum' }}</p>
        </div>
        @if($cert->bukti_dukung_daftar_hadir || $cert->bukti_dukung_dokumentasi || $cert->bukti_dukung_materi)
            <div class="mt-4">
                <h5>Bukti Dukung</h5>
                <ul class="list-group">
                    @if($cert->bukti_dukung_daftar_hadir)
                        <li class="list-group-item">
                            Daftar Hadir: <a href="{{ asset('storage/' . $cert->bukti_dukung_daftar_hadir) }}" target="_blank">Lihat file</a>
                        </li>
                    @endif
                    @if($cert->bukti_dukung_dokumentasi)
                        <li class="list-group-item">
                            Dokumentasi:
                            <ul class="list-unstyled mb-0">
                                @foreach((array) $cert->bukti_dukung_dokumentasi as $path)
                                    <li><a href="{{ asset('storage/' . $path) }}" target="_blank">{{ basename($path) }}</a></li>
                                @endforeach
                            </ul>
                        </li>
                    @endif
                    @if($cert->bukti_dukung_materi)
                        <li class="list-group-item">
                            Materi:
                            <ul class="list-unstyled mb-0">
                                @foreach((array) $cert->bukti_dukung_materi as $path)
                                    <li><a href="{{ asset('storage/' . $path) }}" target="_blank">{{ basename($path) }}</a></li>
                                @endforeach
                            </ul>
                        </li>
                    @endif
                </ul>
            </div>
        @endif
    @endif
@endsection
