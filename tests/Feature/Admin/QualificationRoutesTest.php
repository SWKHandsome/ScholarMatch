<?php

use App\Models\Scholarship;
use App\Models\ScholarshipQualificationRoute;
use App\Services\ScholarshipRecommendationService;
use Illuminate\Support\Facades\DB;

beforeEach(fn () => seedIncomeCategories());

function alternativeRoutes(): array
{
    return [
        ['education_level' => 'Diploma', 'study_status' => 'completed', 'min_cgpa' => 3.00],
        ['education_level' => 'Undergraduate', 'study_status' => 'studying', 'min_cgpa' => 3.30, 'min_year_of_study' => 1, 'max_year_of_study' => 3],
    ];
}

function scholarshipFormPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Alternative Entry Award', 'provider' => 'Demo Provider', 'award_type' => 'Full Scholarship',
        'deadline' => today()->addMonth()->toDateString(), 'application_link' => 'https://example.com/apply',
        'is_active' => 1, 'rules_submitted' => 1, 'qualification_routes_submitted' => 1,
        'required_study_level' => 'Undergraduate', 'qualification_routes' => alternativeRoutes(),
    ], $overrides);
}

test('scholarship add and edit pages include the shared qualification editor', function () {
    $scholarship = makeScholarship();
    $this->actingAs(makeAdmin());
    foreach ([route('admin.scholarships.create'), route('admin.scholarships.edit', $scholarship)] as $url) {
        $this->get($url)->assertOk()->assertSee('Accepted Qualification Routes')->assertSee('+ Add Qualification')
            ->assertSee('Qualification Level')->assertSee('Minimum SPM Credits')->assertSee('Minimum CGPA')
            ->assertSee('Minimum Year of Study')->assertSee('Either status');
    }
});

test('scholarship creation saves general rules and independent routes together', function () {
    $this->actingAs(makeAdmin())->post(route('admin.scholarships.store'), scholarshipFormPayload())
        ->assertSessionHasNoErrors()->assertRedirect(route('admin.scholarships.index'));
    $scholarship = Scholarship::where('name', 'Alternative Entry Award')->firstOrFail();
    expect($scholarship->rule->required_study_level)->toBe('Undergraduate');
    expect($scholarship->rule->qualificationRoutes)->toHaveCount(2);
    expect($scholarship->rule->qualificationRoutes[0]->min_cgpa)->toBe('3.00');
    expect($scholarship->rule->qualificationRoutes[1]->max_year_of_study)->toBe(3);
});

test('invalid route prevents saving any part of a scholarship create or edit', function () {
    $this->actingAs(makeAdmin());
    $payload = scholarshipFormPayload(['qualification_routes' => [['education_level' => 'Diploma', 'min_cgpa' => 3]]]);
    $this->post(route('admin.scholarships.store'), $payload)->assertSessionHasErrors('qualification_routes.0.study_status');
    expect(Scholarship::where('name', $payload['name'])->exists())->toBeFalse();
    $scholarship = makeScholarship(['name' => 'Preserve me']);
    $this->put(route('admin.scholarships.update', $scholarship), $payload)->assertSessionHasErrors('qualification_routes.0.study_status');
    expect($scholarship->fresh()->name)->toBe('Preserve me');
    expect($scholarship->fresh()->rule->min_cgpa)->toBe('3.00');
});

test('editing replaces and removes routes without leaving stale academic restrictions', function () {
    $scholarship = makeScholarship();
    $other = makeScholarship(['name' => 'Other']);
    $other->rule->qualificationRoutes()->createMany(alternativeRoutes());
    $this->actingAs(makeAdmin())->put(route('admin.scholarships.update', $scholarship), scholarshipFormPayload())
        ->assertSessionHasNoErrors();
    expect($scholarship->fresh()->rule->qualificationRoutes)->toHaveCount(2);
    $this->put(route('admin.scholarships.update', $scholarship), scholarshipFormPayload(['qualification_routes' => []]))
        ->assertSessionHasNoErrors();
    $rule = $scholarship->fresh()->rule;
    expect($rule->academicRoutes())->toBe([])->and($rule->min_cgpa)->toBeNull();
    expect($other->fresh()->rule->qualificationRoutes)->toHaveCount(2);
    expect(app(ScholarshipRecommendationService::class)->getRecommendations(makeStudent([], ['cgpa' => 0]))['recommendations'][0]['status'])->toBe('Eligible');
});

test('inactive result and year values are cleared on the server too', function () {
    $scholarship = makeScholarship();
    $this->actingAs(makeAdmin())->post(route('admin.scholarships.rules.store', $scholarship), [
        'qualification_routes' => [
            ['education_level' => 'SPM', 'study_status' => 'completed', 'min_spm_as' => 8, 'min_cgpa' => 4, 'min_year_of_study' => 3],
            ['education_level' => 'Diploma', 'study_status' => 'completed', 'min_cgpa' => 3, 'min_spm_as' => 12, 'max_year_of_study' => 2],
        ],
    ])->assertSessionHasNoErrors();
    $routes = $scholarship->fresh()->rule->qualificationRoutes;
    expect($routes[0]->min_cgpa)->toBeNull()->and($routes[0]->min_year_of_study)->toBeNull();
    expect($routes[1]->min_spm_as)->toBeNull()->and($routes[1]->max_year_of_study)->toBeNull();
});

test('one sided year limits and explicit zero minima are supported', function () {
    $scholarship = makeScholarship();
    $this->actingAs(makeAdmin())->post(route('admin.scholarships.rules.store', $scholarship), [
        'qualification_routes' => [
            ['education_level' => 'Undergraduate', 'study_status' => 'studying', 'min_cgpa' => 0, 'max_year_of_study' => 2],
            ['education_level' => 'Diploma', 'study_status' => 'studying', 'min_year_of_study' => 1],
        ],
    ])->assertSessionHasNoErrors();
    expect($scholarship->fresh()->rule->qualificationRoutes[0]->min_cgpa)->toBe('0.00');
});

test('form submission without initialized JavaScript cannot erase qualification routes', function () {
    $scholarship = makeScholarship();
    $scholarship->rule->qualificationRoutes()->createMany(alternativeRoutes());
    $payload = scholarshipFormPayload();
    unset($payload['qualification_routes_submitted'], $payload['qualification_routes']);
    $this->actingAs(makeAdmin())->put(route('admin.scholarships.update', $scholarship), $payload)
        ->assertSessionHasErrors('qualification_routes_submitted');
    expect($scholarship->fresh()->rule->qualificationRoutes)->toHaveCount(2);
});

test('deleting a scholarship cascades its routes', function () {
    $scholarship = makeScholarship();
    $scholarship->rule->qualificationRoutes()->createMany(alternativeRoutes());
    $this->actingAs(makeAdmin())->delete(route('admin.scholarships.destroy', $scholarship))->assertRedirect();
    expect(ScholarshipQualificationRoute::count())->toBe(0);
});

test('legacy migration preserves mixed thresholds and unrestricted rules', function () {
    $migration = require database_path('migrations/2026_10_19_010000_create_scholarship_qualification_routes_table.php');
    $migration->down();
    $mixed = makeScholarship([], ['accepted_education_levels' => ['SPM', 'Diploma'], 'min_spm_as' => 8, 'min_spm_credits' => 9, 'min_cgpa' => 3.2]);
    $unrestricted = makeScholarship([], ['min_cgpa' => null]);
    $before = DB::table('scholarship_rules')->count();
    $migration->up();
    expect(DB::table('scholarship_rules')->count())->toBe($before);
    $routes = $mixed->fresh()->rule->qualificationRoutes;
    expect($routes)->toHaveCount(2);
    expect($routes[0]->study_status)->toBe('completed')->and($routes[0]->min_cgpa)->toBeNull();
    expect($routes[1]->study_status)->toBe('any')->and($routes[1]->min_cgpa)->toBe('3.20');
    expect($routes[1]->min_spm_as)->toBeNull();
    expect($unrestricted->fresh()->rule->academicRoutes())->toBe([]);
});
