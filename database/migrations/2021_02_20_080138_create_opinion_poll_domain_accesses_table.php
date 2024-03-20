<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOpinionPollDomainAccessesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('opinion_poll_domain_accesses', function (Blueprint $table) {
            $table->integer('domain_id')->nullable()->unsigned();
            $table->integer('poll_id')->nullable()->unsigned();
            $table->integer('domain_group_id')->unsigned()->nullable();
        });

        Schema::table('opinion_poll_domain_accesses', function(Blueprint $table){
            $table->unique(['domain_id', 'poll_id', 'domain_group_id'], 'poll_access_domain_unique_key');
            $table->foreign('domain_id')->references('id')->on('domain_lists')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('poll_id')->references('id')->on('opinion_polls')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('domain_group_id')->references('id')->on('domain_groups')->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('opinion_poll_domain_accesses');
    }
}
