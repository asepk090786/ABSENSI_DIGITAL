<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Onlyoffice extends Component
{
    public string $fileUrl;
    public ?string $callbackUrl;
    public string $fileType;
    public string $title;
    public bool $readonly;
    public ?string $token;
    public string $editorMode;
    public string $userId;
    public string $userName;
    public string $componentId;
    public string $containerId;
    public string $documentKey;

    public function __construct(string $fileUrl, string $fileType, ?string $callbackUrl = null, string $title = '', bool $readonly = false, ?string $token = null, string $containerId = '', string $documentKey = '')
    {
        $this->fileUrl = $fileUrl;
        $this->callbackUrl = $callbackUrl;
        $this->fileType = $fileType;
        $this->title = $title ?: 'Dokumen OnlyOffice';
        $this->readonly = $readonly;
        $this->token = $token;
        $this->editorMode = $readonly ? 'view' : 'edit';
        $this->userId = (string) (auth()->id() ?? 'guest');
        $this->userName = optional(auth()->user())->name ?? 'Guru';
        $this->componentId = 'onlyoffice-editor-' . uniqid();
        $this->containerId = $containerId ?: $this->componentId;
        $this->documentKey = $documentKey ?: sha1($fileUrl . $title . $this->componentId);
    }

    public function render(): View
    {
        return view('components.onlyoffice');
    }
}
