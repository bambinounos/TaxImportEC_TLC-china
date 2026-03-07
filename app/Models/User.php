<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isTariffViewer(): bool
    {
        return $this->role === 'tariff_viewer';
    }

    public function canViewTariffs(): bool
    {
        return $this->isAdmin() || $this->isTariffViewer();
    }

    public function calculations()
    {
        return $this->hasMany(Calculation::class)->orderBy('created_at', 'desc');
    }

    public function sharedCalculations()
    {
        return $this->belongsToMany(Calculation::class, 'calculation_shares', 'shared_with_user_id', 'calculation_id')
                     ->withPivot('permission', 'shared_by_user_id')
                     ->withTimestamps();
    }
}
