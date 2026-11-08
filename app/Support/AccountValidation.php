<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AccountValidation
{
    // Shared with HTML pattern attributes; the server remains authoritative.
    public const NAME_PATTERN = "(?=.*\\p{L})[\\p{L}\\p{M} .'’\\/\\-]+";

    public const EMAIL_PATTERN = '[^@\\s]+@(?:[A-Za-z0-9](?:[A-Za-z0-9\\-]*[A-Za-z0-9])?\\.)+[A-Za-z]{2,63}';

    public const NAME_HINT = 'Use letters, spaces, apostrophes, hyphens, periods or / (up to 100 characters).';

    public const EMAIL_HINT = 'Enter a complete email address, such as student@gmail.com.';

    public static function normalize(Request $request): void
    {
        foreach (['name', 'email'] as $field) {
            if (is_string($request->input($field))) {
                $value = trim($request->input($field));
                $request->merge([$field => $field === 'email' ? Str::lower($value) : $value]);
            }
        }
    }

    public static function nameRules(): array
    {
        return ['bail', 'required', 'string', 'max:100', 'regex:~\A'.self::NAME_PATTERN.'\z~u'];
    }

    public static function emailFormatRules(): array
    {
        return ['bail', 'required', 'string', 'max:255', 'email:rfc', 'regex:~\A'.self::EMAIL_PATTERN.'\z~u'];
    }

    public static function emailRules(?User $except = null): array
    {
        return [...self::emailFormatRules(), Rule::unique(User::class)->ignore($except?->id)];
    }

    public static function messages(): array
    {
        return [
            'name.regex' => self::NAME_HINT,
            'email.email' => self::EMAIL_HINT,
            'email.regex' => self::EMAIL_HINT,
        ];
    }
}
