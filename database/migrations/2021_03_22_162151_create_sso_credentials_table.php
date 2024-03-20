<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSsoCredentialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sso_credentials', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('domain_group_id')->nullable()->unsigned();
            $table->integer('app_id')->nullable();
            $table->string('secret_key')->nullable();
            $table->boolean('status')->default('1');
            $table->integer('created_by')->nullable()->unsigned();
            $table->integer('updated_by')->nullable()->unsigned();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('sso_credentials', function(Blueprint $table){
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
        Schema::dropIfExists('sso_credentials');
    }
}
