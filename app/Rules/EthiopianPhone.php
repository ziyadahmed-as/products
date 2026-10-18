<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class EthiopianPhone implements ValidationRule
{
    /**
     * Run the validation rule.
     * Validates Ethiopian phone number format: +251 followed by 9 digits (total 12 digits).
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        // Clean out whitespace for flexible validation
        $cleaned = str_replace(' ', '', (string) $value);

        // Check if starts with +251 followed by exactly 9 digits (total 12 digits)
        if (!preg_match('/^\+251[0-9]{9}$/', $cleaned)) {
            $fail("The :attribute must start with +251 followed by 9 digits (total 12 digits, e.g. +251912345678).");
        }
    }
}
