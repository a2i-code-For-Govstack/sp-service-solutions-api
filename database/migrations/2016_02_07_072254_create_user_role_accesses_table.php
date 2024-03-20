<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserRoleAccessesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_role_accesses', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('role_id')->unsigned()->nullable();
            $table->tinyInteger('feature_id');
            $table->boolean('create');
            $table->boolean('edit');
            $table->boolean('delete');
            $table->boolean('view_others');
            $table->boolean('edit_others');
            $table->boolean('delete_others');
        });

        Schema::table('user_role_accesses', function(Blueprint $table){
            $table->foreign('role_id')->references('id')->on('user_role_infos')->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_role_accesses');
    }
}
