<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomNotificationType extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'app_name',
        'icon',
        'icon_image',
        'color',
        'theme_type',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * 作成者
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
