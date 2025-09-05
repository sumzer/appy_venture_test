<?php

namespace App\Casts;

use App\Support\CountryCodes;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class Alpha3Country implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        return $value;
    }

    public function set($model, string $key, $value, array $attributes)
    {
        return CountryCodes::toAlpha3((string) $value);
    }
}

