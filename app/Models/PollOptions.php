<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PollOptions extends Model
{
    public function OptionPhotoInfo(){
        return $this->belongsTo('App\Models\MediaGallery','option_photo_id','id');
    }

    public function OptionResult(){
        return $this->belongsTo('App\Models\PollResults','id','poll_option_id');
    }
}
