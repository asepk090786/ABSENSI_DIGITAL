@if($containerId)
    <div id="{{ $containerId }}" style="min-height:520px;"></div>
@else
<div class="row onlyoffice-fullscreen">
    <div class="col-md-12">
        <div class="card card-body onlyoffice-card" style="padding:0;">
            <div class="card-body p-0 onlyoffice-editor-container">
                <style>
                    .onlyoffice-fullscreen {
                        display: flex;
                        flex-direction: column;
                        width: 100%;
                        min-height: 720px;
                        height: min(82vh, 920px);
                    }
                    .onlyoffice-card {
                        display: flex;
                        flex-direction: column;
                        flex: 1 1 auto;
                        min-height: 720px;
                        height: 100%;
                    }
                    .onlyoffice-editor-container {
                        flex: 1 1 auto;
                        display: flex;
                        min-height: 0;
                        height: 100%;
                    }
                    #{{ $componentId }} {
                        flex: 1 1 auto;
                        width: 100% !important;
                        height: 100% !important;
                        min-height: 0 !important;
                        min-height: 680px !important;
                        overflow: hidden !important;
                    }
                    @media (max-height: 900px) {
                        .onlyoffice-fullscreen { min-height: 640px; height: 74vh; }
                        .onlyoffice-card { min-height: 640px; }
                        #{{ $componentId }} { min-height: 600px !important; }
                    }
                    @media (max-width: 768px) {
                        .onlyoffice-fullscreen { min-height: 560px; height: 68vh; }
                        .onlyoffice-card { min-height: 560px; }
                        #{{ $componentId }} { min-height: 520px !important; }
                    }
                </style>
                <div id="{{ $componentId }}"></div>
            </div>
        </div>
    </div>
</div>
@endif

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const onlyOfficeServerUrl = {!! json_encode(rtrim(app(\App\Services\SettingsManager::class)->get('onlyoffice.server_url', config('services.onlyoffice.server_url', env('ONLYOFFICE_SERVER_URL', env('ONLYOFFICE_URL', ''))))), JSON_UNESCAPED_SLASHES) !!};
        const onlyOfficeProxyBase = {!! json_encode(url('onlyoffice')) !!};
        const documentKey = {!! json_encode($documentKey ?? sha1($fileUrl . $title . $componentId)) !!};
        const fileUrl = {!! json_encode($fileUrl) !!};
        const callbackUrl = {!! json_encode($callbackUrl) !!};
        const fileType = {!! json_encode($fileType) !!};
        const jwtToken = {!! json_encode($token ?? null) !!};
        const title = {!! json_encode($title) !!};
        const editorMode = {!! json_encode($editorMode) !!};
        const userId = {!! json_encode($userId) !!};
        const userName = {!! json_encode($userName) !!};
        const componentId = {!! json_encode($componentId) !!};
        const targetId = {!! json_encode($containerId ?: $componentId) !!};

        if (!onlyOfficeServerUrl || !fileUrl) {
            document.getElementById(targetId).innerHTML = '<div class="alert alert-warning m-4">OnlyOffice tidak dapat dimuat karena konfigurasi tidak lengkap.</div>';
            return;
        }

        const apiUrl = onlyOfficeServerUrl.replace(/\/+$/, '') + '/web-apps/apps/api/documents/api.js';

        let onlyOfficeEditor = null;

        const script = document.createElement('script');
        script.src = apiUrl;
        script.onload = function () {
            const editorConfig = {
                mode: editorMode,
                user: {
                    id: userId,
                    name: userName,
                },
                customization: {
                    forcesave: true,
                },
                lang: 'id',
                region: 'id',
                events: {
                    onAppReady: function () {
                        if (onlyOfficeEditor && typeof onlyOfficeEditor.serviceCommand === 'function') {
                            onlyOfficeEditor.serviceCommand('info', {
                                key: documentKey,
                                userdata: 'rpp-create',
                            });
                        }

                        fillOnlyOfficePlaceholders();
                    },
                    onInfo: function (event) {
                        const data = event?.data || {};
                        console.log('OnlyOffice info response:', data);

                        const users = Array.isArray(data.users) ? data.users : [];
                        const activeUsers = users.length ? users.join(', ') : 'Tidak ada';
                        const activeUsersNode = document.getElementById('preview-field-active_users');
                        if (activeUsersNode) {
                            activeUsersNode.textContent = activeUsers;
                        }

                        if (users.length) {
                            console.info('OnlyOffice document active users:', activeUsers);
                        }
                    },
                    onRequestSave: function () {
                        window.dispatchEvent(new CustomEvent('onlyoffice:save-requested', { detail: { editor: onlyOfficeEditor } }));
                    },
                    onDocumentStateChange: function (event) {
                        const state = event?.data?.state || '';
                        if (state) {
                            window.dispatchEvent(new CustomEvent('onlyoffice:document-state', { detail: { state } }));
                        }
                    },
                },
            };

            if (callbackUrl) {
                editorConfig.callbackUrl = callbackUrl;
            }

            const config = {
                width: '100%',
                height: '100%',
                type: 'web-app',
                documentType: 'word',
                document: {
                    fileType: fileType,
                    key: documentKey,
                    title: title,
                    url: fileUrl,
                },
                editorConfig: editorConfig,
                token: jwtToken || undefined,
            };

            onlyOfficeEditor = new DocsAPI.DocEditor(targetId, config);
            window.onlyOfficeEditor = onlyOfficeEditor;
            window.dispatchEvent(new CustomEvent('onlyoffice:ready', { detail: { editor: onlyOfficeEditor } }));
        };
        script.onerror = function () {
            document.getElementById(targetId).innerHTML = '<div class="alert alert-danger m-4">Gagal memuat skrip OnlyOffice. Periksa URL server OnlyOffice pada konfigurasi.</div>';
        };
        document.body.appendChild(script);
    });

    function fillOnlyOfficePlaceholders() {
        const editor = window.onlyOfficeEditor;
        if (!editor || typeof editor.replaceContent !== 'function') {
            console.warn('OnlyOffice editor belum siap untuk replaceContent.');
            return;
        }

        const judul = document.getElementById('input-title')?.value || '';
        const mataPelajaran = {!! json_encode($mataPelajaran ?? '') !!};
        const fase = document.getElementById('fase-selector')?.value || '';
        const kelas = Array.from(document.querySelectorAll('.kelas-checkbox:checked'))
            .map(cb => cb.closest('label')?.querySelector('span')?.textContent?.trim())
            .filter(Boolean)
            .join(', ') || '';
        const status = document.querySelector('select[name="status"]')?.value || '';
        const alokasiWaktu = document.getElementById('input-duration')?.value || '';
        const komponenNilai = Array.from(document.querySelectorAll('input[name="komponen_nilai_ids[]"]:checked'))
            .map(cb => cb.closest('label')?.textContent?.replace(/\s*\(\d+%\)\s*/, '')?.trim())
            .filter(Boolean)
            .join(', ') || '';

        const JUDUL = "@{{JUDUL}}";
        const MATA_PELAJARAN = "@{{MATA_PELAJARAN}}";
        const KELAS = "@{{KELAS}}";
        const FASE = "@{{FASE}}";
        const STATUS = "@{{STATUS}}";
        const ALOKASI_WAKTU = "@{{ALOKASI_WAKTU}}";
        const KOMPONEN_PENILAIAN = "@{{KOMPONEN_PENILAIAN}}";
        const CAPAIAN_PEMBELAJARAN = "@{{CAPAIAN_PEMBELAJARAN}}";
        const TUJUAN_PEMBELAJARAN = "@{{TUJUAN_PEMBELAJARAN}}";
        const METODE_PEMBELAJARAN = "@{{METODE_PEMBELAJARAN}}";
        const MEDIA_PEMBELAJARAN = "@{{MEDIA_PEMBELAJARAN}}";
        const SUMBER_BELAJAR = "@{{SUMBER_BELAJAR}}";
        const PRAKTIK_PEDAGOGIS = "@{{PRAKTIK_PEDAGOGIS}}";
        const LINGKUNGAN_PEMBELAJARAN = "@{{LINGKUNGAN_PEMBELAJARAN}}";
        const PEMANFAATAN_DIGITAL = "@{{PEMANFAATAN_DIGITAL}}";
        const PENGALAMAN_PEMBELAJARAN = "@{{PENGALAMAN_PEMBELAJARAN}}";
        const REFLEKSI_PEMBELAJARAN = "@{{REFLEKSI_PEMBELAJARAN}}";
        const ASESMEN = "@{{ASESMEN}}";

        const data = {
            @json('@{{JUDUL}}'): judul || 'Rencana Pembelajaran',
            @json('@{{MATA_PELAJARAN}}'): mataPelajaran || '',
            @json('@{{KELAS}}'): kelas || '',
            @json('@{{FASE}}'): fase || '',
            @json('@{{STATUS}}'): status ? status.charAt(0).toUpperCase() + status.slice(1) : '',
            @json('@{{ALOKASI_WAKTU}}'): alokasiWaktu || '',
            @json('@{{KOMPONEN_PENILAIAN}}'): komponenNilai || '',
            @json('@{{CAPAIAN_PEMBELAJARAN}}'): '',
            @json('@{{TUJUAN_PEMBELAJARAN}}'): '',
            @json('@{{METODE_PEMBELAJARAN}}'): '',
            @json('@{{MEDIA_PEMBELAJARAN}}'): '',
            @json('@{{SUMBER_BELAJAR}}'): '',
            @json('@{{PRAKTIK_PEDAGOGIS}}'): '',
            @json('@{{LINGKUNGAN_PEMBELAJARAN}}'): '',
            @json('@{{PEMANFAATAN_DIGITAL}}'): '',
            @json('@{{PENGALAMAN_PEMBELAJARAN}}'): '',
            @json('@{{REFLEKSI_PEMBELAJARAN}}'): '',
            @json('@{{ASESMEN}}'): '',
        };

        let html = `<html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'><head><meta charset='utf-8'></head><body>`;
        html += '<p style="text-align:center;font-weight:bold;font-size:14pt;">RENCANA PEMBELAJARAN</p>';
        html += '<p style="text-align:center;font-size:10pt;color:#666;">Isi setiap bagian di bawah ini. Anda dapat menambahkan teks, gambar, tabel, atau elemen lain sesuai kebutuhan.</p>';
        html += '<p></p>';
        html += '<p style="font-weight:bold;font-size:12pt;">INFORMASI UMUM</p>';
        html += '<p style="font-size:10pt;color:#666;">Biarkan placeholder di bawah tetap ada agar sistem dapat mengenali dan mengisi bidangnya secara otomatis.</p>';
        html += '<p></p>';
        html += '<table border="1" cellpadding="8" cellspacing="0" style="border-collapse:collapse;width:100%;">';
        html += '<tr><td style="width:150px;background:#eaf1fb;font-weight:bold;border:1px solid #aeb9c8;">Judul</td><td style="border:1px solid #aeb9c8;">' + escapeHtml(data[@json('@{{JUDUL}}')]) + '</td></tr>';
        html += '<tr><td style="width:150px;background:#eaf1fb;font-weight:bold;border:1px solid #aeb9c8;">Mata Pelajaran</td><td style="border:1px solid #aeb9c8;">' + escapeHtml(data[@json('@{{MATA_PELAJARAN}}')]) + '</td></tr>';
        html += '<tr><td style="width:150px;background:#eaf1fb;font-weight:bold;border:1px solid #aeb9c8;">Kelas / Fase</td><td style="border:1px solid #aeb9c8;">' + escapeHtml(data[@json('@{{KELAS}}')]) + ' / ' + escapeHtml(data[@json('@{{FASE}}')]) + '</td></tr>';
        html += '<tr><td style="width:150px;background:#eaf1fb;font-weight:bold;border:1px solid #aeb9c8;">Status</td><td style="border:1px solid #aeb9c8;">' + escapeHtml(data[@json('@{{STATUS}}')]) + '</td></tr>';
        html += '<tr><td style="width:150px;background:#eaf1fb;font-weight:bold;border:1px solid #aeb9c8;">Alokasi Waktu</td><td style="border:1px solid #aeb9c8;">' + escapeHtml(data[@json('@{{ALOKASI_WAKTU}}')]) + '</td></tr>';
        html += '<tr><td style="width:150px;background:#eaf1fb;font-weight:bold;border:1px solid #aeb9c8;">Komponen Penilaian</td><td style="border:1px solid #aeb9c8;">' + escapeHtml(data[@json('@{{KOMPONEN_PENILAIAN}}')]) + '</td></tr>';
        html += '</table>';
        html += '<p></p>';

        const sections = [
            ['CAPAIAN PEMBELAJARAN', @json('@{{CAPAIAN_PEMBELAJARAN}}')],
            ['TUJUAN PEMBELAJARAN', @json('@{{TUJUAN_PEMBELAJARAN}}')],
            ['METODE PEMBELAJARAN', @json('@{{METODE_PEMBELAJARAN}}')],
            ['MEDIA PEMBELAJARAN', @json('@{{MEDIA_PEMBELAJARAN}}')],
            ['SUMBER BELAJAR', @json('@{{SUMBER_BELAJAR}}')],
            ['PRAKTIK PEDAGOGIS', @json('@{{PRAKTIK_PEDAGOGIS}}')],
            ['LINGKUNGAN PEMBELAJARAN', @json('@{{LINGKUNGAN_PEMBELAJARAN}}')],
            ['PEMANFAATAN DIGITAL', @json('@{{PEMANFAATAN_DIGITAL}}')],
            ['PENGALAMAN PEMBELAJARAN', @json('@{{PENGALAMAN_PEMBELAJARAN}}')],
            ['REFLEKSI PEMBELAJARAN', @json('@{{REFLEKSI_PEMBELAJARAN}}')],
            ['ASESMEN / PENILAIAN', @json('@{{ASESMEN}}')],
        ];

        sections.forEach(([heading, key]) => {
            html += '<p style="font-weight:bold;font-size:12pt;">' + escapeHtml(heading) + '</p>';
            html += '<p>' + escapeHtml(data[key] || '') + '</p>';
            html += '<p></p>';
        });

        html += '</body></html>';

        try {
            editor.replaceContent(html);
            console.info('OnlyOffice placeholder berhasil diisi.');
        } catch (e) {
            console.warn('Gagal mengisi placeholder OnlyOffice:', e);
        }
    }

    function escapeHtml(text) {
        return String(text ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
</script>
@endpush
