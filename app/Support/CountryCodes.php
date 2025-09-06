<?php
namespace App\Support;

use League\ISO3166\ISO3166;

class CountryCodes
{
    /**
     * Convert any given country code (alpha-2 or alpha-3) into an ISO 3166-1 alpha-3 code.
     *
     * @param string $inputCode  The country code (alpha-2 or alpha-3).
     * @param array<string,string> $overrides  Optional overrides (e.g. 'UK' => 'GBR').
     *
     * @return string  The normalized alpha-3 country code.
     *
     * @throws \InvalidArgumentException If the provided code is not valid.
     *
     * @example
     * CountryCodes::toAlpha3('DE');   // returns "DEU"
     * CountryCodes::toAlpha3('GB');   // returns "GBR"
     * CountryCodes::toAlpha3('UK');   // returns "GBR" (override)
     * CountryCodes::toAlpha3('USA');  // returns "USA"
     */
    public static function toAlpha3(
        string $inputCode,
        array $overrides = [
            'UK' => 'GBR',
        ]
    ): string {
        $normalizedCode = strtoupper(trim($inputCode));

        if (isset($overrides[$normalizedCode])) {
            return $overrides[$normalizedCode];
        }

        $iso3166 = new ISO3166();

        if (strlen($normalizedCode) === 3) {
            $iso3166->alpha3($normalizedCode);
            return $normalizedCode;
        }

        if (strlen($normalizedCode) === 2) {
            return $iso3166->alpha2($normalizedCode)['alpha3'];
        }

        throw new \InvalidArgumentException("Invalid country code: $inputCode");
    }

    public static function allAlpha3(): array
    {
        return collect((new ISO3166())->all())
            ->pluck('alpha3')
            ->unique()
            ->values()
            ->toArray();
    }

    public static function allAlpha2(): array
    {
        return collect((new ISO3166())->all())
            ->pluck('alpha2')
            ->unique()
            ->values()
            ->toArray();
    }

    public static function allValidCodes(): array
    {
        $iso = new ISO3166();
        return collect($iso->all())
            ->flatMap(fn($row) => [$row['alpha2'], $row['alpha3']])
            ->unique()
            ->values()
            ->toArray();
    }
}
