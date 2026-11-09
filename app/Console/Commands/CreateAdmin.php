<?php

namespace App\Console\Commands;

use App\Enums\AdminRole;
use App\Models\User;
use App\Services\AuditLogger;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

#[Signature('admin:create {email : Email address of the administrator} {--role=super-admin : Role to assign} {--name= : Full name for a new account}')]
#[Description('Create an administrator, or grant an admin role to an existing user')]
class CreateAdmin extends Command
{
    public function handle(AuditLogger $auditLogger): int
    {
        $role = AdminRole::tryFrom($this->option('role'));

        if ($role === null) {
            $this->error('Unknown role. Valid roles: '.implode(', ', AdminRole::values()));

            return self::FAILURE;
        }

        $this->callSilently('db:seed', ['--class' => RolesAndPermissionsSeeder::class, '--force' => true]);
        $email = strtolower($this->argument('email'));
        $user = User::where('email', $email)->first();

        if ($user === null) {
            $name = $this->option('name') ?: text('Full name', required: true);
            $password = password('Password (at least 12 characters)', required: true);
            $validator = Validator::make(['email' => $email, 'password' => $password], [
                'email' => ['required', 'email'],
                'password' => [Password::min(12)],
            ]);

            if ($validator->fails()) {
                $this->error($validator->errors()->first());

                return self::FAILURE;
            }

            $user = new User;
            $user->forceFill(['name' => $name, 'email' => $email, 'password' => $password, 'email_verified_at' => now()])->save();
        }

        $user->syncRoles([$role->value]);
        $auditLogger->log('admin.created_from_console', $user, newValues: ['email' => $email, 'role' => $role->value]);

        $this->info("{$user->email} is now a {$role->label()}. Sign in at ".route('admin.login').' to set up two-factor authentication.');

        return self::SUCCESS;
    }
}
