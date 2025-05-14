<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class EventResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'eventClass' => $this->eventClass,
            'level' => $this->level,
            'category' => $this->category,
            'subject' => $this->subject,
            'name' => $this->name,
            'poster'=>$this->poster ? Storage::url($this->poster) : null,
            'venue' => $this->venue,
            'county'=>$this->county,
            'description' => $this->description,
            'startDate' => $this->start_date,
            'endDate' => $this->end_date,
            'hosts' => $this->hosts,
            'sponsors' => $this->sponsors,
            'capacity' => $this->capacity,
            'participation_mode'=>$this->participation_mode,
            'link'=>$this->link,
            'registration_fee'=>$this->registration_fee,
            'currency'=>$this->currency,
            'contact_number'=>$this->contact_number,
            'userId' => $this->user_id,
            'isLiked' => $this->is_liked,
            'isPaid' => $this->isPaid,
        ];
    }
}

