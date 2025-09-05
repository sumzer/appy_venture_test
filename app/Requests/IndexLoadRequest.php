<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Support\CountryCodes;
use Illuminate\Validation\Rule;
use App\Enums\LoadStatus;

class IndexLoadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', Rule::in(array_map(fn($e) => $e->value, LoadStatus::cases()))],
            'origin_country' => ['sometimes', new CountryCodes()],
            'destination_country' => ['sometimes', new CountryCodes()],

            'pickup_from' => ['sometimes', 'date'],
            'pickup_to' => ['sometimes', 'date', 'after_or_equal:pickup_from'],
            'delivery_from' => ['sometimes', 'date'],
            'delivery_to' => ['sometimes', 'date', 'after_or_equal:delivery_from'],

            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'sort' => ['sometimes', Rule::in(['created_at', 'pickup_date', 'delivery_date'])],
            'order' => ['sometimes', Rule::in(['asc', 'desc'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'origin_country' => $this->origin_country ? strtoupper($this->origin_country) : null,
            'destination_country' => $this->destination_country ? strtoupper($this->destination_country) : null,
            'per_page' => $this->per_page ?? 20,
            'page' => $this->page ?? 1,
            'sort' => $this->sort ?? 'desc',
        ]);
    }

    public function filters(): array
    {
        $data = $this->validated();

        return [
            'status' => $data['status'] ?? null,
            'origin_country' => $data['origin_country'] ?? null,
            'destination_country' => $data['destination_country'] ?? null,

            'pickup_from' => $data['pickup_from'] ?? null,
            'pickup_to' => $data['pickup_to'] ?? null,
            'delivery_from' => $data['delivery_from'] ?? null,
            'delivery_to' => $data['delivery_to'] ?? null,
        ];
    }

    public function sort(): string
    {
        return $this->validated()['sort'] ?? 'created_at';
    }

    public function order(): string
    {
        return $this->validated()['order'] ?? 'desc';
    }

    public function perPage(): int
    {
        return $this->validated()['per_page'] ?? 20;
    }
}
