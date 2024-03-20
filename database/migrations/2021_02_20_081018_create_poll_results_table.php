<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePollResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('poll_results', function (Blueprint $table) {            
            $table->integer('poll_id')->nullable()->unsigned();
            $table->integer('poll_option_id')->nullable()->unsigned();
            $table->integer('votes')->default('0');            
        });

        Schema::table('poll_results', function(Blueprint $table){
            $table->unique(['poll_id','poll_option_id']);
            $table->foreign('poll_id')->references('id')->on('opinion_polls')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('poll_option_id')->references('id')->on('poll_options')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('poll_results');
    }
}
