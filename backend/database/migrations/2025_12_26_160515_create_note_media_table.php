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
        Schema::create('note_media', function (Blueprint $table) {
            $table->id();

            $table->foreignId('note_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('media_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('field_name'); // audio, image, example_audio

            $table->unique(['note_id', 'media_id', 'field_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('note_media');
    }
};
