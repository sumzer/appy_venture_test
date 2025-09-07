<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'origin_country' => $this->origin_country,
            'origin_city' => $this->origin_city,
            'destination_country' => $this->destination_country,
            'destination_city' => $this->destination_city,
            'pickup_date' => $this->pickup_date->toDateString(),
            'delivery_date' => $this->delivery_date->toDateString(),
            'weight_kg' => $this->weight_kg,
            'price_expectation' => $this->price_expectation,
            'status' => $this->status,

            'shipper' => new UserResource($this->whenLoaded('shipper')),
            'booking' => new BookingResource($this->whenLoaded('booking')),
            'accepted_bid' => new BidResource($this->whenLoaded('acceptedBid')),
        ];
    }
}
