<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AmendUniqueKeyToTrainCheckins extends Migration
{

    public function up(): void {
        Schema::table('train_checkins', function(Blueprint $table) {
            $table->dropUnique('user_trip_origin_departure');
            $table->unique(['user_id', 'trip_id', 'origin', 'departure', 'destination', 'arrival'], 'user_trip_origin_departure_destination_arrival');
        });
    }

    public function down(): void {
        Schema::table('train_checkins', function(Blueprint $table) {
            $table->dropUnique('user_trip_origin_departure_destination_arrival');
            $table->unique(['user_id', 'trip_id', 'origin', 'departure'], 'user_trip_origin_departure');
        });
    }
}
