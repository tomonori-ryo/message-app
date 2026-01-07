<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'avatar',
        'google_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    // ↓↓ これを追加してください ↓↓
    public function messages()
    {
        return $this->hasMany(Message::class);
    }
    public function friends()
    {
        return $this->belongsToMany(User::class, 'friends', 'user_id', 'friend_id');
    }
    public function memos()
    {
        return $this->hasMany(Memo::class);
    }
    public function notificationTypes()
    {
        return $this->belongsToMany(NotificationType::class, 'user_notification_types')
                    ->withPivot('is_enabled', 'icon_image')
                    ->withTimestamps();
    }
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
    public function sentNotifications()
    {
        return $this->hasMany(Notification::class, 'sender_id');
    }
    public function customNotificationTypes()
    {
        return $this->hasMany(CustomNotificationType::class);
    }
    public function senderNotificationTypes()
    {
        return $this->hasMany(UserSenderNotificationType::class);
    }
    public function blockedUsers()
    {
        return $this->belongsToMany(User::class, 'blocks', 'user_id', 'blocked_user_id');
    }
    public function blockedBy()
    {
        return $this->belongsToMany(User::class, 'blocks', 'blocked_user_id', 'user_id');
    }
    public function friendCategories()
    {
        return $this->hasMany(FriendCategory::class)->orderBy('order');
    }
}
