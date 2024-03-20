<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommentRelInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comment_rel_infos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('comment_id')->nullable()->unsigned();
            $table->string('user_name')->nullable();
            $table->string('user_email')->nullable();
            $table->string('contact_no',15)->nullable();
            $table->text('url')->nullable();
            $table->integer('domain_id')->nullable()->unsigned();
            $table->tinyInteger('flag_rpt_type_id')->nullable()->unsigned();
        });

        Schema::table('comment_rel_infos', function(Blueprint $table){            
            $table->foreign('comment_id')->references('id')->on('comment_lists')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('flag_rpt_type_id')->references('id')->on('flag_report_types')->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('comment_rel_infos');
    }
}
