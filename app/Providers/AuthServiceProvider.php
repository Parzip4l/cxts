<?php

namespace App\Providers;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetLocation;
use App\Models\AssetStatus;
use App\Models\Department;
use App\Models\EngineerSchedule;
use App\Models\EngineerSkill;
use App\Models\InspectionTemplate;
use App\Models\Permission;
use App\Models\Role;
use App\Models\ServiceCatalog;
use App\Models\Shift;
use App\Models\SlaPolicy;
use App\Models\SlaPolicyAssignment;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketDetailSubcategory;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use App\Models\TicketSubcategory;
use App\Models\User;
use App\Models\Vendor;
use App\Policies\AccessControlPolicy;
use App\Policies\AssetPolicy;
use App\Policies\InspectionTemplatePolicy;
use App\Policies\OrganizationPolicy;
use App\Policies\SlaManagementPolicy;
use App\Policies\TicketPolicy;
use App\Policies\TaxonomyPolicy;
use App\Policies\WorkforcePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Ticket::class => TicketPolicy::class,
        User::class => OrganizationPolicy::class,
        Department::class => OrganizationPolicy::class,
        Vendor::class => OrganizationPolicy::class,
        ServiceCatalog::class => OrganizationPolicy::class,
        EngineerSkill::class => WorkforcePolicy::class,
        Shift::class => WorkforcePolicy::class,
        EngineerSchedule::class => WorkforcePolicy::class,
        AssetCategory::class => AssetPolicy::class,
        AssetStatus::class => AssetPolicy::class,
        AssetLocation::class => AssetPolicy::class,
        Asset::class => AssetPolicy::class,
        TicketCategory::class => TaxonomyPolicy::class,
        TicketSubcategory::class => TaxonomyPolicy::class,
        TicketDetailSubcategory::class => TaxonomyPolicy::class,
        TicketPriority::class => TaxonomyPolicy::class,
        TicketStatus::class => TaxonomyPolicy::class,
        SlaPolicy::class => SlaManagementPolicy::class,
        SlaPolicyAssignment::class => SlaManagementPolicy::class,
        InspectionTemplate::class => InspectionTemplatePolicy::class,
        Permission::class => AccessControlPolicy::class,
        Role::class => AccessControlPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
