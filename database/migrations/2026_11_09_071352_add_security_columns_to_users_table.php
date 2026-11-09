<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('two_factor_secret')->nullable()->after('password');
            $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
            $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_recovery_codes');
            $table->timestamp('suspended_at')->nullable()->index()->after('two_factor_confirmed_at');
            $table->string('suspension_reason')->nullable()->after('suspended_at');
            $table->timestamp('last_login_at')->nullable()->after('suspension_reason');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['suspended_at']);
            $table->dropColumn([
                'two_factor_secret',
                'two_factor_recovery_codes',
                'two_factor_confirmed_at',
                'suspended_at',
                'suspension_reason',
                'last_login_at',
                'last_login_ip',
            ]);
        });
    }
};
