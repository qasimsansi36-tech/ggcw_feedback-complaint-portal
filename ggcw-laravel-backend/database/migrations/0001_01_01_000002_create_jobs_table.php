<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('student');
            $table->string('father_name')->nullable();
            $table->string('roll')->nullable();
            $table->string('department')->nullable();
            $table->boolean('is_approved')->default(0); // Admin Approval Flag
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'father_name', 'roll', 'department', 'is_approved']);
        });
    }
};