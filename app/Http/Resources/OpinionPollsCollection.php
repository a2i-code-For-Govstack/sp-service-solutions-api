<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\PollOptionsCollection;
use App\Http\Resources\DomainListCollection;
use App\Http\Resources\DomainGroupsCollection;

class OpinionPollsCollection extends JsonResource
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
            'cat_id'            => $this->cat_id,
            'cat_info'          => $this->CatInfo,
            'poll_title'        => $this->poll_title,
            'description'       => $this->description, 
            'domain_id'         => $this->domain_id,
            'domain_info'       => DomainListCollection::collection($this->DomainInfo),
            'domain_group_info' => DomainGroupsCollection::collection($this->DomainGroupInfo),
            'type'              => $this->type,
            'status'            => $this->status,
            'start_time'        => $this->start_time?str_replace('+00:00', '.000Z', gmdate('c', strtotime($this->start_time))):$this->start_time,
            'end_time'          => $this->end_time?str_replace('+00:00', '.000Z', gmdate('c', strtotime($this->end_time))):$this->end_time,
            'poll_options'      => PollOptionsCollection::collection($this->PollOptions),
            'total_votes'       => isset($this->TotalVotes[0])?(int)$this->TotalVotes[0]->amount:0,
            'cover_photo'       => $this->cover_photo,
            'created_by'        => $this->created_by,
            'updated_by'        => $this->updated_by,
            'created_at'        => date('jS, F Y',strtotime($this->created_at)),
            'updated_at'        => date('jS, F Y',strtotime($this->updated_at))
        ];
    }
}
