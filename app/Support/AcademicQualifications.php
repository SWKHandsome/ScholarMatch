<?php

namespace App\Support;

class AcademicQualifications
{
    public const LEVELS = ['SPM', 'IGCSE', 'O-Level', 'STPM', 'STAM', 'A-Level', 'UEC', 'Matriculation', 'Foundation', 'Diploma', 'Undergraduate'];

    public const LEGACY_LEVELS = ['SPM', 'STPM', 'Foundation', 'Matriculation', 'Diploma', 'Undergraduate'];

    public const CGPA_LEVELS = ['STPM', 'Foundation', 'Matriculation', 'Diploma', 'Undergraduate'];

    public const RANKS = ['Mumtaz', 'Jayyid Jiddan', 'Jayyid', 'Maqbul', 'Rasib'];

    public static function categories(): array
    {
        return [
            'secondary' => ['label' => 'Secondary', 'qualifications' => ['SPM', 'IGCSE', 'O-Level']],
            'pathway' => ['label' => 'Pre-U, Diploma & Pathway', 'qualifications' => ['STPM', 'STAM', 'A-Level', 'UEC', 'Matriculation', 'Foundation', 'Diploma']],
            'undergraduate' => ['label' => 'Undergraduate', 'qualifications' => ['Undergraduate']],
        ];
    }

    public static function label(?string $level): string
    {
        return match ($level) {
            'Undergraduate' => 'Bachelor’s Degree',
            'UEC' => 'Senior UEC',
            'O-Level' => 'Cambridge O-Level',
            'IGCSE' => 'Cambridge IGCSE',
            default => $level ?? '',
        };
    }

    public static function category(?string $level): ?string
    {
        foreach (self::categories() as $key => $category) {
            if (in_array($level, $category['qualifications'], true)) {
                return $key;
            }
        }

        return null;
    }

    public static function isCgpa(?string $level): bool
    {
        return in_array($level, self::CGPA_LEVELS, true);
    }

    public static function isGradeBased(?string $level): bool
    {
        return in_array($level, ['SPM', 'IGCSE', 'O-Level', 'A-Level', 'UEC', 'STPM'], true);
    }

    public static function scales(): array
    {
        return [
            'spm' => ['label' => 'SPM grades', 'grades' => ['A+', 'A', 'A-', 'B+', 'B', 'C+', 'C', 'D', 'E', 'G'], 'max_subjects' => 12],
            'igcse_letters' => ['label' => 'IGCSE A*–G', 'grades' => ['A*', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'U'], 'max_subjects' => 20],
            'igcse_9_1' => ['label' => 'IGCSE 9–1', 'grades' => ['9', '8', '7', '6', '5', '4', '3', '2', '1', 'U'], 'max_subjects' => 20],
            'o_level' => ['label' => 'Cambridge O-Level grades', 'grades' => ['A*', 'A', 'B', 'C', 'D', 'E', 'U'], 'max_subjects' => 20],
            'a_level' => ['label' => 'Full A-Level grades', 'grades' => ['A*', 'A', 'B', 'C', 'D', 'E', 'U'], 'max_subjects' => 20],
            'uec' => ['label' => 'Senior UEC grades', 'grades' => ['A1', 'A2', 'B3', 'B4', 'B5', 'B6', 'C7', 'C8', 'F9'], 'max_subjects' => 20],
            'stpm' => ['label' => 'STPM subject grades', 'grades' => ['A', 'A-', 'B+', 'B', 'B-', 'C+', 'C', 'C-', 'D+', 'D', 'F'], 'max_subjects' => 5],
        ];
    }

    public static function allowedScales(?string $level): array
    {
        return match ($level) {
            'SPM' => ['spm'], 'IGCSE' => ['igcse_letters', 'igcse_9_1'],
            'O-Level' => ['o_level'], 'A-Level' => ['a_level'], 'UEC' => ['uec'], 'STPM' => ['stpm'],
            default => [],
        };
    }

    public static function defaultScale(?string $level): ?string
    {
        $scales = self::allowedScales($level);

        return count($scales) === 1 ? $scales[0] : null;
    }

    public static function validCounts(?string $level, ?string $scale, mixed $counts): bool
    {
        if (! in_array($scale, self::allowedScales($level), true) || ! is_array($counts) || $counts === []) {
            return false;
        }
        $definition = self::scales()[$scale];
        foreach ($counts as $grade => $count) {
            if (! in_array((string) $grade, $definition['grades'], true)
                || is_bool($count) || filter_var($count, FILTER_VALIDATE_INT) === false || (int) $count < 0) {
                return false;
            }
        }

        return array_sum($counts) >= 1 && array_sum($counts) <= $definition['max_subjects'];
    }

    public static function countAtOrAbove(array $counts, string $scale, string $grade): int
    {
        $grades = self::scales()[$scale]['grades'] ?? [];
        $position = array_search($grade, $grades, true);
        if ($position === false) {
            return 0;
        }

        return array_sum(array_intersect_key($counts, array_flip(array_slice($grades, 0, $position + 1))));
    }

    public static function summary(array $counts, ?string $scale): string
    {
        $parts = [];
        foreach (self::scales()[$scale]['grades'] ?? [] as $grade) {
            if (($counts[$grade] ?? 0) > 0) {
                $parts[] = $counts[$grade].' × '.$grade;
            }
        }

        return implode(', ', $parts);
    }

    public static function uecAverage(array $counts, int $subjects, string $minimumGrade): ?float
    {
        $total = self::bestSubjectPoints($counts, 'uec', $subjects, $minimumGrade);

        return $total === null ? null : $total / $subjects;
    }

    public static function bestSubjectPoints(array $counts, string $scale, int $subjects, string $minimumGrade): ?float
    {
        $grades = self::scales()[$scale]['grades'] ?? [];
        $position = array_search($minimumGrade, $grades, true);
        if ($position === false || $subjects < 1) {
            return null;
        }
        $remaining = $subjects;
        $total = 0;
        foreach (array_slice($grades, 0, $position + 1) as $index => $grade) {
            $take = min($remaining, (int) ($counts[$grade] ?? 0));
            $points = $scale === 'stpm' ? self::stpmPoints()[$grade] : $index + 1;
            $total += $take * $points;
            $remaining -= $take;
        }

        return $remaining === 0 ? $total : null;
    }

    public static function stpmPoints(): array
    {
        return ['A' => 4.00, 'A-' => 3.67, 'B+' => 3.33, 'B' => 3.00, 'B-' => 2.67,
            'C+' => 2.33, 'C' => 2.00, 'C-' => 1.67, 'D+' => 1.33, 'D' => 1.00, 'F' => 0.00];
    }

    public static function stpmCgpa(mixed $counts, ?string $pengajianAmGrade): ?string
    {
        if (! self::validCounts('STPM', 'stpm', $counts) || array_sum($counts) < 4
            || ! in_array($pengajianAmGrade, self::scales()['stpm']['grades'], true)
            || ($counts[$pengajianAmGrade] ?? 0) < 1) {
            return null;
        }
        $remainingCounts = array_map('intval', $counts);
        $remainingCounts[$pengajianAmGrade]--;
        $points = array_map(fn ($point) => (int) round($point * 100), self::stpmPoints());
        $total = $points[$pengajianAmGrade];
        $remainingSubjects = 3;
        foreach (self::scales()['stpm']['grades'] as $grade) {
            $take = min($remainingSubjects, $remainingCounts[$grade] ?? 0);
            $total += $take * $points[$grade];
            $remainingSubjects -= $take;
        }

        // Work in hundredths, then round the four-subject mean half up to two decimals.
        return number_format(intdiv($total + 2, 4) / 100, 2, '.', '');
    }

    public static function frontend(): array
    {
        return ['categories' => self::categories(), 'labels' => array_combine(self::LEVELS, array_map(self::label(...), self::LEVELS)),
            'stpmPoints' => self::stpmPoints(),
            'scales' => self::scales(), 'cgpaLevels' => self::CGPA_LEVELS,
            'qualificationScales' => array_combine(self::LEVELS, array_map(self::allowedScales(...), self::LEVELS))];
    }
}
