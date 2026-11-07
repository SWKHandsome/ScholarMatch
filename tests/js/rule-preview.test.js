import test from 'node:test';
import assert from 'node:assert/strict';
import { rulePreview } from '../../resources/js/rule-preview.js';

// Form ownership is exercised in the browser; these tests isolate request races and payload handling.
globalThis.FormData = class extends Map {
    append(key, value) { this.set(key, value); }
};

function editor() {
    const sample = new Map([['sample[cgpa]', '3.20']]);
    const form = new Map([['_token', 'csrf-token'], ['_method', 'PUT']]);
    form.addEventListener = () => {};
    form.removeEventListener = () => {};
    globalThis.document = { getElementById: () => sample };
    const view = rulePreview('/admin/rule-preview', {deadline_type: 'rolling'});
    view.$el = { closest: () => form, addEventListener() {}, removeEventListener() {} };
    view.$nextTick = async () => {};
    view.init();
    return view;
}

test('edit preview removes method spoofing while keeping CSRF and sample data', async () => {
    const view = editor();
    globalThis.fetch = async (url, options) => {
        assert.equal(url, '/admin/rule-preview');
        assert.equal(options.body.has('_method'), false);
        assert.equal(options.body.get('_token'), 'csrf-token');
        assert.equal(options.body.get('sample[cgpa]'), '3.20');
        assert.equal(options.body.get('deadline_type'), 'rolling');
        return {ok: true, json: async () => ({passed: true})};
    };
    await view.run();
    assert.equal(view.result.passed, true);
});

test('queued input updates before rendering finishes do not discard the requested preview', async () => {
    const view = editor();
    view.$nextTick = async () => view.invalidate();
    globalThis.fetch = async () => ({ok: true, json: async () => ({passed: true})});
    await view.run();
    assert.equal(view.result.passed, true);
});

test('edits made while a response is pending discard the stale response', async () => {
    const view = editor();
    let resolveResponse;
    let signalStarted;
    const started = new Promise(resolve => { signalStarted = resolve; });
    globalThis.fetch = () => { signalStarted(); return new Promise(resolve => { resolveResponse = resolve; }); };
    const pending = view.run();
    await started;
    view.invalidate();
    resolveResponse({ok: true, json: async () => ({passed: true})});
    await pending;
    assert.equal(view.result, null);
    assert.equal(view.busy, false);
});

test('validation failures are displayed without treating them as a passing result', async () => {
    const view = editor();
    globalThis.fetch = async () => ({ok: false, json: async () => ({errors: {grade_counts: ['Enter valid grades.']}})});
    await view.run();
    assert.deepEqual(view.errors, ['Enter valid grades.']);
    assert.equal(view.result, null);
    assert.equal(view.busy, false);
});
