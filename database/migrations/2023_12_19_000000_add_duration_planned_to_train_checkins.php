<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void {
        Schema::table('train_checkins', static function(Blueprint $table) {
            $table->unsignedInteger('duration_planned')
                  ->after('duration')
                  ->nullable()
                  ->comment('Duration in minutes. Cached value with planned data. Null if not yet calculated.')
                  ->default(null);

        });
    }

    public function down(): void {
        Schema::table('train_checkins', static function(Blueprint $table) {
            $table->dropColumn('duration_planned');
        });
    }
};
