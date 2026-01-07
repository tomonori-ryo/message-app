<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    // 保存してもいい項目を指定
    protected $fillable = ['body', 'user_id', 'receiver_id', 'is_announcement'];

    // ユーザーとの関係（このメッセージを書いた人は...）
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 受信者との関係
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}