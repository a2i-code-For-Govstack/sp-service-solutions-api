<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDomainListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('domain_lists', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('domain_id')->nullable()->unsigned();
            $table->string('sub_domain')->nullable();
            $table->string('sitename_bn')->nullable();
            $table->string('sitename_en')->nullable();
            $table->integer('domain_group_id')->nullable()->unsigned();
            $table->string('alias')->nullable();
            $table->tinyInteger('cluster')->nullable();
            $table->boolean('status')->default(0);
            $table->integer('created_by')->nullable()->unsigned();
            $table->integer('updated_by')->nullable()->unsigned();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('domain_lists', function(Blueprint $table){
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
        Schema::dropIfExists('domain_lists');
    }
}
