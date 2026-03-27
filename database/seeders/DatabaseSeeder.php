<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            DepartmentSeeder::class,
            VendorSeeder::class,
            ServiceCatalogSeeder::class,
            ShiftSeeder::class,
            AssetCategorySeeder::class,
            AssetStatusSeeder::class,
            AssetLocationSeeder::class,
            AssetSeeder::class,
            UserSeeder::class,
            EngineerScheduleSeeder::class,
            TicketStatusSeeder::class,
            TicketPrioritySeeder::class,
            TicketCategorySeeder::class,
            TicketSubcategorySeeder::class,
            TicketDetailSubcategorySeeder::class,
            SlaPolicySeeder::class,
            SlaPolicyAssignmentSeeder::class,
            InspectionTemplateSeeder::class,
            EngineerSkillMatrixSeeder::class,
            RolePermissionSeeder::class,
            ExecutiveReportDemoSeeder::class,
            DemoScenarioSeeder::class,
        ]);
    }
}
