<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OnlineSurveyDomainAccess extends Model
{
    public function DomainDtlInfo(){
        return $this->belongsTo('App\Models\DomainList','domain_id','id');
    }

    public function DomainGroupDtlInfo(){
        return $this->belongsTo('App\Models\DomainGroup','domain_group_id','id');
    }
}
