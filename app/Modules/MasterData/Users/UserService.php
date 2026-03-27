<?php

namespace App\Modules\MasterData\Users;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserService
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return User::query()
            ->with(['department:id,name', 'roleRef:id,code,name'])
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%");
                });
            })
            ->when($filters['role'] ?? null, fn ($query, $role) => $query->where('role', $role))
            ->when($filters['department_id'] ?? null, fn ($query, $departmentId) => $query->where('department_id', $departmentId))
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): User
    {
        $skillIds = $this->extractSkillIds($data);
        $profilePhoto = $data['profile_photo'] ?? null;
        $user = User::query()->create($this->preparePayload($data));
        $this->syncProfilePhoto($user, $profilePhoto, (bool) ($data['remove_profile_photo'] ?? false));
        $this->syncEngineerSkills($user, $data['role'] ?? null, $skillIds);

        return $user->fresh(['department:id,name', 'roleRef:id,code,name', 'engineerSkills:id,name']);
    }

    public function update(User $user, array $data): User
    {
        $skillIds = $this->extractSkillIds($data);
        $profilePhoto = $data['profile_photo'] ?? null;
        $user->update($this->preparePayload($data));
        $this->syncProfilePhoto($user, $profilePhoto, (bool) ($data['remove_profile_photo'] ?? false));
        $this->syncEngineerSkills($user, $data['role'] ?? $user->role, $skillIds);

        return $user->fresh(['department:id,name', 'roleRef:id,code,name', 'engineerSkills:id,name']);
    }

    public function updateProfile(User $user, array $data): User
    {
        $profilePhoto = $data['profile_photo'] ?? null;
        $user->update($this->preparePayload($data));
        $this->syncProfilePhoto($user, $profilePhoto, (bool) ($data['remove_profile_photo'] ?? false));

        return $user->fresh(['department:id,name', 'roleRef:id,code,name']);
    }

    public function delete(User $user): void
    {
        $this->deleteProfilePhoto($user->profile_photo_path);
        $user->delete();
    }

    private function preparePayload(array $data): array
    {
        if (($data['password'] ?? null) !== null && $data['password'] !== '') {
            $data['password'] = Hash::make((string) $data['password']);
        } else {
            unset($data['password']);
        }

        unset($data['current_password']);
        unset($data['password_confirmation']);
        unset($data['engineer_skill_ids']);
        unset($data['profile_photo']);
        unset($data['remove_profile_photo']);

        return $data;
    }

    private function extractSkillIds(array $data): array
    {
        return collect($data['engineer_skill_ids'] ?? [])
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->map(fn ($value) => (int) $value)
            ->unique()
            ->values()
            ->all();
    }

    private function syncEngineerSkills(User $user, ?string $role, array $skillIds): void
    {
        if ($role !== 'engineer') {
            $user->engineerSkills()->sync([]);

            return;
        }

        $user->engineerSkills()->sync($skillIds);
    }

    private function syncProfilePhoto(User $user, mixed $profilePhoto, bool $removePhoto): void
    {
        if ($removePhoto) {
            $this->deleteProfilePhoto($user->profile_photo_path);
            $user->forceFill(['profile_photo_path' => null])->save();
        }

        if (! $profilePhoto instanceof UploadedFile) {
            return;
        }

        $this->deleteProfilePhoto($user->profile_photo_path);
        $path = $profilePhoto->store('profile-photos', 'local');
        $user->forceFill(['profile_photo_path' => $path])->save();
    }

    private function deleteProfilePhoto(?string $path): void
    {
        if (! $path) {
            return;
        }

        Storage::disk('local')->delete($path);
    }
}
