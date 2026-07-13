<?php

use App\Models\IncomeCategory;
use App\Models\Scholarship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    seedIncomeCategories();
});

test('admin can view scholarships index', function () {
    $admin = makeAdmin();
    Scholarship::factory()->count(3)->create(['is_active' => true]);

    $response = $this->actingAs($admin)->get(route('admin.scholarships.index'));

    $response->assertOk();
    $response->assertSee('Scholarships');
    $response->assertSee('Add Scholarship');
});

test('admin can view create scholarship page', function () {
    $admin = makeAdmin();

    $response = $this->actingAs($admin)->get(route('admin.scholarships.create'));

    $response->assertOk();
    $response->assertSee('Add Scholarship');
    $response->assertSee('Scholarship Name');
    $response->assertSee('Provider');
    $response->assertSee('Description');
    $response->assertSee('Award Type');
    $response->assertSee('Deadline');
    $response->assertSee('Application Link');
});

test('admin can create scholarship', function () {
    $admin = makeAdmin();

    $response = $this->actingAs($admin)->post(route('admin.scholarships.store'), [
        'name' => 'New Scholarship',
        'provider' => 'New Provider',
        'description' => 'New description',
        'award_type' => 'Partial Scholarship',
        'deadline' => now()->addMonths(2)->toDateString(),
        'application_link' => 'https://example.com/apply',
        'is_active' => true,
    ]);

    $response->assertRedirect(route('admin.scholarships.index'));
    $response->assertSessionHas('success');

    expect(Scholarship::where('name', 'New Scholarship')->exists())->toBeTrue();
});

test('admin can view edit scholarship page', function () {
    $admin = makeAdmin();
    $scholarship = Scholarship::factory()->create(['name' => 'Edit Test']);

    $response = $this->actingAs($admin)->get(route('admin.scholarships.edit', $scholarship));

    $response->assertOk();
    $response->assertSee('Edit Test');
});

test('admin can update scholarship', function () {
    $admin = makeAdmin();
    $scholarship = Scholarship::factory()->create(['name' => 'Old Name']);

    $response = $this->actingAs($admin)->put(route('admin.scholarships.update', $scholarship), [
        'name' => 'Updated Name',
        'provider' => 'Updated Provider',
        'description' => 'Updated description',
        'award_type' => 'Full Scholarship',
        'deadline' => now()->addMonths(3)->toDateString(),
        'application_link' => 'https://example.com/updated',
        'is_active' => true,
    ]);

    $response->assertRedirect(route('admin.scholarships.index'));
    $response->assertSessionHas('success');

    $scholarship->refresh();
    expect($scholarship->name)->toBe('Updated Name');
    expect($scholarship->provider)->toBe('Updated Provider');
});

test('admin can delete scholarship', function () {
    $admin = makeAdmin();
    $scholarship = Scholarship::factory()->create(['name' => 'To Delete']);

    $response = $this->actingAs($admin)->delete(route('admin.scholarships.destroy', $scholarship));

    $response->assertRedirect(route('admin.scholarships.index'));
    $response->assertSessionHas('success');

    expect(Scholarship::where('id', $scholarship->id)->exists())->toBeFalse();
});

test('scholarship validation requires all fields', function () {
    $admin = makeAdmin();

    $response = $this->actingAs($admin)->post(route('admin.scholarships.store'), [
        'name' => '',
        'provider' => '',
        'award_type' => '',
        'deadline' => '',
        'application_link' => '',
    ]);

    $response->assertSessionHasErrors([
        'name',
        'provider',
        'award_type',
        'deadline',
        'application_link',
    ]);
});

test('scholarship validation requires valid URL', function () {
    $admin = makeAdmin();

    $response = $this->actingAs($admin)->post(route('admin.scholarships.store'), [
        'name' => 'Test',
        'provider' => 'Test',
        'award_type' => 'Full Scholarship',
        'deadline' => now()->addMonth()->toDateString(),
        'application_link' => 'not-a-url',
    ]);

    $response->assertSessionHasErrors('application_link');
});

test('scholarships index requires admin role', function () {
    $student = User::factory()->create(['role' => User::ROLE_STUDENT]);

    $response = $this->actingAs($student)->get(route('admin.scholarships.index'));

    $response->assertForbidden();
});

test('scholarships redirects guest to login', function () {
    $response = $this->get(route('admin.scholarships.index'));

    $response->assertRedirect(route('login'));
});