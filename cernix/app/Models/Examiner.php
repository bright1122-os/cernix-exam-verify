<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Examiner extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'examiners';
    protected $primaryKey = 'examiner_id';
    public $timestamps = false;

    protected $fillable = [
        'full_name',
        'username',
        'password_hash',
        'role',
        'admin_user_id',
        'is_active',
        'last_active_at',
        'created_at',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_active_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function getAuthPassword(): string
    {
        return (string) $this->password_hash;
    }
}
