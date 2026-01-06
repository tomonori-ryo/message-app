<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'app_name', 'icon', 'color', 'theme_type', 'description', 'is_active'];

    /**
     * この通知タイプを選択しているユーザー
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_notification_types')
                    ->withPivot('is_enabled')
                    ->withTimestamps();
    }
}
