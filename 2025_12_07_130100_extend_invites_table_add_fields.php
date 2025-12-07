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
        Schema::table('invites', function (Blueprint $table) {
            if (!Schema::hasColumn('invites', 'email')) {
                $table->string('email')->nullable()->after('code');
            }
            if (!Schema::hasColumn('invites', 'token')) {
                $table->string('token')->unique()->nullable()->after('email');
            }
            if (!Schema::hasColumn('invites', 'role')) {
                $table->string('role')->nullable()->after('token');
            }
            if (!Schema::hasColumn('invites', 'company_id')) {
                $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade')->after('role');
            }
            if (!Schema::hasColumn('invites', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('company_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invites', function (Blueprint $table) {
            if (Schema::hasColumn('invites', 'expires_at')) {
                $table->dropColumn('expires_at');
            }
            if (Schema::hasColumn('invites', 'company_id')) {
                $table->dropForeign(['company_id']);
                $table->dropColumn('company_id');
            }
            if (Schema::hasColumn('invites', 'role')) {
                $table->dropColumn('role');
            }
            if (Schema::hasColumn('invites', 'token')) {
                $table->dropColumn('token');
            }
            if (Schema::hasColumn('invites', 'email')) {
                $table->dropColumn('email');
            }
        });
    }
};
