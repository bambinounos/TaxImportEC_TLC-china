<?php

namespace App\Providers;

use App\Models\Calculation;
use App\Policies\CalculationPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Calculation::class => CalculationPolicy::class,
    ];

    public function boot(): void
    {
        //
    }
}
