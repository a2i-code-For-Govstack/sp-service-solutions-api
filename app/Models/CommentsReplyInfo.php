<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommentsReplyInfo extends Model
{
    public function CreatedByInfo(){
        // return $this->hasOneThrough('App\Models\UserInfos','App\User','created_by','user_id','id','id');
        //return $this->belongsTo('App\Models\UserInfos','created_by','user_id');
        return $this->belongsTo('App\User','created_by')->with('UserInfo');
    }
}
