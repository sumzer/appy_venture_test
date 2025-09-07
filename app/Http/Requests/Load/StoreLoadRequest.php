<?php
namespace App\Http\Requests\Load;

use App\Enums\LoadStatus;

class StoreLoadRequest extends BaseLoadRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isShipper() ?? false;
    }

    protected function allowedStatuses(): array
    {
        return [
            LoadStatus::Draft->value,
            LoadStatus::Open->value,
            LoadStatus::Closed->value,
        ];
    }
}
