<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kwtsms_templates', function (Blueprint $table) {
            // Replace single-column unique on name with a composite unique on (name, locale)
            // so the same template name can exist in multiple locales (e.g. en + ar).
            $table->dropUnique(['name']);
            $table->unique(['name', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::table('kwtsms_templates', function (Blueprint $table) {
            $table->dropUnique(['name', 'locale']);
            $table->unique(['name']);
        });
    }
};
