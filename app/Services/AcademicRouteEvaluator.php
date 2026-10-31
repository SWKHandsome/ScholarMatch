<?php

namespace App\Services;

use App\Models\AcademicResult;
use App\Support\AcademicQualifications;

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
                    $levelMatches, 'Accepted latest qualification: '.AcademicQualifications::label($route['education_level']).'; your latest qualification: '.AcademicQualifications::label($academic->education_level).'.'),
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
            $thresholds = match (true) {
                $route['education_level'] === 'SPM' => ['min_spm_as' => ['spm_as', 'SPM As'], 'min_spm_credits' => ['spm_credits', 'SPM credits']],
                AcademicQualifications::isCgpa($route['education_level']) => ['min_cgpa' => ['cgpa', 'CGPA']],
                default => [],
            };
            foreach ($thresholds as $key => [$attribute, $label]) {
                $required = $route[$key] ?? null;
                $actual = $levelMatches ? $academic->$attribute : null;
                if ($levelMatches && $route['education_level'] === 'SPM') {
                    $actual = AcademicQualifications::countAtOrAbove($academic->grade_counts ?? [], 'spm', $attribute === 'spm_as' ? 'A-' : 'C');
                }
                $checks[] = $this->check($key, $label, $required, $actual, $actual !== null && (float) $actual >= (float) $required,
                    $levelMatches ? "Minimum $label: $required; your official result: $actual."
                        : "This route requires {$route['education_level']} results; your latest qualification is {$academic->education_level}.", $required !== null);
            }
            if (AcademicQualifications::isGradeBased($route['education_level'])) {
                $scale = ($route['grading_scale'] ?? null) ?: AcademicQualifications::defaultScale($route['education_level']);
                $scaleMatches = $levelMatches && $academic->grading_scale === $scale;
                if ($route['education_level'] === 'IGCSE' && $scale !== null) {
                    $scaleLabel = AcademicQualifications::scales()[$scale]['label'] ?? $scale;
                    $actualScale = AcademicQualifications::scales()[$academic->grading_scale]['label'] ?? $academic->grading_scale;
                    $checks[] = $this->check('grading_scale', 'IGCSE grading scale', $scaleLabel, $actualScale, $scaleMatches,
                        "Required grading scale: $scaleLabel; your grading scale: $actualScale. No cross-scale conversion is applied.");
                }
                foreach ($route['grade_requirements'] ?? [] as $position => $condition) {
                    $grade = $condition['grade'];
                    $required = (int) $condition['min_subjects'];
                    $actual = $scaleMatches ? AcademicQualifications::countAtOrAbove($academic->grade_counts ?? [], $scale, $grade) : null;
                    $label = "Subjects at $grade or above";
                    $checks[] = $this->check('min_grade_count_'.$position, $label, $required, $actual,
                        $actual !== null && $actual >= $required,
                        $actual !== null ? "Required: $required subjects at $grade or above; your official results: $actual."
                            : 'This grade condition requires the matching qualification and grading scale.');
                }
            }
            if ($route['education_level'] === 'STAM') {
                $required = $route['min_stam_rank'] ?? null;
                $actual = $levelMatches ? $academic->stam_rank : null;
                $minimumPosition = array_search($required, AcademicQualifications::RANKS, true);
                $actualPosition = array_search($actual, AcademicQualifications::RANKS, true);
                $checks[] = $this->check('min_stam_rank', 'STAM overall pangkat', $required, $actual,
                    $minimumPosition !== false && $actualPosition !== false && $actualPosition <= $minimumPosition,
                    "Minimum STAM pangkat: $required; your official pangkat: ".($actual ?? 'a different qualification').'.', $required !== null);
            }
            if ($route['education_level'] === 'UEC' && ! empty($route['uec_average'])) {
                $average = $route['uec_average'];
                $actual = $levelMatches ? AcademicQualifications::uecAverage($academic->grade_counts ?? [], (int) $average['subjects'], $average['minimum_grade']) : null;
                $required = 'Best '.$average['subjects'].' subjects at '.$average['minimum_grade'].' or above; average ≤ '.number_format($average['max_average'], 2);
                $display = $actual === null ? 'Not enough qualifying subjects' : number_format($actual, 4);
                $checks[] = $this->check('max_uec_average', 'UEC best-subject average', $required, $display,
                    $actual !== null && $actual <= (float) $average['max_average'] + 0.000000001,
                    "Required: $required; your result: $display. UEC grade numbers run from A1 = 1 to F9 = 9; a lower average is better.");
            }
            foreach (['stpm_average' => ['STPM', 'stpm'], 'uec_total' => ['UEC', 'uec']] as $field => [$level, $scale]) {
                if ($route['education_level'] !== $level || empty($route[$field])) {
                    continue;
                }
                $settings = $route[$field];
                $subjects = (int) $settings['subjects'];
                $total = $levelMatches && $academic->grading_scale === $scale
                    ? AcademicQualifications::bestSubjectPoints($academic->grade_counts ?? [], $scale, $subjects, $settings['minimum_grade']) : null;
                $isAverage = $field === 'stpm_average';
                $actual = $total === null ? null : ($isAverage ? $total / $subjects : $total);
                $limit = $settings[$isAverage ? 'min_average' : 'max_total'];
                $required = 'Best '.$subjects.' subjects at '.$settings['minimum_grade'].' or above; '
                    .($isAverage ? 'average ≥ '.number_format($limit, 2) : 'total ≤ '.$limit.' points');
                $display = $actual === null ? 'Not enough qualifying subjects' : ($isAverage ? number_format($actual, 4) : (string) (int) $actual);
                $met = $actual !== null && ($isAverage ? $actual + 0.000000001 >= (float) $limit : $actual <= (float) $limit);
                $checks[] = $this->check($isAverage ? 'min_stpm_average' : 'max_uec_total', $isAverage ? 'STPM best-subject average' : 'UEC best-subject total',
                    $required, $display, $met, "Required: $required; your result: $display. "
                    .($isAverage ? 'Uses official STPM subject grade points; this is separate from your overall CGPA.' : 'A1 = 1 through F9 = 9; a lower total is better.'));
            }
            $failures = count(array_filter($checks, fn ($check) => $check['outcome'] === 'not_met'));
            $evaluations[] = [
                'label' => 'Route '.($index + 1).': '.AcademicQualifications::label($route['education_level']).' — '.$this->statusLabel($status),
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
