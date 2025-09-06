<?php

namespace App\Http\Requests\Load;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Support\CountryCodes;
use App\Enums\LoadStatus;

class UpdateLoadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'origin_country' => ['sometimes', new CountryCodes()],
            'origin_city' => ['sometimes', 'string', 'max:120'],
            'destination_country' => ['sometimes', new CountryCodes()],
            'destination_city' => ['sometimes', 'string', 'max:120'],

            'pickup_date' => ['sometimes', 'date', 'after_or_equal:today'],
            'delivery_date' => ['sometimes', 'date', 'after_or_equal:pickup_date'],

            'weight_kg' => ['sometimes', 'integer', 'min:1'],
            'price_expectation' => ['nullable', 'integer', 'min:1'],

            'status' => [
                'sometimes',
                Rule::in([
                    LoadStatus::Draft->value,
                    LoadStatus::Open->value,
                ])
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'origin_country' => $this->origin_country ? strtoupper($this->origin_country) : null,
            'destination_country' => $this->destination_country ? strtoupper($this->destination_country) : null,
        ]);
    }
}
