<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'otp_request_count')) {
                $table->unsignedTinyInteger('otp_request_count')->default(0)->after('otp_attempts');
            }
            if (!Schema::hasColumn('users', 'otp_request_locked_at')) {
                $table->timestamp('otp_request_locked_at')->nullable()->after('otp_locked_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['otp_request_count', 'otp_request_locked_at']);
        });
    }
};
