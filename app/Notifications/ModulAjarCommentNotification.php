<?php

namespace App\Notifications;

use App\Models\ModulAjarComment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ModulAjarCommentNotification extends Notification
{
    use Queueable;

    public function __construct(public ModulAjarComment $comment)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'modul_ajar_comment',
            'comment_id' => $this->comment->id,
            'module_id' => $this->comment->modul_ajar_id,
            'message' => 'Pengawas memberikan komentar pada modul ajar Anda.',
            'comment' => $this->comment->komentar,
            'url' => route('rencana_pembelajaran.index'),
        ];
    }
}
