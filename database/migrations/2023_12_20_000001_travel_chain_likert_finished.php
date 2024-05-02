<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::table('travel_chains', static function(Blueprint $table) {
            $table->unsignedSmallInteger('felt_punctual')->nullable()->after('finished');
            $table->unsignedSmallInteger('felt_stressed')->nullable()->after('felt_punctual');
        });
    }

    public function down(): void {
        Schema::table('travel_chains', static function(Blueprint $table) {
            $table->dropColumn('felt_punctual');
            $table->dropColumn('felt_stressed');
        });
    }
};
