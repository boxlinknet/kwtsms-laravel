<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kwtsms_logs', function (Blueprint $table) {
            // Expand recipient from varchar(30) to text to accommodate comma-separated
            // phone lists for bulk sends (e.g. "96598765432,96512345678,...").
            $table->text('recipient')->change();
        });
    }

    public function down(): void
    {
        Schema::table('kwtsms_logs', function (Blueprint $table) {
            $table->string('recipient', 30)->change();
        });
    }
};
