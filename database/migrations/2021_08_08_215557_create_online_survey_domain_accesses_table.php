<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOnlineSurveyDomainAccessesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('online_survey_domain_accesses', function (Blueprint $table) {
            $table->integer('domain_id')->nullable()->unsigned();
            $table->integer('survey_id')->nullable()->unsigned();
            $table->integer('domain_group_id')->nullable()->unsigned();
        });

        Schema::table('online_survey_domain_accesses', function(Blueprint $table){
            $table->unique(['domain_id', 'survey_id', 'domain_group_id'], 'survey_access_domain_unique_key');
            $table->foreign('domain_id')->references('id')->on('domain_lists')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('survey_id')->references('id')->on('online_surveys')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('online_survey_domain_accesses');
    }
}
