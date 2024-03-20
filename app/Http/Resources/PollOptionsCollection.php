<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\MediaGalleriesCollection;

class PollOptionsCollection extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // return parent::toArray($request);
        return [
            'id'                => $this->id,
            'option_title'      => $this->option_title,
            'option_photo_info' => new MediaGalleriesCollection($this->OptionPhotoInfo),
            'option_result'     => $this->OptionResult,
            'poll_id'           => $this->poll_id,
            'req_explain'       => $this->req_explain
        ];
    }
}
