<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSmtpInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('smtp_infos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('mail_host');
            $table->string('mail_port');
            $table->string('mail_username');
            $table->string('mail_password');
            $table->string('mail_encryption');
            $table->integer('domain_group_id')->unsigned()->nullable();
            $table->boolean('status')->default('1');
            $table->integer('created_by')->nullable()->unsigned();
            $table->integer('updated_by')->nullable()->unsigned();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('smtp_infos', function(Blueprint $table){
            $table->foreign('domain_group_id')->references('id')->on('domain_groups')->onDelete('set null')->onUpdate('cascade');
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
        Schema::dropIfExists('smtp_infos');
    }
}
