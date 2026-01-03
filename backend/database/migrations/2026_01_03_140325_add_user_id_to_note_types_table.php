<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Cleanup Phase
        $defaults = ['Basic', 'Short Answer', 'Multiple Choice'];

        // A. Deduplicate Defaults
        foreach ($defaults as $defaultName) {
            $types = DB::table('note_types')
                ->where('name', $defaultName)
                ->orderBy('created_at', 'asc')
                ->orderBy('id', 'asc') // Fallback
                ->get();

            if ($types->count() > 1) {
                $master = $types->first();
                $duplicates = $types->slice(1);

                foreach ($duplicates as $duplicate) {
                    // Reassign Notes to Master
                    DB::table('notes')
                        ->where('note_type_id', $duplicate->id)
                        ->update(['note_type_id' => $master->id]);

                    // Delete Duplicate NoteType
                    // Note: This cascades to CardTemplates because of database foreign keys
                    DB::table('note_types')->where('id', $duplicate->id)->delete();
                }
            }
        }

        // B. Destructive Purge of Non-Defaults
        // Find all IDs that are NOT in the surviving defaults
        // Re-query in case we deleted some above
        $idsToKeep = DB::table('note_types')
            ->whereIn('name', $defaults)
            ->pluck('id');

        // Delete Notes associated with custom types first (to clear Cards, Reviews via Cascade)
        // Since `notes.note_type_id` is restrictOnDelete, we MUST delete notes first.
        DB::table('notes')
            ->whereNotIn('note_type_id', $idsToKeep)
            ->delete();

        // Now safe to delete User/Custom NoteTypes
        DB::table('note_types')
            ->whereNotIn('id', $idsToKeep)
            ->delete();

        // 2. Schema Change
        Schema::table('note_types', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->cascadeOnDelete();

            $table->index(['user_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('note_types', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id', 'name']);
            $table->dropColumn('user_id');
        });
    }
};
