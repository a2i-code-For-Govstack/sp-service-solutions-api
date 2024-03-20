<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OpinionPoll extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    public function PollOptions(){
        return $this->hasMany('App\Models\PollOptions','poll_id')->with(['OptionPhotoInfo','OptionResult']);
    }

    public function TotalVotes(){
        return $this->hasMany('App\Models\PollResults','poll_id')->selectRaw('poll_results.poll_id,SUM(poll_results.votes) as amount')->groupBy('poll_results.poll_id');
    }

    public function CatInfo(){
        return $this->belongsTo('App\Models\Categories','cat_id','id');
    }

    public function DomainInfo(){
        return $this->hasMany('App\Models\OpinionPollDomainAccess','poll_id')->with('DomainDtlInfo');
    }

    public function DomainGroupInfo(){
        return $this->hasMany('App\Models\OpinionPollDomainAccess','poll_id')->with('DomainGroupDtlInfo');
    }
}
