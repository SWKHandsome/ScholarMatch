<?php

namespace App\Services;

use App\Models\AcademicResult;

class AcademicRouteEvaluator
{
    public function evaluate(array $routes, AcademicResult $academic): array
    {
        if (! $routes) {
            return [$this->check('academic_route', 'Qualification route', null, $academic->education_level, true,
                'No qualification route restriction is specified.', false)];
        }

        $evaluations = [];
        foreach ($routes as $index => $route) {
            $levelMatches = $academic->education_level === $route['education_level'];
            $status = $route['study_status'];
            $statusMatches = $status === 'any' || $academic->study_status === $status;
            $checks = [
                $this->check('qualification', 'Latest qualification', $route['education_level'], $academic->education_level,
                    $levelMatches, "Accepted latest qualification: {$route['education_level']}; your latest level: {$academic->education_level}."),
                $this->check('study_status', 'Study status', $this->statusLabel($status), $this->statusLabel($academic->study_status),
                    $statusMatches, 'Required study status: '.$this->statusLabel($status).'; your status: '.$this->statusLabel($academic->study_status).'.', $status !== 'any'),
            ];
            foreach (['min_year_of_study' => 'Minimum year of study', 'max_year_of_study' => 'Maximum year of study'] as $key => $label) {
                $required = $route[$key] ?? null;
                $met = $academic->study_status === 'studying' && $academic->year_of_study !== null
                    && ($key === 'min_year_of_study' ? $academic->year_of_study >= $required : $academic->year_of_study <= $required);
                $checks[] = $this->check($key, $label, $required, $academic->year_of_study, $met,
                    "$label: $required; your current year: ".($academic->year_of_study ?? 'not currently studying').'.', $required !== null);
            }
            $thresholds = $route['education_level'] === 'SPM'
                ? ['min_spm_as' => ['spm_as', 'SPM As'], 'min_spm_credits' => ['spm_credits', 'SPM credits']]
                : ['min_cgpa' => ['cgpa', 'CGPA']];
            foreach ($thresholds as $key => [$attribute, $label]) {
                $required = $route[$key] ?? null;
                $actual = $levelMatches ? $academic->$attribute : null;
                $checks[] = $this->check($key, $label, $required, $actual, $actual !== null && (float) $actual >= (float) $required,
                    $levelMatches ? "Minimum $label: $required; your official result: $actual."
                        : "This route requires {$route['education_level']} results; your latest qualification is {$academic->education_level}.", $required !== null);
            }
            $failures = count(array_filter($checks, fn ($check) => $check['outcome'] === 'not_met'));
            $evaluations[] = [
                'label' => 'Route '.($index + 1).': '.$route['education_level'].' — '.$this->statusLabel($status),
                'passed' => $failures === 0, 'checks' => $checks,
                'rank' => [$failures === 0 ? 0 : 1, $levelMatches ? 0 : 1, $statusMatches ? 0 : 1, $failures, $index],
            ];
        }
        $ranked = $evaluations;
        usort($ranked, fn ($a, $b) => $a['rank'] <=> $b['rank']);
        $selected = $ranked[0];
        $summary = $this->check('academic_route', 'Qualification route (pass any one)', array_column($evaluations, 'label'),
            $academic->education_level.' — '.$this->statusLabel($academic->study_status), $selected['passed'],
            $selected['passed'] ? 'You meet '.$selected['label'].'.' : 'None of the qualification routes is fully satisfied. Review the route conditions below.');
        $summary['routes'] = array_map(function ($evaluation) {
            unset($evaluation['rank']);

            return $evaluation;
        }, $evaluations);

        // Only one passing (or closest relevant failing) route contributes to the decision.
        return array_merge([$summary], $selected['checks']);
    }

    private function check(string $key, string $label, mixed $required, mixed $actual, bool $met, string $message, bool $applicable = true): array
    {
        return [
            'key' => $key, 'label' => $label, 'required' => $required, 'actual' => $actual,
            'outcome' => ! $applicable ? 'not_applicable' : ($met ? 'met' : 'not_met'),
            'message' => $applicable ? $message : "No $label requirement is specified.",
        ];
    }

    private function statusLabel(string $status): string
    {
        return ['studying' => 'Currently Studying', 'completed' => 'Completed', 'any' => 'Either status'][$status] ?? $status;
    }
}
