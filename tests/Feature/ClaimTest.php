<?php

namespace Tests\Feature;

use App\Models\Claim;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ClaimTest extends TestCase
{
    use RefreshDatabase;

    private $userRole;
    private $verifierRole;
    private $approverRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRole = Role::create(['name' => 'User']);
        $this->verifierRole = Role::create(['name' => 'Verifier']);
        $this->approverRole = Role::create(['name' => 'Approver']);
    }

    public function test_user_can_create_claim()
    {
        $user = User::factory()->create(['role_id' => $this->userRole->id]);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/claims', [
            'title' => 'Test Claim',
            'description' => 'Test Description',
            'amount' => 1000,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'code' => 200,
                'message' => 'Klaim berhasil dibuat',
            ]);

        $this->assertDatabaseHas('claims', [
            'title' => 'Test Claim',
            'amount' => 1000,
            'user_id' => $user->id,
        ]);
    }

    public function test_user_can_view_own_claims()
    {
        $user = User::factory()->create(['role_id' => $this->userRole->id]);
        Sanctum::actingAs($user);

        Claim::create([
            'user_id' => $user->id,
            'title' => 'Klaim 1',
            'description' => 'Test 1',
            'amount' => 500,
            'status' => 'draft',
        ]);

        $response = $this->getJson('/api/claims/my');

        $response->assertStatus(200)
            ->assertJson([
                'code' => 200,
            ]);

        $this->assertCount(1, $response->json('data'));
    }

    public function test_verifier_can_view_submitted_claims()
    {
        $verifier = User::factory()->create(['role_id' => $this->verifierRole->id]);
        Sanctum::actingAs($verifier);

        $user = User::factory()->create(['role_id' => $this->userRole->id]);

        Claim::create([
            'user_id' => $user->id,
            'title' => 'Submitted Claim',
            'description' => 'Test',
            'amount' => 500,
            'status' => 'submitted',
        ]);

        Claim::create([
            'user_id' => $user->id,
            'title' => 'Draft Claim',
            'description' => 'Test',
            'amount' => 500,
            'status' => 'draft',
        ]);

        $response = $this->getJson('/api/claims/submitted');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('submitted', $response->json('data.0.status'));
    }

    public function test_user_can_submit_claim()
    {
        $user = User::factory()->create(['role_id' => $this->userRole->id]);

        // Cek kode Controller -> userRole = $request->user()->role->name, pastikan dapa role
        $user->load('role');
        Sanctum::actingAs($user);

        $claim = Claim::create([
            'user_id' => $user->id,
            'title' => 'Draft Claim',
            'description' => 'Test',
            'amount' => 500,
            'status' => 'draft',
        ]);

        $response = $this->patchJson('/api/claims/' . $claim->id . '/submit', [
            'status' => 'submitted'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('claims', [
            'id' => $claim->id,
            'status' => 'submitted',
        ]);
    }

    public function test_verifier_can_verify_claim()
    {
        $verifier = User::factory()->create(['role_id' => $this->verifierRole->id]);
        $verifier->load('role');
        Sanctum::actingAs($verifier);

        $user = User::factory()->create(['role_id' => $this->userRole->id]);

        $claim = Claim::create([
            'user_id' => $user->id,
            'title' => 'Submitted Claim',
            'description' => 'Test',
            'amount' => 500,
            'status' => 'submitted',
        ]);

        $response = $this->patchJson('/api/claims/' . $claim->id . '/verify', [
            'status' => 'reviewed'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('claims', [
            'id' => $claim->id,
            'status' => 'reviewed',
        ]);
    }

    public function test_approver_can_approve_claim()
    {
        $approver = User::factory()->create(['role_id' => $this->approverRole->id]);
        $approver->load('role');
        Sanctum::actingAs($approver);

        $user = User::factory()->create(['role_id' => $this->userRole->id]);

        $claim = Claim::create([
            'user_id' => $user->id,
            'title' => 'Reviewed Claim',
            'description' => 'Test',
            'amount' => 500,
            'status' => 'reviewed',
        ]);

        $response = $this->patchJson('/api/claims/' . $claim->id . '/approve', [
            'status' => 'approved'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('claims', [
            'id' => $claim->id,
            'status' => 'approved',
        ]);
    }

    public function test_approver_can_reject_claim()
    {
        $approver = User::factory()->create(['role_id' => $this->approverRole->id]);
        $approver->load('role');
        Sanctum::actingAs($approver);

        $user = User::factory()->create(['role_id' => $this->userRole->id]);

        $claim = Claim::create([
            'user_id' => $user->id,
            'title' => 'Reviewed Claim',
            'description' => 'Test',
            'amount' => 500,
            'status' => 'reviewed',
        ]);

        $response = $this->patchJson('/api/claims/' . $claim->id . '/reject', [
            'status' => 'rejected'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('claims', [
            'id' => $claim->id,
            'status' => 'rejected',
        ]);
    }
}
