<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmtpInfo extends Model
{
    public function DomainGroupInfo(){
        return $this->belongsTo('App\Models\DomainGroup','domain_group_id','id');
    }
}
