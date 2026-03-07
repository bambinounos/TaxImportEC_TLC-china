<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalculationShare extends Model
{
    protected $fillable = [
        'calculation_id',
        'shared_with_user_id',
        'shared_by_user_id',
        'permission',
    ];

    public function calculation()
    {
        return $this->belongsTo(Calculation::class);
    }

    public function sharedWithUser()
    {
        return $this->belongsTo(User::class, 'shared_with_user_id');
    }

    public function sharedByUser()
    {
        return $this->belongsTo(User::class, 'shared_by_user_id');
    }
}
