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
        DB::table('card_templates')
            ->where('name', 'Standard')
            ->update(['back_template' => '{{Back}}']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('card_templates')
            ->where('name', 'Standard')
            ->update(['back_template' => '{{FrontSide}}<hr id="answer">{{Back}}']);
    }
};
