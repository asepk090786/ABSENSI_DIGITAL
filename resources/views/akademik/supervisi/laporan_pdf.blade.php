<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Supervisi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #333;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }
        .header p {
            margin: 5px 0;
            font-size: 12px;
        }
        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        .section-title {
            background-color: #f0f0f0;
            padding: 10px;
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 10px;
            border-left: 4px solid #0066cc;
        }
        .info-box {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .info-item {
            flex: 1;
            padding-right: 20px;
            font-size: 12px;
        }
        .info-label {
            font-weight: bold;
            color: #555;
            display: block;
            margin-bottom: 3px;
        }
        .info-value {
            color: #000;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-bottom: 15px;
        }
        .table th {
            background-color: #e6f0ff;
            padding: 8px;
            text-align: left;
            border: 1px solid #999;
            font-weight: bold;
        }
        .table td {
            padding: 8px;
            border: 1px solid #ccc;
            vertical-align: top;
        }
        .table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            color: white;
        }
        .badge-success {
            background-color: #28a745;
        }
        .badge-info {
            background-color: #17a2b8;
        }
        .badge-warning {
            background-color: #ffc107;
            color: #333;
        }
        .text-content {
            font-size: 11px;
            line-height: 1.5;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .footer {
            margin-top: 40px;
            text-align: right;
            font-size: 11px;
        }
        .signature-box {
            display: inline-block;
            width: 200px;
            text-align: center;
            border-top: 1px solid #000;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN SUPERVISI PEMBELAJARAN</h1>
        <p>Sistem Monitoring & Evaluasi Pembelajaran (SIMADIS)</p>
        <p style="font-size: 10px; color: #666;">Tanggal Cetak: {{ now()->format('d-m-Y H:i') }}</p>
    </div>

    <!-- IDENTITAS SUPERVISI -->
    <div class="section">
        <div class="section-title">📋 IDENTITAS SUPERVISI</div>
        <div class="info-box">
            <div class="info-item">
                <span class="info-label">Guru:</span>
                <span class="info-value">{{ $supervisi->guru->user->name ?? $supervisi->guru->nama }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Mata Pelajaran:</span>
                <span class="info-value">{{ $supervisi->mataPelajaran->nama_mapel ?? '-' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Kelas:</span>
                <span class="info-value">{{ $supervisi->kelas->nama_kelas ?? '-' }}</span>
            </div>
        </div>
        <div class="info-box">
            <div class="info-item">
                <span class="info-label">Tanggal Supervisi:</span>
                <span class="info-value">{{ $supervisi->tanggal->format('d-m-Y') }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Supervisor:</span>
                <span class="info-value">{{ $supervisi->supervisor->user->name ?? $supervisi->supervisor->nama ?? '-' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Status:</span>
                <span class="badge badge-success">{{ $supervisi->status ?? 'Selesai' }}</span>
            </div>
        </div>
    </div>

    <!-- TUJUAN & FOKUS -->
    <div class="section">
        <div class="section-title">🎯 TUJUAN & FOKUS SUPERVISI</div>
        <div class="info-box">
            <div class="info-item">
                <span class="info-label">Tujuan:</span>
                <div class="text-content">{{ $supervisi->tujuan ?? '-' }}</div>
            </div>
        </div>
        <div class="info-box">
            <div class="info-item">
                <span class="info-label">Fokus Observasi:</span>
                <div class="text-content">{{ $supervisi->fokus ?? '-' }}</div>
            </div>
        </div>
    </div>

    <!-- HASIL OBSERVASI -->
    @if($supervisi->observationItems->count() > 0)
    <div class="section">
        <div class="section-title">📊 HASIL OBSERVASI</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Indikator</th>
                    <th style="width: 60px;">Skor</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($supervisi->observationItems as $item)
                <tr>
                    <td>
                        <strong>{{ $item->indicator->indikator ?? '-' }}</strong><br>
                        <small style="color: #666;">{{ $item->indicator->deskripsi ?? '' }}</small>
                    </td>
                    <td style="text-align: center;">
                        @if($item->skor)
                            <span class="badge badge-info">{{ $item->skor }}/5</span>
                        @else
                            <span style="color: #999;">-</span>
                        @endif
                    </td>
                    <td>{{ $item->catatan ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- CATATAN OBJEKTIF -->
    @if($supervisi->catatan_objektif)
    <div class="section">
        <div class="section-title">📝 CATATAN OBJEKTIF PENGAMATAN</div>
        <div class="text-content">{{ $supervisi->catatan_objektif }}</div>
    </div>
    @endif

    <!-- REFLEKSI & FEEDBACK -->
    @if($supervisi->postConference)
    <div class="section">
        <div class="section-title">🤝 REFLEKSI & FEEDBACK POST-CONFERENCE</div>

        @if($supervisi->postConference->refleksi_guru)
        <div style="margin-bottom: 15px;">
            <span class="info-label">Refleksi Guru:</span>
            <div class="text-content">{{ $supervisi->postConference->refleksi_guru }}</div>
        </div>
        @endif

        @if($supervisi->postConference->refleksi_supervisor)
        <div style="margin-bottom: 15px;">
            <span class="info-label">Refleksi Supervisor:</span>
            <div class="text-content">{{ $supervisi->postConference->refleksi_supervisor }}</div>
        </div>
        @endif

        @if($supervisi->postConference->feedback)
        <table class="table">
            <tr>
                <td style="width: 40%;">
                    <strong>Kekuatan Guru:</strong><br>
                    <div class="text-content">{{ $supervisi->postConference->feedback->kekuatan ?? '-' }}</div>
                </td>
                <td>
                    <strong>Area Pengembangan:</strong><br>
                    <div class="text-content">{{ $supervisi->postConference->feedback->area_pengembangan ?? '-' }}</div>
                </td>
            </tr>
        </table>

        @if($supervisi->postConference->feedback->umpan_balik)
        <div style="margin-bottom: 15px;">
            <span class="info-label">Umpan Balik Keseluruhan:</span>
            <div class="text-content">{{ $supervisi->postConference->feedback->umpan_balik }}</div>
        </div>
        @endif
        @endif
    </div>
    @endif

    <!-- RENCANA TINDAK LANJUT -->
    @if($supervisi->postConference && $supervisi->postConference->actionPlans->count() > 0)
    <div class="section">
        <div class="section-title">📋 RENCANA TINDAK LANJUT</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Tujuan</th>
                    <th>Aktivitas</th>
                    <th>PJ</th>
                    <th>Target</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($supervisi->postConference->actionPlans as $plan)
                <tr>
                    <td>{{ Str::limit($plan->tujuan, 40) }}</td>
                    <td>{{ Str::limit($plan->aktivitas, 40) }}</td>
                    <td>
                        {{ $plan->penanggungJawab->user->name ?? $plan->penanggungJawab->nama ?? '-' }}
                    </td>
                    <td>{{ $plan->target_selesai->format('d-m-Y') }}</td>
                    <td>
                        <span class="badge" style="background-color: {{ $plan->status == 'Selesai' ? '#28a745' : ($plan->status == 'Berjalan' ? '#ffc107' : '#6c757d') }};">
                            {{ $plan->status }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- MONITORING PROGRESS -->
    @if($supervisi->postConference && $supervisi->postConference->actionPlans->count() > 0)
        @php
            $hasMonitoring = $supervisi->postConference->actionPlans->some(fn($p) => $p->monitorings->count() > 0);
        @endphp
        @if($hasMonitoring)
        <div class="section">
            <div class="section-title">📈 MONITORING PROGRESS TINDAK LANJUT</div>
            @foreach($supervisi->postConference->actionPlans as $plan)
                @if($plan->monitorings->count() > 0)
                <div style="margin-bottom: 15px; padding: 10px; background-color: #f9f9f9; border-left: 3px solid #0066cc;">
                    <strong>{{ Str::limit($plan->tujuan, 50) }}</strong><br>
                    <small style="color: #666;">Progress terakhir:</small>
                    <table class="table" style="margin-top: 8px;">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Progress</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($plan->monitorings->sortByDesc('created_at') as $monitoring)
                            <tr>
                                <td>{{ $monitoring->tanggal_monitoring->format('d-m-Y') }}</td>
                                <td style="text-align: center;">
                                    <span class="badge badge-info">{{ $monitoring->progress_persen }}%</span>
                                </td>
                                <td>{{ Str::limit($monitoring->catatan, 50) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            @endforeach
        </div>
        @endif
    @endif

    <!-- FOOTER -->
    <div class="footer">
        <p>Laporan ini adalah dokumen resmi hasil supervisi pembelajaran.</p>
        <div class="signature-box">
            Supervisor<br><br><br>
            {{ $supervisi->supervisor->user->name ?? $supervisi->supervisor->nama ?? '________________________' }}
        </div>
    </div>
</body>
</html>
