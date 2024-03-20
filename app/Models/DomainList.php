<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DomainList extends Model
{
    public function DomainGroupInfo(){
        return $this->belongsTo('App\Models\DomainGroup','domain_group_id','id');
    }
}
