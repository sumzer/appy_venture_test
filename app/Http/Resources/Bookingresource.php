<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'load_id' => $this->load_id,
            'bid_id' => $this->bid_id,
            'carrier_id' => $this->carrier_id,
            'booked_at' => $this->booked_at?->toDateTimeString(),
            'bid' => new BidResource($this->whenLoaded('bid')),
            'carrier' => new UserResource($this->whenLoaded('carrier')),
            'shipper' => new UserResource($this->whenLoaded('shipper')),
        ];
    }
}
