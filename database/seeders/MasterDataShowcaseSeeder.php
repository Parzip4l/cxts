<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class MasterDataShowcaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            DepartmentSeeder::class,
            VendorSeeder::class,
            ServiceCatalogSeeder::class,
            ShiftSeeder::class,
            AssetCategorySeeder::class,
            AssetStatusSeeder::class,
            AssetLocationSeeder::class,
            AssetSeeder::class,
            TicketStatusSeeder::class,
            TicketPrioritySeeder::class,
            TicketCategorySeeder::class,
            TicketSubcategorySeeder::class,
            TicketDetailSubcategorySeeder::class,
            SlaPolicySeeder::class,
            UserSeeder::class,
            EngineerScheduleSeeder::class,
            InspectionTemplateSeeder::class,
            EngineerSkillMatrixSeeder::class,
            SlaPolicyAssignmentSeeder::class,
        ]);
    }
}
