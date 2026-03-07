<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalculationAuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'calculation_id',
        'user_id',
        'action',
        'description',
        'changes',
    ];

    protected $casts = [
        'changes' => 'array',
        'created_at' => 'datetime',
    ];

    public function calculation()
    {
        return $this->belongsTo(Calculation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
