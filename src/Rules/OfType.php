<?php

declare(strict_types=1);

namespace Honed\Upload\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

final class OfType implements ValidationRule
{
    /**
     * @param  array<int, string>  $types
     */
    public function __construct(
        protected array $types
    ) {}

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($this->types)) {
            return;
        }

        if (! \is_string($value)) {
            $fail('The :attribute must be a string.');

            return;
        }

        foreach ($this->types as $type) {
            if ($this->isWildcard($type) && Str::startsWith($value, $type)) {
                return;
            }

            if (Str::is($type, $value)) {
                return;
            }
        }

        $fail('The :attribute is not supported.');
    }

    /**
     * Determine if the MIME type is a partial.
     */
    protected function isWildcard(string $type): bool
    {
        return Str::endsWith($type, '/') || Str::endsWith($type, '*');
    }
}
