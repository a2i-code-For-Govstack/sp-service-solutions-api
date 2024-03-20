<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewRating extends Model
{
    public function CreatedByInfo(){
        return $this->belongsTo('App\User','created_by')->with('UserInfo');
    }
    public function CommentInfo(){
        return $this->belongsTo('App\Models\CommentList','comment_id')->with('CreatedByInfo');
    }
}
