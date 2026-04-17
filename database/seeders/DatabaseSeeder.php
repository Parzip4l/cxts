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
            VendorSeeder::class,
            ShiftSeeder::class,
            TicketStatusSeeder::class,
            TicketPrioritySeeder::class,
            TicketCategorySeeder::class,
            SbuIctMasterSeeder::class,
            TicketSubcategorySeeder::class,
            TicketDetailSubcategorySeeder::class,
            InspectionTemplateSeeder::class,
            EngineerSkillMatrixSeeder::class,
            RolePermissionSeeder::class,
            EngineerScheduleSeeder::class,
        ]);
    }
}
