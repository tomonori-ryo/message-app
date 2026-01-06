<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'sender_id',
        'notification_type_id',
        'title',
        'body',
        'real_message',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    /**
     * 通知を受信したユーザー
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 通知を送信したユーザー
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * 通知タイプ
     */
    public function notificationType()
    {
        return $this->belongsTo(NotificationType::class);
    }
}
