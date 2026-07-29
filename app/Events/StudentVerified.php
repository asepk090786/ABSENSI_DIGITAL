<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StudentVerified implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $kelas_id;
    public $tanggal;
    public $siswa_id;
    public $status;
    public $siswa_name;

    /**
     * Create a new event instance.
     */
    public function __construct($kelas_id, $tanggal, $siswa_id, $status = 'hadir', $siswa_name = null)
    {
        $this->kelas_id = $kelas_id;
        // normalize tanggal to Y-m-d
        $this->tanggal = is_string($tanggal) ? date('Y-m-d', strtotime($tanggal)) : (is_object($tanggal) ? $tanggal->format('Y-m-d') : date('Y-m-d'));
        $this->siswa_id = $siswa_id;
        $this->status = $status;
        $this->siswa_name = $siswa_name;
    }

    /**
     * Get the channels the event should broadcast on.
     * public channel for simplicity: 'absensi-kelas.{kelas}.{tanggal}'
     */
    public function broadcastOn()
    {
        return new Channel('absensi-kelas.' . $this->kelas_id . '.' . $this->tanggal);
    }

    public function broadcastAs()
    {
        return 'StudentVerified';
    }

    public function broadcastWith()
    {
        return [
            'siswa_id' => $this->siswa_id,
            'status' => $this->status,
            'siswa_name' => $this->siswa_name,
        ];
    }
}
