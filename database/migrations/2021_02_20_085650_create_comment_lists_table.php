<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommentListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comment_lists', function (Blueprint $table) {
            $table->increments('id');
            $table->text('comment');
            $table->string('file')          ->nullable();
            $table->string('device_info')   ->nullable();
            $table->string('ip_addr')       ->nullable();
            $table->boolean('status')       ->default(0);
            $table->integer('created_by')   ->nullable()->unsigned();
            $table->integer('updated_by')   ->nullable()->unsigned();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('comment_lists', function(Blueprint $table){
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
        Schema::dropIfExists('comment_lists');
    }
}
