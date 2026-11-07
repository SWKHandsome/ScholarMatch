<?php

use App\Models\RecommendationLog;

beforeEach(fn () => seedIncomeCategories());

test('recommendations paginate ten cards in deadline order and keep complete evaluation logs', function () {
    $user = makeStudent();
    for ($number = 12; $number >= 1; $number--) {
        makeScholarship([
            'name' => sprintf('Award %02d', $number),
            'deadline' => today()->addDays($number)->toDateString(),
            'special_requirements' => $number === 1 ? 'Interview required.' : null,
        ]);
    }
    makeScholarship(['name' => 'Higher CGPA Award'], ['min_cgpa' => 3.9]);

    $this->actingAs($user)->get(route('student.recommendations'))->assertOk()
        ->assertViewHas('recommendations', fn ($items) => $items->count() === 10 && $items->total() === 12
            && $items->first()['scholarship_data']['name'] === 'Award 01'
            && $items->last()['scholarship_data']['name'] === 'Award 10')
        ->assertViewHas('decisionCounts', fn ($counts) => $counts->get('Eligible') === 12 && $counts->get('Not Eligible') === 1)
        ->assertSee('Interview required.')->assertDontSee('Award 11')->assertDontSee('Higher CGPA Award');
    expect(RecommendationLog::count())->toBe(13);

    $this->get(route('student.recommendations', ['page' => 2]))->assertOk()
        ->assertViewHas('recommendations', fn ($items) => $items->count() === 2 && $items->currentPage() === 2)
        ->assertSee('Award 11')->assertSee('Award 12')->assertDontSee('Award 01');
    expect(RecommendationLog::count())->toBe(13);
});

test('search matches names and providers without case sensitivity and combines with award type', function () {
    $user = makeStudent();
    makeScholarship(['name' => 'Engineering Grant', 'provider' => 'Future Trust', 'award_type' => 'Full Scholarship']);
    makeScholarship(['name' => 'Future Leaders Award', 'provider' => 'Another Foundation', 'award_type' => 'Full Scholarship']);
    makeScholarship(['name' => 'Partial Grant', 'provider' => 'Future Trust', 'award_type' => 'Partial Scholarship']);
    makeScholarship(['name' => 'Other Grant', 'provider' => 'Other Foundation']);

    $this->actingAs($user)->get(route('student.recommendations', ['search' => '  FUTURE  ', 'award_type' => 'Full Scholarship']))
        ->assertOk()->assertSee('Engineering Grant')->assertSee('Future Leaders Award')
        ->assertDontSee('Partial Grant')->assertDontSee('Other Grant')
        ->assertViewHas('recommendations', fn ($items) => $items->total() === 2)
        ->assertViewHas('filters', fn ($filters) => $filters['search'] === 'FUTURE');
});

test('programme filters include unrestricted scholarships and supported field lists', function (string $filter, string $value, array $otherRule) {
    $user = makeStudent();
    makeScholarship(['name' => 'Specific Award'], ['required_fields_of_study' => ['Science', 'Engineering']]);
    makeScholarship(['name' => 'Open Programme Award'], [
        'required_study_level' => null, 'required_fields_of_study' => null,
        'required_field_of_study' => null, 'required_institution_type' => null,
    ]);
    makeScholarship(['name' => 'Different Programme Award'], $otherRule);

    $this->actingAs($user)->get(route('student.recommendations', [$filter => $value]))->assertOk()
        ->assertSee('Specific Award')->assertSee('Open Programme Award')->assertDontSee('Different Programme Award')
        ->assertViewHas('decisionCounts', fn ($counts) => $counts->get('Eligible') === 2 && $counts->get('Not Eligible', 0) === 0);
})->with([
    'funded level' => ['study_level', 'Undergraduate', ['required_study_level' => 'Diploma']],
    'multiple supported fields' => ['field_of_study', 'Engineering', ['required_fields_of_study' => ['Business', 'Science']]],
    'institution' => ['institution_type', 'Public University', ['required_institution_type' => 'Private University']],
]);

test('all programme filters combine without changing the student eligibility decision', function () {
    $user = makeStudent();
    makeScholarship(['name' => 'Target Programme Award'], [
        'required_study_level' => 'Diploma', 'required_field_of_study' => 'Business',
        'required_institution_type' => 'Private University',
    ]);
    makeScholarship(['name' => 'Different Institution Award'], [
        'required_study_level' => 'Diploma', 'required_field_of_study' => 'Business',
    ]);
    $query = ['study_level' => 'Diploma', 'field_of_study' => 'Business', 'institution_type' => 'Private University'];
    $this->actingAs($user)->get(route('student.recommendations', $query))->assertOk()
        ->assertSee('No eligible scholarships match these filters.')
        ->assertViewHas('decisionCounts', fn ($counts) => $counts->get('Not Eligible') === 1);
    $this->get(route('student.recommendations', $query + ['decision' => 'Not Eligible']))->assertOk()
        ->assertSee('Target Programme Award')->assertDontSee('Different Institution Award')->assertDontSee('Apply Now');
});

test('pagination and eligibility group links preserve filters and group changes reset page', function () {
    $user = makeStudent();
    for ($number = 1; $number <= 11; $number++) {
        makeScholarship(['name' => 'Future Award '.$number, 'award_type' => 'Tuition Fee Waiver']);
    }
    $query = ['search' => 'Future', 'award_type' => 'Tuition Fee Waiver', 'study_level' => 'Undergraduate',
        'field_of_study' => 'Engineering', 'institution_type' => 'Public University'];
    $response = $this->actingAs($user)->get(route('student.recommendations', $query + ['page' => 2]));
    $response->assertOk()->assertViewHas('awardTypes', fn ($types) => $types->contains('Tuition Fee Waiver'));
    $paginator = $response->viewData('recommendations');
    parse_str(parse_url($paginator->url(1), PHP_URL_QUERY), $pageQuery);
    expect($pageQuery)->toMatchArray($query + ['decision' => 'Eligible', 'page' => '1']);
    $response->assertSee(route('student.recommendations', $query + ['decision' => 'Not Eligible']))
        ->assertSee(route('student.recommendations', ['decision' => 'Eligible']), false);
});

test('not eligible results have their own ten item pages and out of range pages remain useful', function () {
    $user = makeStudent();
    for ($number = 1; $number <= 11; $number++) {
        makeScholarship(['name' => sprintf('Higher Result Award %02d', $number)], ['min_cgpa' => 3.9]);
    }
    $this->actingAs($user)->get(route('student.recommendations', ['decision' => 'Not Eligible']))->assertOk()
        ->assertViewHas('recommendations', fn ($items) => $items->count() === 10 && $items->total() === 11)
        ->assertDontSee('Apply Now');
    $this->get(route('student.recommendations', ['decision' => 'Not Eligible', 'page' => 99]))->assertOk()
        ->assertViewHas('recommendations', fn ($items) => $items->currentPage() === 2 && $items->count() === 1)
        ->assertSee('Higher Result Award 11');
});

test('empty filtered lists retain filters and a reset action', function () {
    $user = makeStudent();
    makeScholarship();
    $this->actingAs($user)->get(route('student.recommendations', ['search' => 'No matching provider', 'award_type' => 'Living Allowance']))->assertOk()
        ->assertSee('No eligible scholarships match these filters.')->assertSee('Apply Filters')->assertSee('Reset')
        ->assertViewHas('recommendations', fn ($items) => $items->total() === 0 && $items->currentPage() === 1)
        ->assertViewHas('awardTypes', fn ($types) => $types->contains('Living Allowance'));
});

test('unsupported or malformed filter values are rejected', function (array $query, string $field) {
    $this->actingAs(makeStudent())->get(route('student.recommendations', $query))->assertSessionHasErrors($field);
})->with([
    'unsupported level' => [['study_level' => 'Imaginary'], 'study_level'],
    'unsupported field' => [['field_of_study' => 'Imaginary'], 'field_of_study'],
    'unsupported institution' => [['institution_type' => 'Imaginary'], 'institution_type'],
    'unsupported decision' => [['decision' => 'Pending'], 'decision'],
    'search array' => [['search' => ['invalid']], 'search'],
    'invalid page' => [['page' => -1], 'page'],
]);
