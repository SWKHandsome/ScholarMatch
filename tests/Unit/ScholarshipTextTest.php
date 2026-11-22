<?php

use App\Support\ScholarshipText;
use Illuminate\Validation\ValidationException;

test('plain scholarship text keeps literal punctuation, line breaks and safe display', function () {
    $text = "First line\n<b>literal text</b> & details";
    expect(ScholarshipText::forStorage($text, 'description'))->toBe($text)
        ->and(ScholarshipText::html($text))->toBe('<p>First line</p><p>&lt;b&gt;literal text&lt;/b&gt; &amp; details</p>')
        ->and(ScholarshipText::plain($text))->toBe($text);
});

test('formatted scholarship text supports bold and both list styles while discarding arbitrary attributes', function () {
    $value = ScholarshipText::PREFIX.json_encode(['type' => 'doc', 'content' => [
        ['type' => 'paragraph', 'attrs' => ['onclick' => 'unsafe()'], 'content' => [
            ['type' => 'text', 'text' => '<script>unsafe()</script>', 'marks' => [['type' => 'bold'], ['type' => 'link', 'attrs' => ['href' => 'javascript:unsafe()']]]],
        ]],
        ['type' => 'bulletList', 'attrs' => ['listStyle' => 'dash', 'style' => 'color:red'], 'content' => [
            ['type' => 'listItem', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Portfolio']]]]],
        ]],
        ['type' => 'bulletList', 'content' => [
            ['type' => 'listItem', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Interview']]]]],
        ]],
    ]]);
    $stored = ScholarshipText::forStorage($value, 'special_requirements');
    expect($stored)->not->toContain('onclick', 'javascript:', 'style')
        ->and(ScholarshipText::html($stored))->toContain('<strong>&lt;script&gt;unsafe()&lt;/script&gt;</strong>',
            '<ul data-list-style="dash"><li><p>Portfolio</p></li></ul>', '<ul><li><p>Interview</p></li></ul>')
        ->and(ScholarshipText::plain($stored))->toContain('Portfolio', 'Interview');
});

test('empty formatted content is stored as empty and added deadline guidance preserves the document', function () {
    $empty = ScholarshipText::PREFIX.json_encode(['type' => 'doc', 'content' => [['type' => 'paragraph']]]);
    expect(ScholarshipText::forStorage($empty, 'special_requirements'))->toBeNull();
    $rich = ScholarshipText::PREFIX.json_encode(['type' => 'doc', 'content' => [
        ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Interview', 'marks' => [['type' => 'bold']]]]],
    ]]);
    expect(ScholarshipText::html(ScholarshipText::appendParagraph($rich, 'Confirm dates.')))
        ->toBe('<p><strong>Interview</strong></p><p>Confirm dates.</p>');
});

test('both list node types render correctly and oversized text cannot overflow a MySQL TEXT column', function () {
    $value = ScholarshipText::PREFIX.json_encode(['type' => 'doc', 'content' => [
        ['type' => 'bulletList', 'content' => [['type' => 'listItem', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Bullet']]]]]]],
        ['type' => 'dashList', 'content' => [['type' => 'listItem', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Dash']]]]]]],
    ]]);
    expect(ScholarshipText::html(ScholarshipText::forStorage($value, 'description')))
        ->toBe('<ul><li><p>Bullet</p></li></ul><ul data-list-style="dash"><li><p>Dash</p></li></ul>');
    expect(fn () => ScholarshipText::forStorage(str_repeat('a', 65536), 'description'))
        ->toThrow(ValidationException::class);
});
