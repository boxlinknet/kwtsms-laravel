<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kwtsms_logs', function (Blueprint $table) {
            $table->string('recipient_type', 20)->default('customer')->after('event_type');
            $table->unsignedTinyInteger('pages')->default(1)->after('is_test');
        });
    }

    public function down(): void
    {
        Schema::table('kwtsms_logs', function (Blueprint $table) {
            $table->dropColumn(['recipient_type', 'pages']);
        });
    }
};
