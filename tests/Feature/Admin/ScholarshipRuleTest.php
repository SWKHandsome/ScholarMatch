<?php

use App\Models\ScholarshipRule;
use App\Services\ScholarshipRecommendationService;

beforeEach(fn () => seedIncomeCategories());

test('admin rule forms expose requirements without hard soft or scoring selectors', function () {
    $admin = makeAdmin();
    $scholarship = makeScholarship();
    foreach (['create', 'edit'] as $action) {
        $parameters = $action === 'edit' ? [$scholarship, $scholarship->rule] : $scholarship;
        $this->actingAs($admin)->get(route('admin.scholarships.rules.'.$action, $parameters))->assertOk()
            ->assertSee('Funded Study Level')->assertSee('Accepted Qualification Routes')->assertSee('+ Add Qualification')
            ->assertSee('Supported Fields of Study')->assertDontSee('Income Rule Type')
            ->assertDontSee('Soft (scoring)')->assertDontSee('Hard (filter)')
            ->assertSee('Any nationality')->assertSee('Non-Malaysian')->assertDontSee('Singaporean');
    }
});

test('admin can create rules without obsolete type selectors', function () {
    $admin = makeAdmin();
    $scholarship = makeScholarship();
    $this->actingAs($admin)->post(route('admin.scholarships.rules.store', $scholarship), [
        'required_nationality' => 'Malaysian', 'required_study_level' => 'Undergraduate',
        'qualification_routes' => [
            ['education_level' => 'Diploma', 'study_status' => 'completed', 'min_cgpa' => 3.00],
            ['education_level' => 'Undergraduate', 'study_status' => 'studying', 'min_cgpa' => 3.50, 'min_year_of_study' => 1, 'max_year_of_study' => 2],
        ],
        'required_fields_of_study' => ['Computer Science', 'Engineering'], 'field_selection_submitted' => 1,
    ])->assertSessionHasNoErrors()->assertRedirect(route('admin.scholarships.rules.index', $scholarship));
    $rule = $scholarship->fresh()->rule;
    expect($rule->qualificationRoutes->pluck('education_level')->all())->toBe(['Diploma', 'Undergraduate']);
    expect($rule->qualificationRoutes->pluck('min_cgpa')->all())->toBe(['3.00', '3.50']);
    expect($rule->required_fields_of_study)->toBe(['Computer Science', 'Engineering']);
    expect(ScholarshipRule::where('scholarship_id', $scholarship->id)->count())->toBe(1);
});

test('admin rule edits invalidate cached decisions', function () {
    $user = makeStudent();
    $scholarship = makeScholarship();
    $service = app(ScholarshipRecommendationService::class);
    expect($service->getRecommendations($user)['recommendations'][0]['status'])->toBe('Eligible');
    $this->actingAs(makeAdmin())->put(route('admin.scholarships.rules.update', [$scholarship, $scholarship->rule]), [
        'qualification_routes' => [['education_level' => 'Undergraduate', 'study_status' => 'studying', 'min_cgpa' => 4]],
    ])->assertSessionHasNoErrors();
    expect($service->getRecommendations($user->fresh())['recommendations'][0]['status'])->toBe('Not Eligible');
});

test('admin can save either binary nationality or an unrestricted rule', function ($nationality) {
    $scholarship = makeScholarship();
    $this->actingAs(makeAdmin())->put(route('admin.scholarships.rules.update', [$scholarship, $scholarship->rule]), [
        'required_nationality' => $nationality,
    ])->assertSessionHasNoErrors();
    expect($scholarship->fresh()->rule->required_nationality)->toBe($nationality);
})->with(['Malaysian', 'Non-Malaysian', null]);

test('rule validation rejects inconsistent qualification and year settings', function ($payload, $field) {
    $scholarship = makeScholarship();
    $this->actingAs(makeAdmin())->post(route('admin.scholarships.rules.store', $scholarship), $payload)
        ->assertSessionHasErrors($field);
})->with([
    [['required_nationality' => 'Singaporean'], 'required_nationality'],
    [['required_fields_of_study' => ['Unlisted Field']], 'required_fields_of_study.0'],
    [['qualification_routes' => [['education_level' => 'Unknown', 'study_status' => 'completed']]], 'qualification_routes.0.education_level'],
    [['qualification_routes' => [['education_level' => 'SPM', 'study_status' => 'studying']]], 'qualification_routes.0.study_status'],
    [['qualification_routes' => [['education_level' => 'Diploma']]], 'qualification_routes.0.study_status'],
    [['qualification_routes' => [['education_level' => 'Diploma', 'study_status' => 'completed', 'min_cgpa' => 4.1]]], 'qualification_routes.0.min_cgpa'],
    [['qualification_routes' => [['education_level' => 'Undergraduate', 'study_status' => 'studying', 'min_year_of_study' => 3, 'max_year_of_study' => 2]]], 'qualification_routes.0.max_year_of_study'],
]);

test('clearing a field selection really removes the field requirement', function () {
    $scholarship = makeScholarship();
    $this->actingAs(makeAdmin())->put(route('admin.scholarships.rules.update', [$scholarship, $scholarship->rule]), [
        'field_selection_submitted' => 1,
    ])->assertSessionHasNoErrors();
    expect($scholarship->fresh()->rule->supportedFieldsOfStudy())->toBe([]);
});

test('rules belonging to another scholarship cannot be edited through a nested URL', function () {
    $first = makeScholarship(['name' => 'First']);
    $second = makeScholarship(['name' => 'Second']);
    $this->actingAs(makeAdmin())->put(route('admin.scholarships.rules.update', [$first, $second->rule]), [
        'qualification_routes' => [['education_level' => 'Undergraduate', 'study_status' => 'any', 'min_cgpa' => 0]],
    ])->assertNotFound();
    expect($second->fresh()->rule->min_cgpa)->toBe('3.00');
});

test('rule index shows configured requirements and retains access control', function () {
    $scholarship = makeScholarship();
    $this->actingAs(makeAdmin())->get(route('admin.scholarships.rules.index', $scholarship))
        ->assertOk()->assertSee('Scholarship Rules')->assertSee('Test Scholarship');
    $this->actingAs(makeStudent())->get(route('admin.scholarships.rules.index', $scholarship))->assertForbidden();
    auth()->logout();
    $this->get(route('admin.scholarships.rules.index', $scholarship))->assertRedirect(route('login'));
});
