<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePollResultExplainsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('poll_result_explains', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('poll_id')->nullable()->unsigned();
            $table->integer('poll_option_id')->nullable()->unsigned();
            $table->text('comments')->nullable();
            $table->timestamps();
        });

        Schema::table('poll_result_explains', function(Blueprint $table){
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
        Schema::dropIfExists('poll_result_explains');
    }
}
