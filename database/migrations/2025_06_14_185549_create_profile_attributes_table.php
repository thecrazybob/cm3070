<?php

declare(strict_types=1);

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
        Schema::create('profile_attributes', function (Blueprint $table): void {
            $table->id();
            $table->string('key_name', 50)->unique();
            $table->string('display_name', 100);
            $table->text('description')->nullable();
            $table->enum('data_type', ['string', 'text', 'email', 'url', 'date', 'boolean', 'json'])->default('string');
            $table->boolean('is_system')->default(false);
            $table->json('validation_rules')->nullable();
            $table->timestamps();

            $table->index('key_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_attributes');
    }
};
