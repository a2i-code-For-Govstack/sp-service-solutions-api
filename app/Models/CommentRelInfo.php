<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommentRelInfo extends Model
{
    public function FlagRptInfo(){
        return $this->belongsTo('App\Models\FlagReportTypes','flag_rpt_type_id','id');
    }
}
