<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Student extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'students';
    protected $primaryKey = 'matric_no';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'matric_no',
        'full_name',
        'department_id',
        'level',
        'session_id',
        'photo_path',
        'password',
        'is_active',
        'remember_token',
        'created_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function getAuthIdentifierName(): string
    {
        return 'matric_no';
    }
}
