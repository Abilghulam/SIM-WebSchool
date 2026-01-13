<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class SystemAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public array $payload) {}

    public function via($notifiable): array
    {
        // ✅ sekarang database + broadcast
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'key' => $this->payload['key'] ?? null,
            'title' => $this->payload['title'] ?? 'Notifikasi',
            'message' => $this->payload['message'] ?? '',
            'level' => $this->payload['level'] ?? 'info',
            'group' => $this->payload['group'] ?? null,
            'action_url' => $this->payload['action_url'] ?? null,
            'action_text' => $this->payload['action_text'] ?? null,
            'meta' => $this->payload['meta'] ?? [],
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        // payload broadcast harus mirip toDatabase biar frontend gampang
        return new BroadcastMessage($this->toDatabase($notifiable));
    }
}
