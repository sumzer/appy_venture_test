<?php
namespace App\Http\Requests\Load;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\CountryCode;
use Illuminate\Validation\Rule;
use App\Enums\LoadStatus;

class StoreLoadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isShipper() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'origin_country' => strtoupper((string) $this->origin_country),
            'destination_country' => strtoupper((string) $this->destination_country),
        ]);
    }
    public function rules(): array
    {
        return [
            'origin_country' => ['required', new CountryCode()],
            'origin_city' => ['required', 'string', 'max:120'],
            'destination_country' => ['required', new CountryCode()],
            'destination_city' => ['required', 'string', 'max:120'],

            'pickup_date' => ['required', 'date', 'after_or_equal:today'],
            'delivery_date' => ['required', 'date', 'after_or_equal:pickup_date'],

            'weight_kg' => ['required', 'integer', 'min:1'],
            'price_expectation' => ['nullable', 'integer', 'min:1'],

            'status' => [
                'required',
                Rule::in([
                    LoadStatus::Draft->value,
                    LoadStatus::Open->value,
                ])
            ],
        ];
    }
}
