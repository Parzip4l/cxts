<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetLocation;
use App\Models\Inspection;
use App\Models\InspectionTemplate;
use App\Models\User;
use Illuminate\Database\Seeder;

class InspectionSeeder extends Seeder
{
    public function run(): void
    {
        $inspector = User::query()->where('email', 'inspector@demo.com')->first();
        $template = InspectionTemplate::query()->where('code', 'INSP-WIFI-DAILY')->with('items')->first();
        $asset = Asset::query()->where('code', 'AST-AP-001')->first();
        $location = AssetLocation::query()->where('code', 'LOC-JKT-001')->first();

        if ($inspector === null || $template === null) {
            return;
        }

        $inspection = Inspection::query()->updateOrCreate(
            ['inspection_number' => 'INSP-SEED-0001'],
            [
                'inspection_template_id' => $template->id,
                'asset_id' => $asset?->id,
                'asset_location_id' => $location?->id,
                'inspection_officer_id' => $inspector->id,
                'inspection_date' => now()->toDateString(),
                'status' => Inspection::STATUS_IN_PROGRESS,
                'started_at' => now()->subMinutes(20),
                'summary_notes' => 'Initial seeded inspection for demo.',
                'created_by_id' => $inspector->id,
                'updated_by_id' => $inspector->id,
            ]
        );

        if ($inspection->items()->exists()) {
            return;
        }

        foreach ($template->items as $templateItem) {
            $inspection->items()->create([
                'inspection_template_item_id' => $templateItem->id,
                'sequence' => $templateItem->sequence,
                'item_label' => $templateItem->item_label,
                'item_type' => $templateItem->item_type,
                'expected_value' => $templateItem->expected_value,
            ]);
        }
    }
}
