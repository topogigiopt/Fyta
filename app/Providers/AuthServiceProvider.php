<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
      'App\Product' => 'App\Policies\ProductPolicy',
      'App\Order' => 'App\Policies\OrderPolicy',
      'App\User' => 'App\Policies\UserPolicy',
      'App\Discount' => 'App\Policies\SalesPolicy'
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
    }
}
