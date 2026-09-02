<?php

namespace Tests\Feature\Api;

use App\Models\Department;
use App\Models\ServiceCatalog;
use App\Models\ServiceCommitment;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceCommitmentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_manage_ola_and_underpinning_contract(): void
    {
        $admin = User::factory()->create([
            'email' => 'service-commitment-admin@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'super_admin',
        ]);
        $department = Department::query()->create([
            'code' => 'DEP-COMMIT',
            'name' => 'Commitment Department',
            'is_active' => true,
        ]);
        $vendor = Vendor::query()->create([
            'code' => 'VND-COMMIT',
            'name' => 'Commitment Vendor',
            'is_active' => true,
        ]);
        $service = ServiceCatalog::query()->create([
            'code' => 'SRV-COMMIT',
            'name' => 'Commitment Service',
            'ownership_model' => ServiceCatalog::OWNERSHIP_HYBRID,
            'department_owner_id' => $department->id,
            'vendor_id' => $vendor->id,
            'is_active' => true,
        ]);

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => $admin->email,
            'password' => 'secret123',
        ])->json('token');

        $olaResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/service-commitments', [
                'name' => 'Internal fulfillment OLA',
                'commitment_type' => ServiceCommitment::TYPE_OLA,
                'service_id' => $service->id,
                'provider_department_id' => $department->id,
                'response_target_minutes' => 30,
                'resolution_target_minutes' => 240,
                'availability_target_percent' => 99.50,
                'escalation_contact' => 'ops-lead@example.com',
                'review_frequency' => 'Monthly',
                'status' => ServiceCommitment::STATUS_ACTIVE,
            ])
            ->assertCreated()
            ->assertJsonPath('data.commitment_type', ServiceCommitment::TYPE_OLA)
            ->assertJsonPath('data.service_id', $service->id)
            ->assertJsonPath('data.provider_department_id', $department->id)
            ->assertJsonPath('data.vendor_id', null);

        $ucResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/service-commitments', [
                'name' => 'Vendor support UC',
                'commitment_type' => ServiceCommitment::TYPE_UNDERPINNING_CONTRACT,
                'service_id' => $service->id,
                'vendor_id' => $vendor->id,
                'response_target_minutes' => 60,
                'resolution_target_minutes' => 480,
                'escalation_contact' => 'vendor-support@example.com',
                'status' => ServiceCommitment::STATUS_ACTIVE,
            ])
            ->assertCreated()
            ->assertJsonPath('data.commitment_type', ServiceCommitment::TYPE_UNDERPINNING_CONTRACT)
            ->assertJsonPath('data.vendor_id', $vendor->id)
            ->assertJsonPath('data.provider_department_id', null);

        $this->assertDatabaseHas('service_commitments', [
            'id' => (int) $olaResponse->json('data.id'),
            'commitment_type' => ServiceCommitment::TYPE_OLA,
            'provider_department_id' => $department->id,
            'vendor_id' => null,
        ]);
        $this->assertDatabaseHas('service_commitments', [
            'id' => (int) $ucResponse->json('data.id'),
            'commitment_type' => ServiceCommitment::TYPE_UNDERPINNING_CONTRACT,
            'vendor_id' => $vendor->id,
            'provider_department_id' => null,
        ]);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/service-commitments', [
                'name' => 'Invalid UC',
                'commitment_type' => ServiceCommitment::TYPE_UNDERPINNING_CONTRACT,
                'status' => ServiceCommitment::STATUS_ACTIVE,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('vendor_id');
    }
}
