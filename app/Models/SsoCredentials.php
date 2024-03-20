<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SsoCredentials extends Model
{
    public function DomainGroupInfo(){
        return $this->belongsTo('App\Models\DomainGroup','domain_group_id','id');
    }
}
