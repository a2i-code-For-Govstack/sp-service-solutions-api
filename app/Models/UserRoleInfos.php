<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRoleInfos extends Model
{
    public function RoleAccesses(){
        return $this->hasMany('App\Models\UserRoleAccess','role_id');
    }

    public function DomainGroupInfo(){
        return $this->belongsTo('App\Models\DomainGroup','domain_group_id','id');
    }
}
