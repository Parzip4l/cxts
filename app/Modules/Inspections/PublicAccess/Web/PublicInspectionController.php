<?php

namespace App\Modules\Inspections\PublicAccess\Web;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetLocation;
use App\Models\InspectionTemplate;
use App\Models\User;
use App\Modules\Inspections\Inspections\InspectionService;
use App\Modules\Inspections\PublicAccess\Requests\StorePublicInspectionRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublicInspectionController extends Controller
{
    public function __construct(private readonly InspectionService $inspectionService)
    {
    }

    public function create(): View
    {
        $templates = InspectionTemplate::query()
            ->where('is_active', true)
            ->with([
                'items' => fn ($query) => $query->where('is_active', true)->orderBy('sequence'),
            ])
            ->orderBy('name')
            ->get(['id', 'name', 'description']);

        $templatePayload = $templates->map(function (InspectionTemplate $template): array {
            return [
                'id' => $template->id,
                'name' => $template->name,
                'items' => $template->items->map(fn ($item): array => [
                    'id' => $item->id,
                    'sequence' => $item->sequence,
                    'label' => $item->item_label,
                    'expected' => $item->expected_value,
                    'required' => (bool) $item->is_required,
                ])->values()->all(),
            ];
        })->values()->all();

        return view('public.inspections.create', [
            'templates' => $templates,
            'templatePayload' => $templatePayload,
            'assetOptions' => Asset::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'locationOptions' => AssetLocation::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StorePublicInspectionRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $publicOfficer = $this->findOrCreatePublicOfficer();

        $inspection = $this->inspectionService->createAndSubmitPublic(
            data: $data,
            officer: $publicOfficer,
            supportingFiles: $request->file('supporting_files', []),
        );

        return redirect()
            ->route('public.inspections.create')
            ->with('success', "Inspeksi berhasil dikirim dengan nomor {$inspection->inspection_number}.");
    }

    private function findOrCreatePublicOfficer(): User
    {
        $email = 'public-inspection@system.local';

        $user = User::query()->where('email', $email)->first();
        if ($user !== null) {
            return $user;
        }

        return User::query()->create([
            'name' => 'Public Inspection Bot',
            'email' => $email,
            'password' => Hash::make(Str::random(24)),
            'role' => 'inspection_officer',
            'department_id' => null,
            'email_verified_at' => now(),
        ]);
    }
}
