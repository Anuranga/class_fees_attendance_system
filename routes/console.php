<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('make:superadmin {email} {password?}', function (string $email, ?string $password = null) {
    $password = $password ?: 'password';
    $user = User::firstOrNew(['email' => $email]);
    $user->name = $user->name ?: 'Super Administrator';
    $user->password = Hash::make($password);
    $user->role = 'super_admin';
    $user->status = 'active';
    $user->email_verified_at = now();
    $user->can_collect_fees = true;
    $user->can_manage_uniforms = true;
    $user->save();

    $this->info("Super Admin [{$email}] created/updated successfully with password [{$password}].");
})->purpose('Create or promote a user to Super Administrator');
