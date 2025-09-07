<?php
namespace App\Http\Requests\Load;

use App\Enums\LoadStatus;

class UpdateLoadRequest extends BaseLoadRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function allowedStatuses(): array
    {
        return [
            LoadStatus::Draft->value,
            LoadStatus::Open->value,
        ];
    }
}
