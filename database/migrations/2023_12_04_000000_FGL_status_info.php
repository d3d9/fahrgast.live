<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void {
        Schema::table('statuses', static function(Blueprint $table) {
            $table->boolean('planned')->nullable();
            $table->boolean('taken')->nullable()->after('planned');
            $table->string('not_taken_reason')
                  ->nullable()
                  ->after('taken');
        });
    }

    public function down(): void {
        Schema::table('statuses', static function(Blueprint $table) {
            $table->dropColumn('planned');
            $table->dropColumn('taken');
            $table->dropColumn('not_taken_reason');
        });
    }
};
