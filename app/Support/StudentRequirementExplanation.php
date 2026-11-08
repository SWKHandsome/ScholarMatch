<?php

namespace App\Support;

class StudentRequirementExplanation
{
    public static function prepare(array $checks): array
    {
        $byKey = array_column($checks, null, 'key');
        $routes = $byKey['academic_route']['routes'] ?? [];
        $academicKeys = [];
        foreach ($routes as $route) {
            $academicKeys = array_merge($academicKeys, array_column($route['checks'], 'key'));
        }
        $academic = array_values(array_filter($checks, fn ($check) => in_array($check['key'], $academicKeys, true)));
        $selected = null;
        foreach ($routes as $index => $route) {
            if ($route['checks'] === $academic) {
                $selected = $route;
                unset($routes[$index]);
                break;
            }
        }

        $qualification = $byKey['qualification'] ?? null;
        $status = $byKey['study_status'] ?? null;
        $rows = [];
        if ($qualification) {
            $combined = $qualification;
            $combined['label'] = 'Accepted qualification';
            $combined['required'] = AcademicQualifications::label($qualification['required']).($status ? ' · '.$status['required'] : '');
            $combined['actual'] = AcademicQualifications::label($qualification['actual']).($status ? ' · '.$status['actual'] : '');
            $combined['outcome'] = $qualification['outcome'] === 'not_met' || ($status['outcome'] ?? null) === 'not_met' ? 'not_met' : 'met';
            $combined['gap'] = implode(' ', array_filter([EligibilityGap::describe($qualification), $status ? EligibilityGap::describe($status) : null]));
            $rows[] = self::row($combined);
        }

        $inactive = [];
        foreach ($checks as $check) {
            if (in_array($check['key'], ['qualification', 'study_status'], true)) {
                continue;
            }
            if ($check['outcome'] === 'not_applicable') {
                if ($check['key'] !== 'deadline') {
                    $inactive[] = self::row($check);
                }

                continue;
            }
            if ($check['key'] !== 'academic_route') {
                $rows[] = self::row($check);
            }
        }

        return [
            'failed' => array_values(array_filter($rows, fn ($row) => $row['outcome'] === 'not_met')),
            'unchecked' => array_values(array_filter($rows, fn ($row) => $row['outcome'] === 'not_checked')),
            'academic' => array_values(array_filter($rows, fn ($row) => $row['outcome'] === 'met' && in_array($row['key'], $academicKeys, true))),
            'general' => array_values(array_filter($rows, fn ($row) => $row['outcome'] === 'met' && ! in_array($row['key'], $academicKeys, true))),
            'inactive' => $inactive, 'selected' => $selected, 'alternatives' => array_values($routes),
        ];
    }

    public static function row(array $check): array
    {
        $format = function ($value) use ($check): string {
            if ($value === null || $value === []) {
                return '—';
            }
            if (is_array($value)) {
                return implode(', ', $value);
            }
            if (in_array($check['key'], ['qualification', 'funding_level'], true)) {
                return AcademicQualifications::label((string) $value);
            }
            if ($check['key'] === 'max_household_income' && is_numeric($value)) {
                return 'RM '.number_format((float) $value, 2);
            }

            return (string) $value;
        };

        return array_merge($check, [
            'gap' => $check['gap'] ?? EligibilityGap::describe($check),
            'label' => match (true) {
                str_starts_with($check['key'], 'min_grade_count_') => 'At least '.$check['required'].' '.preg_replace('/^Subjects /', (int) $check['required'] === 1 ? 'subject ' : 'subjects ', $check['label']),
                $check['key'] === 'min_cgpa' => 'Minimum CGPA',
                $check['key'] === 'min_spm_as' => 'Minimum SPM As',
                $check['key'] === 'min_spm_credits' => 'Minimum SPM credits',
                default => $check['label'],
            },
            'required' => $format($check['required']), 'actual' => $format($check['actual']),
        ]);
    }
}
