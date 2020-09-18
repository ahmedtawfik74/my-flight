<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FlightResouce extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'                    => $this->id,
            'origin_city'           => $this->origin_city,
            'destination_city'      => $this->destination_city,
            'price'                 => $this->price,
            'takeoff_time'          => $this->takeoff_time,
            'landing_time'          => $this->landing_time
        ];
    }
}