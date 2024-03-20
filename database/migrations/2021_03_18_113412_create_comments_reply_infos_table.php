<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommentsReplyInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comments_reply_infos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('comment_id')->nullable()->unsigned();
            $table->string('subject');
            $table->text('comment');
            $table->string('reply_media', 15)->nullable();
            $table->boolean('status')->default(0);
            $table->integer('created_by')->nullable()->unsigned();
            $table->integer('updated_by')->nullable()->unsigned();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('comments_reply_infos', function(Blueprint $table){
            $table->foreign('comment_id')->references('id')->on('comment_lists')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('comments_reply_infos');
    }
}
