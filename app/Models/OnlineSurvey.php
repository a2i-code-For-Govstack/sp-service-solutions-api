<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OnlineSurvey extends Model
{
    use SoftDeletes;

    public function CatInfo(){
        return $this->belongsTo('App\Models\Categories','cat_id','id');
    }

    public function DomainInfo(){
        return $this->hasMany('App\Models\OnlineSurveyDomainAccess','survey_id')->with('DomainDtlInfo');
    }

    public function DomainGroupInfo(){
        return $this->hasMany('App\Models\OnlineSurveyDomainAccess','survey_id')->with('DomainGroupDtlInfo');
    }
}
