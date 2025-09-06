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
        $isOpening = $this->input('status') === LoadStatus::Open->value;
        return [
            'origin_country' => $this->ruleRequiredUnlessDraft($isOpening, [
                Rule::in(CountryCodes::allValidCodes())
            ]),
            'origin_city' => $this->ruleRequiredUnlessDraft($isOpening, [
                'string',
                'max:120'
            ]),
            'destination_country' => $this->ruleRequiredUnlessDraft($isOpening, [
                Rule::in(CountryCodes::allValidCodes())
            ]),
            'destination_city' => $this->ruleRequiredUnlessDraft($isOpening, [
                'string',
                'max:120'
            ]),
            'pickup_date' => $this->ruleRequiredUnlessDraft($isOpening, [
                'date',
                'after_or_equal:today'
            ]),
            'delivery_date' => $this->ruleRequiredUnlessDraft($isOpening, [
                'date',
                'after_or_equal:pickup_date'
            ]),
            'weight_kg' => $this->ruleRequiredUnlessDraft($isOpening, [
                'integer',
                'min:1'
            ]),
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

    protected function prepareForValidation(): void
    {
        $this->merge([
            'origin_country' => $this->origin_country ? CountryCodes::toAlpha3($this->origin_country) : null,
            'destination_country' => $this->destination_country ? CountryCodes::toAlpha3($this->destination_country) : null,
        ]);
    }

    private function ruleRequiredUnlessDraft(bool $isOpening, array $rules): array
    {
        return array_filter([
            $isOpening ? 'required' : 'sometimes',
            $isOpening ? null : 'nullable',
            ...$rules,
        ]);
    }
}
