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
        Schema::create('invites', function (Blueprint $table) {
        $table->id();
        $table->string('code')->unique();
        $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // Who sent the invite
        $table->boolean('used')->default(false); // Has this invite been used?
        $table->foreignId('used_by')->nullable()->constrained('users')->onDelete('set null'); // Who used it
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invites');
    }
};
