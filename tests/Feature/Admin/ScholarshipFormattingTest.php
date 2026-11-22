<?php

use App\Support\ScholarshipText;

beforeEach(fn () => seedIncomeCategories());

function formattedScholarshipText(string $text): string
{
    return ScholarshipText::PREFIX.json_encode(['type' => 'doc', 'content' => [
        ['type' => 'bulletList', 'attrs' => ['listStyle' => 'dash'], 'content' => [
            ['type' => 'listItem', 'content' => [['type' => 'paragraph', 'content' => [
                ['type' => 'text', 'text' => $text, 'marks' => [['type' => 'bold']]],
            ]]]],
        ]],
    ]]);
}

test('formatted descriptions and special conditions save and display across student surfaces', function () {
    $student = makeStudent();
    $award = makeScholarship(['deadline' => null, 'deadline_type' => 'unconfirmed']);
    $this->actingAs(makeAdmin())->put(route('admin.scholarships.update', $award), [
        'name' => $award->name, 'provider' => $award->provider,
        'award_type' => $award->award_type, 'application_link' => $award->application_link, 'is_active' => true,
        'description' => formattedScholarshipText('Tuition support'),
        'special_requirements' => formattedScholarshipText('Portfolio required'),
    ])->assertSessionHasNoErrors()->assertRedirect();
    $award->refresh();
    expect(ScholarshipText::html($award->description))->toContain('<strong>Tuition support</strong>')
        ->and(ScholarshipText::html($award->special_requirements))->toContain('data-list-style="dash"');
    $this->actingAs($student)->get(route('student.recommendations'))->assertOk()
        ->assertSee('<strong>Tuition support</strong>', false)->assertSee('<strong>Portfolio required</strong>', false)
        ->assertSee('Application dates must be confirmed with the provider.')
        ->assertDontSee(ScholarshipText::PREFIX);
    $this->get(route('student.recommendations.show', $award))->assertOk()
        ->assertSee('<strong>Tuition support</strong>', false)->assertSee('<strong>Portfolio required</strong>', false);
});

test('blank formatting clears provider warnings and the visible special text limit is enforced', function () {
    $award = makeScholarship(['special_requirements' => 'Interview']);
    $payload = $award->only(['name', 'provider', 'award_type', 'application_link', 'is_active']);
    $this->actingAs(makeAdmin())->put(route('admin.scholarships.update', $award), array_merge($payload, [
        'special_requirements' => formattedScholarshipText(''),
    ]))->assertSessionHasNoErrors();
    expect($award->fresh()->special_requirements)->toBeNull();
    $this->put(route('admin.scholarships.update', $award), array_merge($payload, [
        'special_requirements' => formattedScholarshipText(str_repeat('a', 5001)),
    ]))->assertSessionHasErrors('special_requirements');
    expect($award->fresh()->special_requirements)->toBeNull();
});
