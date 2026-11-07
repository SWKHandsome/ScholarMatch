import test from 'node:test';
import assert from 'node:assert/strict';
import { incomeCategoryPreview } from '../../resources/js/income-category-preview.js';

const categories = [
    { name: 'B40', min: '0.00', max: '3169.00', range: 'RM 0.00 – RM 3,169.00/month' },
    { name: 'M40', min: '3170.00', max: '6339.00', range: 'RM 3,170.00 – RM 6,339.00/month' },
    { name: 'T20', min: '6340.00', max: null, range: 'RM 6,340.00/month and above' },
];

test('typing changes the category immediately using inclusive configured boundaries', () => {
    const preview = incomeCategoryPreview({ categories });
    for (const [income, name] of [['0', 'B40'], ['3169', 'B40'], ['3170', 'M40'], ['6339.00', 'M40'], ['6340', 'T20'], ['9999999999.99', 'T20']]) {
        preview.setIncome(income);
        assert.equal(preview.match?.name, name);
        assert.equal(preview.heading, `Your income category: ${name}`);
        assert.equal(preview.detail, `Current range: ${categories.find(category => category.name === name).range}`);
    }
});

test('admin supplied ranges and names replace every assumption about default cutoffs', () => {
    const preview = incomeCategoryPreview({ value: '4500', categories: [
        { name: 'Lower', min: '0.00', max: '3999.99', range: 'custom lower range' },
        { name: 'Middle', min: '4000.00', max: null, range: 'custom middle range' },
    ] });
    assert.equal(preview.match.name, 'Middle');
    assert.equal(preview.detail, 'Current range: custom middle range');
    preview.setIncome('3999.99');
    assert.equal(preview.match.name, 'Lower');
});

test('gaps never retain the previous category', () => {
    const preview = incomeCategoryPreview({ value: '3169', categories });
    assert.equal(preview.match.name, 'B40');
    preview.setIncome('3169.50');
    assert.equal(preview.match, null);
    assert.equal(preview.heading, 'Income category unavailable');
    assert.match(preview.detail, /No category matches/);
});

test('empty, malformed, negative, over-precision and browser-invalid values have appropriate feedback', () => {
    const preview = incomeCategoryPreview({ categories });
    assert.match(preview.detail, /Enter your monthly/);
    for (const value of ['-1', '3.333', '1e3', 'abc', '10000000000']) {
        preview.setIncome(value);
        assert.equal(preview.match, null);
        assert.equal(preview.heading, 'Check the income amount');
    }
    preview.setIncome('', true);
    assert.equal(preview.heading, 'Check the income amount');
    preview.setIncome('');
    assert.match(preview.detail, /Enter your monthly/);
});

test('missing configuration is explicit, and overlaps use the server-provided ordering', () => {
    assert.match(incomeCategoryPreview({ value: '0', categories: [] }).detail, /No income ranges are configured/);
    const preview = incomeCategoryPreview({ value: '3500', categories: [
        { name: 'First', min: '0', max: '4000', range: 'first range' },
        { name: 'Second', min: '3000', max: null, range: 'second range' },
    ] });
    assert.equal(preview.match.name, 'First');
});
