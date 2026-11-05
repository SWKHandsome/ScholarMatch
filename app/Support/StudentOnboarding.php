<?php

namespace App\Support;

use App\Models\User;

class StudentOnboarding
{
    public static function nextRoute(User $user): ?string
    {
        if (! $user->isStudent()) {
            return null;
        }
        if (! $user->studentProfile?->isComplete()) {
            return 'student.profile.edit';
        }
        if (! $user->academicResult?->isReadyForRecommendations()) {
            return 'student.academic-result.edit';
        }

        return null;
    }
}
