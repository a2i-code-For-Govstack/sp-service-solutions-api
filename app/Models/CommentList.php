<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommentList extends Model
{
    public function CommentRelInfo(){
        return $this->belongsTo('App\Models\CommentRelInfo','id','comment_id')->with('FlagRptInfo');
    }    

    public function CommentReplyInfo(){
        return $this->hasMany('App\Models\CommentsReplyInfo','comment_id')->with('CreatedByInfo');
    }

    public function CommentReviewInfo(){
        return $this->hasOne('App\Models\ReviewRating', 'comment_id','id')->with('CreatedByInfo');
    }
}
