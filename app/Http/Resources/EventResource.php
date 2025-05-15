<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

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
            'poster' => $this->posterUrl,
            'venue' => $this->venue,
            'county' => $this->county,
            'description' => $this->description,
            'startDate' => $this->startDate, 
            'endDate' => $this->endDate,
            'hosts' => $this->hosts,
            'sponsors' => $this->sponsors,
            'capacity' => $this->capacity,
            'participation_mode' => $this->participation_mode,
            'link' => $this->link,
            'registration_fee' => $this->registration_fee,
            'currency' => $this->currency,
            'contact_number' => $this->contact_number,
            'userId' => $this->user_id,
            'isLiked' => $this->isLiked,
            'isPaid' => $this->isPaid,
        ];
    }
}
