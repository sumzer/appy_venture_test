<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Load;
use App\Models\Bid;

class StoreBidRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isCarrier() ?? false;
    }
    public function rules(): array
    {
        $load = $this->route('load');
        return [
            'amount' => ['required', 'integer', 'min:1'],
            'message' => ['nullable', 'string', 'max:2000'],
            Rule::unique('bids')->where(
                fn($q) =>
                $q->where('load_id', $load->id)
                    ->where('carrier_id', $this->user()->id)
            ),
        ];
    }
}
