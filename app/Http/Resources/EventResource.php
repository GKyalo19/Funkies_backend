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
            'venue' => $this->venue,
            'description' => $this->description,
            'startDate' => $this->start_date,
            'endDate' => $this->end_date,
            'hosts' => $this->hosts,
            'sponsors' => $this->sponsors,
            'capacity' => $this->capacity,
            'userId' => $this->user_id,
            'isLiked' => $this->is_liked, // This uses the accessor from your model
        ];
    }
}

