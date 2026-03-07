<?php

namespace App\Services;

use App\Models\Calculation;
use App\Models\CalculationAuditLog;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    public static function log(Calculation $calculation, string $action, ?string $description = null, ?array $changes = null): void
    {
        CalculationAuditLog::create([
            'calculation_id' => $calculation->id,
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $description,
            'changes' => $changes,
        ]);
    }
}
