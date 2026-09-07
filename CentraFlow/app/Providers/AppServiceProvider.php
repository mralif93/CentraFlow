<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Passport::tokensCan([
            'hrms:read' => 'View staff profiles, leave requests, and claims in HRMS',
            'hrms:write' => 'Manage employees, approve leaves and claims in HRMS',
            'payroll:read' => 'View compensation and payslips in Payroll',
            'payroll:run' => 'Compute and execute payroll processing runs',
            'invoice:read' => 'View clients, rates, and invoices',
            'invoice:manage' => 'Generate and reconcile invoices and payments',
        ]);

        Passport::enablePasswordGrant();
        Passport::authorizationView('auth.oauth.authorize');
    }
}
