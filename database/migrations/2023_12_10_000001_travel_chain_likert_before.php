<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::table('travel_chains', static function(Blueprint $table) {
            $table->unsignedSmallInteger('reliability_importance')->nullable();
            $table->unsignedSmallInteger('planned_for_reliability')->nullable();
        });
    }

    public function down(): void {
        Schema::table('travel_chains', static function(Blueprint $table) {
            $table->dropColumn('reliability_importance');
            $table->dropColumn('planned_for_reliability');
        });
    }
};
