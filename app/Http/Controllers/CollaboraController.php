<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CollaboraController extends Controller
{
    /**
     * Lokasi file DOCX sementara yang sedang diedit.
     */
    private function getTempFilePath(string $tempKey): string
    {
        // Hanya izinkan karakter aman untuk nama file.
        $tempKey = preg_replace('/[^A-Za-z0-9_\-]/', '', $tempKey);

        return public_path(
            'uploads/rencana_pembelajaran/docx/temp/' . $tempKey . '.docx'
        );
    }

    /**
     * URL WOPI Source yang akan dipanggil Collabora.
     */
    private function getWopiSrc(string $tempKey): string
    {
        return url('/collabora/wopi/files/' . $tempKey);
    }

    private function wopiCorsHeaders(): array
    {
        $origin = request()->header('Origin');
        $headers = [
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, OPTIONS',
            'Access-Control-Allow-Headers' => 'Accept, Accept-Encoding, Authorization, Content-Type, Content-Length, X-Requested-With, X-WOPI-Override, X-WOPI-ItemVersion, X-WOPI-Lock, X-WOPI-Locks, X-WOPI-SessionId',
            'Access-Control-Expose-Headers' => 'X-WOPI-ItemVersion, X-WOPI-SessionId, X-WOPI-Lock, X-WOPI-Locks, X-WOPI-Override',
            'Access-Control-Allow-Credentials' => 'true',
        ];

        if ($origin) {
            $headers['Access-Control-Allow-Origin'] = $origin;
        }

        return $headers;
    }

    private function wopiResponse($response)
    {
        foreach ($this->wopiCorsHeaders() as $key => $value) {
            $response->headers->set($key, $value);
        }

        return $response;
    }

    public function options(Request $request, string $tempKey)
    {
        return response('', 200, $this->wopiCorsHeaders());
    }

    /**
     * WOPI CheckFileInfo
     *
     * Dipanggil oleh Collabora untuk mengetahui:
     * - nama file
     * - ukuran file
     * - user
     * - apakah user boleh edit
     * - apakah file mendukung update
     */
    public function checkFileInfo(Request $request, string $tempKey)
    {
        $tempFilePath = $this->getTempFilePath($tempKey);

        /*
         * Jika file temporary belum ada,
         * gunakan template sebagai sumber dokumen.
         */
        if (!file_exists($tempFilePath)) {
            $templatePath = storage_path(
                'app/templates/template_modul_ajar.docx'
            );

            if (!file_exists($templatePath)) {
                return response()->json([
                    'Error' => 'File not found',
                    'Message' => 'Template dokumen tidak ditemukan.',
                ], 404);
            }

            $filePath = $templatePath;
        } else {
            $filePath = $tempFilePath;
        }

        /*
         * Pastikan file benar-benar bisa dibaca.
         */
        if (!is_readable($filePath)) {
            return response()->json([
                'Error' => 'File not readable',
                'Message' => 'File dokumen tidak dapat dibaca oleh server.',
            ], 500);
        }

        $fileName = basename($filePath);
        $fileSize = filesize($filePath);

        /*
         * Informasi user.
         */
        $userId = auth()->check()
            ? (string) auth()->id()
            : 'guest';

        $userName = auth()->check()
            ? (auth()->user()->name ?? 'User')
            : 'Guest';

        $userEmail = auth()->check()
            ? (auth()->user()->email ?? '')
            : '';

        /*
         * Version file.
         *
         * Digunakan WOPI untuk mengetahui perubahan versi dokumen.
         */
        $version = md5(
            $filePath . '|' .
            filemtime($filePath) . '|' .
            $fileSize
        );

        /*
         * Response WOPI CheckFileInfo.
         */
        $response = [
            /*
             * Informasi dokumen
             */
            'BaseFileName' => $fileName,
            'Size' => $fileSize,
            'Version' => $version,

            /*
             * Informasi user
             */
            'UserId' => $userId,
            'UserFriendlyName' => $userName,
            'UserInfo' => $userEmail,

            /*
             * =====================================================
             * PENTING UNTUK MODE EDIT
             * =====================================================
             */

            // User boleh melakukan perubahan dokumen.
            'UserCanWrite' => true,

            // User tidak dibatasi untuk write relatif.
            'UserCanNotWriteRelative' => false,

            // WOPI host mendukung update file.
            'SupportsUpdate' => true,

            /*
             * =====================================================
             * FITUR FILE
             * =====================================================
             */

            'SupportsRename' => false,
            'SupportsDelete' => false,
            'SupportsCreate' => false,

            /*
             * =====================================================
             * LOCK
             * =====================================================
             */

            'SupportsLocks' => true,
            'SupportsGetLock' => true,
            'SupportsExtendedLockLength' => true,

            /*
             * =====================================================
             * UI / POSTMESSAGE
             * =====================================================
             */

            'PostMessageOrigin' => url('/'),

            'TemplateSaveAs' => false,
            'MoveDisabled' => true,

            /*
             * Perhatikan huruf M:
             *
             * DownloadAsPostMessage
             *
             * bukan:
             * DownloadAsPostmessage
             */
            'DownloadAsPostMessage' => false,

            /*
             * Breadcrumb
             */
            'BreadcrumbDocName' => 'Modul Ajar',
            'BreadcrumbFolderUrl' => url('/modul-ajar'),
        ];

        return $this->wopiResponse(
            response()
                ->json($response)
                ->header('X-WOPI-SessionId', $tempKey)
        );
    }

    /**
     * WOPI GetFile
     *
     * Collabora menggunakan endpoint ini untuk mengambil
     * file DOCX.
     */
    public function getFile(Request $request, string $tempKey)
    {
        $tempFilePath = $this->getTempFilePath($tempKey);

        /*
         * Gunakan file temporary jika sudah ada.
         */
        if (file_exists($tempFilePath)) {
            $filePath = $tempFilePath;
        } else {
            /*
             * Jika belum ada, gunakan template.
             */
            $templatePath = storage_path(
                'app/templates/template_modul_ajar.docx'
            );

            if (!file_exists($templatePath)) {
                return response('File not found', 404);
            }

            $filePath = $templatePath;
        }

        /*
         * Pastikan file bisa dibaca.
         */
        if (!is_readable($filePath)) {
            return response(
                'File cannot be read',
                500
            );
        }

        return $this->wopiResponse(
            response()->file(
                $filePath,
                [
                    'Content-Type' =>
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',

                    'Content-Disposition' =>
                        'inline; filename="' . basename($filePath) . '"',
                ]
            )
        );
    }

    /**
     * WOPI PutFile
     *
     * Collabora memanggil endpoint ini ketika dokumen disimpan.
     */
    public function putFile(Request $request, string $tempKey)
    {
        $tempDir = public_path(
            'uploads/rencana_pembelajaran/docx/temp'
        );

        /*
         * Buat folder jika belum ada.
         */
        if (!is_dir($tempDir)) {
            if (!mkdir($tempDir, 0755, true) && !is_dir($tempDir)) {
                return response()->json([
                    'Error' => 'Cannot create temp directory',
                ], 500);
            }
        }

        $filePath = $this->getTempFilePath($tempKey);

        /*
         * Ambil isi file dari request.
         */
        $content = $request->getContent();

        if ($content === false || strlen($content) === 0) {
            return response()->json([
                'Error' => 'Empty document content',
            ], 400);
        }

        /*
         * Tulis file secara aman.
         *
         * LOCK_EX mencegah dua proses menulis file
         * secara bersamaan.
         */
        $written = file_put_contents(
            $filePath,
            $content,
            LOCK_EX
        );

        if ($written === false) {
            return response()->json([
                'Error' => 'Failed to save document',
                'Message' => 'Server tidak dapat menulis file DOCX.',
            ], 500);
        }

        /*
         * Pastikan file hasil simpan memang ada.
         */
        if (!file_exists($filePath)) {
            return response()->json([
                'Error' => 'File was not created',
            ], 500);
        }

        /*
         * WOPI PutFile sukses.
         */
        return $this->wopiResponse(
            response('OK', 200)
                ->header('X-WOPI-ItemVersion', md5(
                    $filePath . '|' .
                    filemtime($filePath) . '|' .
                    filesize($filePath)
                ))
        );
    }

    /**
     * WOPI Lock
     */
    public function lock(Request $request, string $tempKey)
    {
        return $this->wopiResponse(
            response('Lock acquired', 200)
        );
    }

    /**
     * WOPI Unlock
     */
    public function unlock(Request $request, string $tempKey)
    {
        return $this->wopiResponse(
            response('Lock released', 200)
        );
    }

    /**
     * WOPI RefreshLock
     */
    public function refreshLock(Request $request, string $tempKey)
    {
        return $this->wopiResponse(
            response('Lock refreshed', 200)
        );
    }

    /**
     * Membuat URL WOPI untuk Collabora.
     */
    public function buildCollaboraWopiUrl(
        string $collaboraServerUrl,
        string $tempKey
    ): string {
        $wopiSrc = $this->getWopiSrc($tempKey);

        return $collaboraServerUrl .
            '/loleaflet/dist/loleaflet.html?WOPISrc=' .
            urlencode($wopiSrc) .
            '&lang=id&mode=edit';
    }
}
