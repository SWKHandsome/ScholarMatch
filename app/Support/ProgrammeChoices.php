<?php

namespace App\Support;

class ProgrammeChoices
{
    // Explicit student answer, distinct from a missing value or an admin restriction.
    public const UNDECIDED = '__undecided__';

    public const UNDECIDED_LABEL = "Haven't decided";

    public static function label(?string $value): ?string
    {
        return $value === self::UNDECIDED ? self::UNDECIDED_LABEL : $value;
    }
}
