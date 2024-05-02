<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddChainIdToStatuses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('statuses', function(Blueprint $table) {
            $table->bigInteger('chain_id')->unsigned()->nullable();

            $table->foreign('chain_id')
                  ->references('id')
                  ->on('travel_chains')
                  ->onDelete('set null')
                  ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('statuses', function(Blueprint $table) {
            $table->dropForeign("statuses_chain_id_foreign");
        });
        Schema::table('statuses', function(Blueprint $table) {
            $table->dropColumn('chain_id');
        });
    }
}
