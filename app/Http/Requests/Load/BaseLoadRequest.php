<?php
namespace App\Http\Requests\Load;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Support\CountryCodes;
use App\Enums\LoadStatus;

abstract class BaseLoadRequest extends FormRequest
{
    abstract protected function allowedStatuses(): array;

    protected function isOpening(): bool
    {
        $current = $this->route('load')?->status;
        $final = $this->input('status', $current);
        return $final === LoadStatus::Open->value;
    }

    protected function ruleRequiredUnlessDraft(bool $isOpening, array $rules): array
    {
        return array_filter([
            $isOpening ? 'required' : 'sometimes',
            $isOpening ? null : 'nullable',
            ...$rules,
        ]);
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('origin_country')) {
            $val = $this->input('origin_country');
            $this->merge([
                'origin_country' => ($val === null || $val === '')
                    ? null
                    : CountryCodes::toAlpha3($val),
            ]);
        }

        if ($this->has('destination_country')) {
            $val = $this->input('destination_country');
            $this->merge([
                'destination_country' => ($val === null || $val === '')
                    ? null
                    : CountryCodes::toAlpha3($val),
            ]);
        }
    }

    public function rules(): array
    {
        $isOpening = $this->isOpening();

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
            'price_expectation' => ['sometimes', 'nullable', 'integer', 'min:1'],

            'status' => ['required', Rule::in($this->allowedStatuses())],
        ];
    }
}
