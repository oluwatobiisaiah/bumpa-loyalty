<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->enum('type', ['purchase', 'spending', 'referral', 'review', 'streak']);
            $table->json('criteria'); // Stores achievement requirements
            $table->integer('points')->default(0);
            $table->string('icon')->nullable();
            $table->enum('tier', ['bronze', 'silver', 'gold', 'platinum'])->default('bronze');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['type', 'is_active']);
            $table->index('tier');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('achievements');
    }
};
