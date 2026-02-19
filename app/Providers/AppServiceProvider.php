<?php

namespace App\Providers;

use App\Models\PropertyMaster;
use App\Models\PropertyLocationAccess;
use App\Models\PropertyPhoto;
use App\Models\PropertySummary;
use App\Models\PropertyInspectionSignoff;
use App\Models\User;
use App\Models\Role;
use App\Observers\GenericObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        PropertyMaster::observe(GenericObserver::class);
        PropertyLocationAccess::observe(GenericObserver::class);
        PropertyPhoto::observe(GenericObserver::class);
        PropertySummary::observe(GenericObserver::class);
        PropertyInspectionSignoff::observe(GenericObserver::class);
        User::observe(GenericObserver::class);
        Role::observe(GenericObserver::class);
    }
}