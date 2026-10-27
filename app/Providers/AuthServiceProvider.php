<?php

namespace App\Providers;

use App\Models\Agreement;
use App\Models\Application;
use App\Models\Certificate;
use App\Models\Logbook;
use App\Models\Program;
use App\Policies\AgreementPolicy;
use App\Policies\ApplicationPolicy;
use App\Policies\CertificatePolicy;
use App\Policies\LogbookPolicy;
use App\Policies\ProgramPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<string, string>
     */
    protected $policies = [
        Agreement::class => AgreementPolicy::class,
        Application::class => ApplicationPolicy::class,
        Certificate::class => CertificatePolicy::class,
        Logbook::class => LogbookPolicy::class,
        Program::class => ProgramPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}