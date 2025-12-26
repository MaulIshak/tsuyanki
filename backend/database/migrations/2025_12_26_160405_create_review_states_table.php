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
        Schema::create('review_states', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('card_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->float('ease_factor')->default(2.5);
            $table->integer('interval')->default(0); // hari
            $table->integer('repetition')->default(0);

            $table->timestamp('due_at')->index();
            $table->timestamp('last_reviewed_at')->nullable();

            $table->timestamps();

            $table->unique(['user_id', 'card_id']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_states');
    }
};
