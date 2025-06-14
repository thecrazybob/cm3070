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
        Schema::create('context_profile_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('context_id')->constrained()->onDelete('cascade');
            $table->foreignId('attribute_id')->constrained('profile_attributes')->onDelete('cascade');
            $table->text('value')->nullable();
            $table->enum('visibility', ['private', 'protected', 'public'])->default('private');
            $table->timestamps();

            $table->unique(['context_id', 'attribute_id']);
            $table->index(['user_id', 'context_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('context_profile_values');
    }
};
