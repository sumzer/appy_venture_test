<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use League\ISO3166\ISO3166;

class CountryCode implements ValidationRule
{
    public function __construct(
        private bool $allowAlpha2 = true,
        private bool $allowAlpha3 = true,
    ) {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $code = strtoupper(trim((string) $value));

        if (!preg_match('/^[A-Z]{2,3}$/', $code)) {
            $fail("The $attribute must be a valid ISO 3166-1 alpha-2 or alpha-3 code.");
            return;
        }

        $iso = new ISO3166();

        try {
            if (strlen($code) === 2 && $this->allowAlpha2) {
                $iso->alpha2($code);
                return;
            }
            if (strlen($code) === 3 && $this->allowAlpha3) {
                $iso->alpha3($code);
                return;
            }
        } catch (\Throwable $e) {

        }

        $fail("The $attribute must be a valid ISO 3166-1 country code.");
    }
}
