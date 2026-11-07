<?php

use App\Models\RecommendationLog;
use App\Models\ScholarshipRule;
use App\Models\StudyField;
use App\Services\ScholarshipRecommendationService;
use Database\Seeders\ScholarshipCatalogSeeder;
use Database\Seeders\StudyFieldSeeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

beforeEach(fn () => seedIncomeCategories());

function fieldAcademicPayload(string $field): array
{
    return ['education_level' => 'Undergraduate', 'study_status' => 'studying', 'year_of_study' => 2,
        'cgpa' => 3.5, 'result_status' => 'official', 'funding_study_level' => 'Undergraduate',
        'institution_type' => 'Public University', 'field_of_study' => $field];
}

test('matching settings places study fields below income categories and retains the old page URL', function () {
    $this->actingAs(makeAdmin())->get(route('admin.matching-settings'))->assertOk()
        ->assertSee('Matching Settings')->assertSeeInOrder(['Income Categories', 'Fields of Study'])
        ->assertSee('+ Add Field')->assertSee('Computer Science');
    $this->get(route('admin.income-categories.index'))->assertOk()->assertSee('Matching Settings');
});

test('new study fields appear across academic scholarship preview and filter forms', function () {
    $this->actingAs(makeAdmin())->post(route('admin.study-fields.store'), ['name' => 'Artificial Intelligence', 'is_active' => 1])
        ->assertSessionHasNoErrors()->assertRedirect(route('admin.matching-settings').'#fields-of-study');
    $this->get(route('admin.scholarships.create'))->assertOk()->assertSee('Artificial Intelligence');
    $student = makeStudent();
    makeScholarship();
    $this->actingAs($student)->get(route('student.academic-result.edit'))->assertOk()->assertSee('Artificial Intelligence');
    $this->get(route('student.recommendations'))->assertOk()->assertSee('Artificial Intelligence');
    $this->patch(route('student.academic-result.update'), fieldAcademicPayload('Artificial Intelligence'))->assertSessionHasNoErrors();
    expect($student->fresh()->academicResult->field_of_study)->toBe('Artificial Intelligence');
});

test('renaming a study field preserves matching and updates linked records without rewriting logs', function () {
    $student = makeStudent();
    DB::table('student_profiles')->where('user_id', $student->id)->update(['field_of_study' => 'Engineering']);
    $legacy = makeScholarship();
    $multi = makeScholarship(['name' => 'Multiple fields'], ['required_fields_of_study' => ['Engineering', 'Business']]);
    $log = RecommendationLog::create(['user_id' => $student->id, 'scholarship_id' => $legacy->id, 'status' => 'Eligible', 'explanation' => ['field' => 'Engineering']]);
    $service = app(ScholarshipRecommendationService::class);
    expect(collect($service->getRecommendations($student)['recommendations'])->where('status', 'Eligible')->count())->toBe(2);
    $version = Cache::get('recommendation_catalog_version');
    $field = StudyField::where('name', 'Engineering')->firstOrFail();
    $this->actingAs(makeAdmin())->patch(route('admin.study-fields.update', $field), ['name' => 'Engineering & Technology', 'is_active' => 1])->assertSessionHasNoErrors();
    $student = $student->fresh();
    expect($student->academicResult->field_of_study)->toBe('Engineering & Technology')
        ->and($student->studentProfile->field_of_study)->toBe('Engineering & Technology')
        ->and($legacy->fresh()->rule->required_field_of_study)->toBe('Engineering & Technology')
        ->and($multi->fresh()->rule->required_fields_of_study)->toBe(['Engineering & Technology', 'Business'])
        ->and($field->fresh()->seed_name)->toBe('Engineering')
        ->and($log->fresh()->explanation)->toBe(['field' => 'Engineering'])
        ->and(Cache::get('recommendation_catalog_version'))->not->toBe($version);
    expect(collect($service->getRecommendations($student)['recommendations'])->where('status', 'Eligible')->count())->toBe(2);
});

test('inactive fields remain valid for existing students but cannot be newly assigned', function () {
    $existing = makeStudent();
    $other = makeStudent([], ['field_of_study' => 'Business']);
    $field = StudyField::where('name', 'Engineering')->firstOrFail();
    $this->actingAs(makeAdmin())->patch(route('admin.study-fields.update', $field), ['name' => 'Engineering', 'is_active' => 0])->assertSessionHasNoErrors();
    expect($existing->fresh()->academicResult->isReadyForRecommendations())->toBeTrue();
    $this->actingAs($existing)->get(route('student.academic-result.edit'))->assertOk()->assertSee('Engineering (Inactive)');
    $this->patch(route('student.academic-result.update'), fieldAcademicPayload('Engineering'))->assertSessionHasNoErrors();
    $this->actingAs($other)->get(route('student.academic-result.edit'))->assertOk()->assertDontSee('value="Engineering"', false);
    $this->patch(route('student.academic-result.update'), fieldAcademicPayload('Engineering'))->assertSessionHasErrors('field_of_study');
    expect($other->fresh()->academicResult->field_of_study)->toBe('Business');
    $this->get(route('student.recommendations', ['field_of_study' => 'Engineering']))->assertOk();
});

test('existing scholarship restrictions can retain inactive fields but new restrictions cannot select them', function () {
    $existing = makeScholarship();
    $new = makeScholarship(['name' => 'New restriction']);
    $new->rule->delete();
    StudyField::where('name', 'Engineering')->update(['is_active' => false]);
    $payload = ['qualification_routes_submitted' => 1, 'required_fields_of_study' => ['Engineering']];
    $this->actingAs(makeAdmin())->post(route('admin.scholarships.rules.store', $existing), $payload)->assertSessionHasNoErrors();
    $this->post(route('admin.scholarships.rules.store', $new), $payload)->assertSessionHasErrors('required_fields_of_study.0');
    expect($existing->fresh()->rule->supportedFieldsOfStudy())->toBe(['Engineering']);
});

test('field edits reject duplicate empty overlong and malformed labels', function ($name) {
    $this->actingAs(makeAdmin())->post(route('admin.study-fields.store'), ['name' => $name, 'is_active' => 1])
        ->assertSessionHasErrors('name', null, 'studyField');
    expect(StudyField::count())->toBe(21);
})->with(['Engineering', 'engineering', '', str_repeat('x', 256), [['bad']]]);

test('field settings are administrator-only and have no destructive delete endpoint', function () {
    $field = StudyField::firstOrFail();
    $this->actingAs(makeStudent())->get(route('admin.matching-settings'))->assertForbidden();
    $this->post(route('admin.study-fields.store'), ['name' => 'Forbidden', 'is_active' => 1])->assertForbidden();
    $this->patch(route('admin.study-fields.update', $field), ['name' => 'Forbidden', 'is_active' => 0])->assertForbidden();
    $this->actingAs(makeAdmin())->delete(route('admin.study-fields.update', $field))->assertStatus(405);
});

test('repeated seeding preserves renamed inactive fields and maps bundled demo restrictions to them', function () {
    $this->seed(ScholarshipCatalogSeeder::class);
    $field = StudyField::where('name', 'Engineering')->firstOrFail();
    $this->actingAs(makeAdmin())->patch(route('admin.study-fields.update', $field), ['name' => 'Engineering & Technology', 'is_active' => 0])->assertSessionHasNoErrors();
    $this->seed(StudyFieldSeeder::class);
    $this->seed(ScholarshipCatalogSeeder::class);
    expect(StudyField::count())->toBe(21)->and(StudyField::where('name', 'Engineering')->exists())->toBeFalse()
        ->and($field->fresh()->is_active)->toBeFalse();
    $fields = ScholarshipRule::all()->flatMap->supportedFieldsOfStudy();
    expect($fields)->toContain('Engineering & Technology')->not->toContain('Engineering');
});
