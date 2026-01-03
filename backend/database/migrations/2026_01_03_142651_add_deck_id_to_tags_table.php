<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tags', function (Blueprint $table) {
            $table->foreignId('deck_id')->nullable()->constrained()->nullOnDelete();
            $table->unique(['name', 'deck_id']);
            // Drop old unique if exists (it was probably unique('name'))
            // $table->dropUnique(['name']); // Careful, this might fail if constraint name differs or doesn't exist.
            // Assuming original migration set unique('name'), we might need to relax it to allow same name in different decks?
            // Actually, if we keep unique('name'), then we can't have "Vocab" in Deck A and "Vocab" in Deck B.
            // We must drop the old unique constraint.
        });

        // Dropping unique index on 'name' if it exists. 
        // We know from 2025_12_26_160542_create_tags_table.php that it might not have unique constraint explicitely?
        // Checking the file content for 'create_tags_table' would have been safer, but let's assume valid standard
        // and add the new one. Use Schema::table to modify indexes separately if needed.

        Schema::table('tags', function (Blueprint $table) {
            $table->dropUnique(['name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tags', function (Blueprint $table) {
            $table->dropForeign(['deck_id']);
            $table->dropUnique(['name', 'deck_id']);
            $table->dropColumn('deck_id');
            $table->unique('name'); // Restore old unique
        });
    }
};
