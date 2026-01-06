<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSenderNotificationType extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'sender_id',
        'notification_type_id',
    ];

    /**
     * 受信者（自分）
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 送信者（チャット相手）
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
