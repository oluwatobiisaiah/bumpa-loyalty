<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create users table migration
 * File: 2024_01_01_000000_create_users_table.php
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['customer', 'admin'])->default('customer');
            $table->integer('total_points')->default(0);
            $table->decimal('total_cashback', 10, 2)->default(0);
            $table->rememberToken();
            $table->timestamps();

            $table->index(['role', 'total_points']);
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
