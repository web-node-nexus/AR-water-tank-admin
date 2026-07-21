<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_providers', function (Blueprint $table) {
            $table->timestamp('last_login_at')->nullable()->after('is_active');
            $table->string('fcm_token')->nullable()->after('last_login_at');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->timestamp('provider_accepted_at')->nullable()->after('assigned_at');
            $table->timestamp('provider_rejected_at')->nullable()->after('provider_accepted_at');
        });
    }

    public function down(): void
    {
        Schema::table('service_providers', function (Blueprint $table) {
            $table->dropColumn(['last_login_at', 'fcm_token']);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['provider_accepted_at', 'provider_rejected_at']);
        });
    }
};
