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
        Schema::create('notes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('deck_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('note_type_id')
                ->constrained()
                ->restrictOnDelete();

            $table->jsonb('fields');
            // contoh:
            // { "expression": "食べる", "meaning": "makan" }

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
