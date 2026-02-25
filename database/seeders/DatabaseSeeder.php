<?php

namespace Database\Seeders;

use App\Models\Claim;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        // 1. Buat Roles
        $roleUser = Role::create(['name' => 'User']);
        $roleVerifier = Role::create(['name' => 'Verifier']);
        $roleApprover = Role::create(['name' => 'Approver']);

        // 2. Buat Users Dummy
        $user = User::create([
            'name' => 'John (User)',
            'email' => 'user@aqi.com',
            'password' => Hash::make('password123'),
            'role_id' => $roleUser->id,
        ]);

        User::create([
            'name' => 'Jane (Verifier)',
            'email' => 'verifier@aqi.com',
            'password' => Hash::make('password123'),
            'role_id' => $roleVerifier->id,
        ]);

        User::create([
            'name' => 'Boss (Approver)',
            'email' => 'approver@aqi.com',
            'password' => Hash::make('password123'),
            'role_id' => $roleApprover->id,
        ]);

        // 3. Buat beberapa Claim Dummy untuk User
        Claim::create([
            'user_id' => $user->id,
            'title' => 'Klaim Perawatan Gigi',
            'description' => 'Tambal gigi di klinik terdekat',
            'amount' => 500000,
            'status' => 'draft'
        ]);

        Claim::create([
            'user_id' => $user->id,
            'title' => 'Klaim Rawat Inap',
            'description' => 'Demam berdarah 3 hari',
            'amount' => 2500000,
            'status' => 'submitted' // Contoh yang sudah di-submit agar bisa dilihat Verifier
        ]);
    }
}
