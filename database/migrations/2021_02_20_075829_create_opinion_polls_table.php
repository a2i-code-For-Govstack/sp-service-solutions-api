<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOpinionPollsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('opinion_polls', function (Blueprint $table) {
            $table->increments('id');
            $table->string('poll_title')->nullable();
            $table->text('description')->nullable();
            $table->integer('cat_id')->nullable()->unsigned();            
            $table->boolean('type')->default(0);
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->boolean('status')->default(0);
            $table->integer('created_by')->nullable()->unsigned();
            $table->integer('updated_by')->nullable()->unsigned();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('opinion_polls', function(Blueprint $table){
            $table->foreign('cat_id')->references('id')->on('categories')->onDelete('set null')->onUpdate('cascade');            
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('opinion_polls');
    }
}
