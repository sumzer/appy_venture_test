<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BidResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'load_id' => $this->load_id,
            'carrier_id' => $this->carrier_id,
            'amount' => $this->amount,
            'message' => $this->message,
            'status' => $this->status,
            'created_at' => $this->created_at?->toDateTimeString(),
            'carrier' => new UserResource($this->whenLoaded('carrier')),
        ];
    }
}
