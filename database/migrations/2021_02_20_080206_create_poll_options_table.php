<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePollOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('poll_options', function (Blueprint $table) {
            $table->increments('id');
            $table->string('option_title');
            $table->integer('poll_id')->nullable()->unsigned();            
            $table->integer('option_photo_id')->nullable()->unsigned();
            $table->boolean('req_explain')->default('0');
        });

        Schema::table('poll_options', function(Blueprint $table){
            $table->foreign('poll_id')->references('id')->on('opinion_polls')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('option_photo_id')->references('id')->on('media_galleries')->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('poll_options');
    }
}
