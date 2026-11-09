<?php

namespace Database\Seeders;

use App\Enums\AdminRole;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Creates the administrator accounts listed below. Safe to run repeatedly:
 * existing accounts keep their current password.
 */
class AdminSeeder extends Seeder
{
    /**
     * @return list<array{name: string, email: string, role: AdminRole, password: string}>
     */
    private function admins(): array
    {
        return [
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@cambohub.test',
                'role' => AdminRole::SuperAdmin,
                'password' => 'password',
            ],
        ];
    }

    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        foreach ($this->admins() as $adminDetails) {
            $admin = User::firstOrNew(['email' => $adminDetails['email']]);

            if (! $admin->exists) {
                $admin->forceFill([
                    'name' => $adminDetails['name'],
                    'password' => $adminDetails['password'],
                    'email_verified_at' => now(),
                ])->save();
            }

            $admin->syncRoles([$adminDetails['role']->value]);
        }

        $this->command?->info('Seeded '.count($this->admins()).' administrators.');
    }
}
