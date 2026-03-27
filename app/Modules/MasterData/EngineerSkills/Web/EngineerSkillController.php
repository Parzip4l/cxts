<?php

namespace App\Modules\MasterData\EngineerSkills\Web;

use App\Http\Controllers\Controller;
use App\Models\EngineerSkill;
use App\Modules\MasterData\EngineerSkills\EngineerSkillService;
use App\Modules\MasterData\EngineerSkills\Requests\StoreEngineerSkillRequest;
use App\Modules\MasterData\EngineerSkills\Requests\UpdateEngineerSkillRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EngineerSkillController extends Controller
{
    public function __construct(private readonly EngineerSkillService $engineerSkillService)
    {
        $this->authorizeResource(EngineerSkill::class, 'engineer_skill');
    }

    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->input('search'),
        ];

        if ($request->has('is_active') && $request->input('is_active') !== '') {
            $filters['is_active'] = (bool) $request->input('is_active');
        }

        return view('modules.master-data.engineer-skills.index', [
            'engineerSkills' => $this->engineerSkillService->paginate($filters),
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        return view('modules.master-data.engineer-skills.form', [
            'engineerSkill' => new EngineerSkill(),
            'action' => route('master-data.engineer-skills.store'),
            'method' => 'POST',
            'pageTitle' => 'Create Engineer Skill',
        ]);
    }

    public function store(StoreEngineerSkillRequest $request): RedirectResponse
    {
        $this->engineerSkillService->create($request->validated());

        return redirect()
            ->route('master-data.engineer-skills.index')
            ->with('success', 'Engineer skill has been created.');
    }

    public function edit(EngineerSkill $engineerSkill): View
    {
        return view('modules.master-data.engineer-skills.form', [
            'engineerSkill' => $engineerSkill,
            'action' => route('master-data.engineer-skills.update', $engineerSkill),
            'method' => 'PUT',
            'pageTitle' => 'Edit Engineer Skill',
        ]);
    }

    public function update(UpdateEngineerSkillRequest $request, EngineerSkill $engineerSkill): RedirectResponse
    {
        $this->engineerSkillService->update($engineerSkill, $request->validated());

        return redirect()
            ->route('master-data.engineer-skills.index')
            ->with('success', 'Engineer skill has been updated.');
    }

    public function destroy(EngineerSkill $engineerSkill): RedirectResponse
    {
        $this->engineerSkillService->delete($engineerSkill);

        return redirect()
            ->route('master-data.engineer-skills.index')
            ->with('success', 'Engineer skill has been deleted.');
    }
}
