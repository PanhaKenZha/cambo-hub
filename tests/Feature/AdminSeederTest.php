<?php

namespace Tests\Feature;

use App\Enums\AdminRole;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_one_super_admin_with_seeded_credentials(): void
    {
        $this->seed(AdminSeeder::class);

        $this->assertSame(1, User::count());
        $superAdmin = User::where('email', 'superadmin@cambohub.test')->firstOrFail();
        $this->assertSame(AdminRole::SuperAdmin, $superAdmin->adminRole());
        $this->assertNotNull($superAdmin->email_verified_at);
        $this->assertTrue(Hash::check('password', $superAdmin->password));
    }

    public function test_rerunning_keeps_changed_passwords_and_creates_no_duplicates(): void
    {
        $this->seed(AdminSeeder::class);
        $superAdmin = User::where('email', 'superadmin@cambohub.test')->firstOrFail();
        $superAdmin->forceFill(['password' => 'changed-after-first-login'])->save();

        $this->seed(AdminSeeder::class);

        $this->assertSame(1, User::count());
        $this->assertTrue(Hash::check('changed-after-first-login', $superAdmin->refresh()->password));
    }
}
