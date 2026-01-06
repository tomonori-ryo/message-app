<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Memo extends Model
{
    use HasFactory;

    protected $fillable = ['content', 'user_id', 'target_user_id']; // ← target_user_id を追加

    // メモの対象（相手）を取得するリレーション
    public function targetUser()
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    // メモの作成者（自分）
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}