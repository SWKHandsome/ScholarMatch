import test from 'node:test';
import assert from 'node:assert/strict';
import { academicResultEditor, qualificationEditor } from '../../resources/js/academic-qualifications.js';

const config = {
    categories: { secondary: { qualifications: ['SPM', 'IGCSE'] }, pathway: { qualifications: ['STPM', 'A-Level', 'UEC', 'STAM', 'Diploma'] }, undergraduate: { qualifications: ['Undergraduate'] } },
    qualificationScales: { SPM: ['spm'], IGCSE: ['igcse_letters', 'igcse_9_1'], 'A-Level': ['a_level'], UEC: ['uec'], STPM: ['stpm'] },
    cgpaLevels: ['STPM', 'Diploma', 'Undergraduate'],
    labels: { STPM: 'STPM', UEC: 'Senior UEC', 'A-Level': 'A-Level' },
    stpmPoints: { A: 4, 'A-': 3.67, 'B+': 3.33, B: 3, 'B-': 2.67, 'C+': 2.33, C: 2, 'C-': 1.67, 'D+': 1.33, D: 1, F: 0 },
    scales: {
        spm: { grades: ['A+', 'A', 'A-', 'B+', 'B', 'C+', 'C', 'D', 'E', 'G'], max_subjects: 12 },
        igcse_letters: { grades: ['A*', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'U'], max_subjects: 20 },
        igcse_9_1: { grades: ['9', '8', '7', '6', '5', '4', '3', '2', '1', 'U'], max_subjects: 20 },
        a_level: { grades: ['A*', 'A', 'B', 'C', 'D', 'E', 'U'], max_subjects: 20 },
        uec: { grades: ['A1', 'A2', 'B3', 'B4', 'B5', 'B6', 'C7', 'C8', 'F9'], max_subjects: 20 },
        stpm: { grades: ['A', 'A-', 'B+', 'B', 'B-', 'C+', 'C', 'C-', 'D+', 'D', 'F'], max_subjects: 5 },
    },
};

test('counters update summaries and inclusive SPM totals with keyboard-entered counts', () => {
    const form = academicResultEditor(config, { level: 'SPM', counts: { 'A+': 2, A: 1, 'A-': 1, B: 2, C: 1, G: 1 } });
    form.init();
    assert.equal(form.totalSubjects, 8);
    assert.equal(form.countAtOrAbove('A-'), 4);
    assert.equal(form.countAtOrAbove('C'), 7);
    assert.match(form.summary, /2 × A\+/);
    form.adjust('A+', -1);
    assert.equal(form.countAtOrAbove('A-'), 3);
    form.counts.B = 3;
    assert.equal(form.validCounts, true);
});

test('counters stop at zero and the application limit while invalid direct entry cannot pass', () => {
    const form = academicResultEditor(config, { level: 'SPM' });
    form.init();
    form.adjust('A', -1);
    assert.equal(form.counts.A, 0);
    assert.equal(form.validCounts, false);
    form.counts.A = 12;
    form.adjust('G', 1);
    assert.equal(form.counts.G, 0);
    form.counts.G = 1;
    assert.equal(form.validCounts, false);
    form.counts.A = 1.5;
    assert.equal(form.validCounts, false);
    form.counts.A = -1;
    assert.equal(form.validCounts, false);
});

test('category and qualification changes clear incompatible results and official confirmation', () => {
    const form = academicResultEditor(config, { level: 'SPM', counts: { A: 8 }, official: true });
    form.init();
    form.selectCategory('pathway');
    assert.equal(form.level, '');
    assert.equal(form.official, false);
    assert.deepEqual(form.counts, {});
    form.selectQualification('UEC');
    assert.equal(form.scale, 'uec');
    assert.equal(form.studyStatus, 'completed');
    assert.equal(form.totalSubjects, 0);
    form.counts.A1 = 5;
    form.selectQualification('Diploma');
    assert.equal(form.cgpaBased, true);
    assert.equal(form.scale, '');
    assert.deepEqual(form.counts, {});
});

test('IGCSE requires an explicit scale and changing scales resets every count', () => {
    const form = academicResultEditor(config, { level: 'IGCSE' });
    form.init();
    assert.equal(form.scale, '');
    assert.equal(form.validCounts, false);
    form.scale = 'igcse_9_1';
    form.scaleChanged();
    form.adjust('9', 1);
    form.adjust('U', 1);
    assert.equal(form.countAtOrAbove('6'), 1);
    form.scale = 'igcse_letters';
    form.scaleChanged();
    assert.equal(form.totalSubjects, 0);
    assert.equal(form.counts.U, 0);
    assert.equal(form.counts['9'], undefined);
});

test('grouped grade rows retain stable identities when the first row or another route is removed', () => {
    const editor = qualificationEditor([], config);
    editor.$nextTick = () => {};
    editor.init();
    editor.addRoute();
    editor.addRoute();
    const route = editor.routes[1];
    route.education_level = 'A-Level';
    editor.levelChanged(route);
    const condition = route.requirements[0];
    editor.addGradeRow(condition);
    const row = condition.rows[1];
    row.grade = 'B';
    row.value = 3;
    editor.removeGradeRow(route, condition, 0);
    editor.routes.splice(0, 1);
    assert.equal(editor.routes[0]._key, route._key);
    assert.equal(editor.routes[0].requirements[0]._key, condition._key);
    assert.equal(editor.routes[0].requirements[0].rows[0]._key, row._key);
    assert.equal(editor.routes[0].requirements[0].rows[0].value, 3);
});

test('admin qualification changes clear grade conditions, average rules and stale year/result fields', () => {
    const editor = qualificationEditor([], config);
    const route = editor.makeRoute({ education_level: 'UEC', study_status: 'completed', grade_requirements: [{ grade: 'A1', min_subjects: 3 }], uec_average: { subjects: 5, max_average: 3, minimum_grade: 'B6' } });
    route.education_level = 'Diploma';
    editor.levelChanged(route);
    assert.deepEqual(route.requirements.map(condition => condition.type), ['min_cgpa']);
    assert.equal(editor.conditionStarted(route.requirements[0]), false);
    assert.equal(route.grading_scale, '');
    route.min_year_of_study = 2;
    route.requirements[0].value = 3;
    route.education_level = 'SPM';
    editor.levelChanged(route);
    assert.equal(route.study_status, 'completed');
    assert.equal(route.min_year_of_study, '');
    assert.deepEqual(route.requirements.map(condition => condition.type), ['grade']);
    assert.equal(editor.conditionStarted(route.requirements[0]), false);
});

test('STPM retains independent official CGPA and grade counts and clears both when status changes', () => {
    const form = academicResultEditor(config, { level: 'STPM', studyStatus: 'studying', year: 2, cgpa: '3.00', counts: { A: 1, B: 2, C: 1 }, official: true });
    form.init();
    assert.equal(form.cgpaBased, true);
    assert.equal(form.gradeBased, true);
    assert.equal(form.totalSubjects, 4);
    assert.equal(form.cgpa, '3.00');
    form.statusChanged();
    assert.equal(form.totalSubjects, 0);
    assert.equal(form.cgpa, '');
    assert.equal(form.official, false);
});

test('saved mixed conditions restore their exact values and stable nested submission names', () => {
    const editor = qualificationEditor([], config);
    const route = editor.makeRoute({ education_level: 'STPM', study_status: 'completed', min_cgpa: 0,
        grade_requirements: [{ grade: 'A-', min_subjects: 1 }], stpm_average: { subjects: 3, minimum_grade: 'F', min_average: 3 } });
    assert.deepEqual(route.requirements.map(item => item.type), ['min_cgpa', 'grade', 'stpm_average']);
    assert.equal(route.requirements[0].value, 0);
    assert.equal(editor.gradeRowName(2, 0, 'grade'), 'qualification_routes[2][grade_requirements][0][grade]');
    assert.equal(route.requirements[1].rows[0].grade, 'A-');
    assert.equal(editor.conditionName(2, 2, route.requirements[2]), 'qualification_routes[2][stpm_average][min_average]');
    assert.equal(editor.conditionName(2, 2, route.requirements[2], 'grade'), 'qualification_routes[2][stpm_average][minimum_grade]');
    assert.equal(editor.availableTypes(route).some(item => item.value === 'min_cgpa'), false);
    assert.equal(editor.availableTypes(route, route.requirements[0]).some(item => item.value === 'min_cgpa'), true);
});

test('requirement types are qualification-specific and changing type clears incompatible settings', () => {
    const editor = qualificationEditor([], config);
    const route = editor.makeRoute({ education_level: 'UEC' });
    const condition = editor.makeCondition('uec_total', { subjects: 5, value: 11, grade: 'B6' });
    route.requirements.push(condition);
    assert.match(editor.describe(route, condition), /total points must be no more than 11/);
    assert.equal(editor.conditionName(0, 0, condition), 'qualification_routes[0][uec_total][max_total]');
    const key = condition._key;
    condition.type = 'grade';
    editor.conditionChanged(condition);
    assert.equal(condition._key, key);
    assert.equal(condition.value, '');
    assert.equal(condition.grade, '');
    assert.equal(editor.types(editor.makeRoute({ education_level: 'A-Level' })).some(item => item.value === 'min_cgpa'), false);
    assert.deepEqual(editor.types(editor.makeRoute({ education_level: 'STPM' })).map(item => item.value), ['min_cgpa', 'grade', 'stpm_average']);
});

test('grade pattern preview expresses ABB correctly and scale changes clear old grade thresholds', () => {
    const editor = qualificationEditor([], config);
    const route = editor.makeRoute({ education_level: 'A-Level', grade_requirements: [{ grade: 'A', min_subjects: 1 }, { grade: 'B', min_subjects: 3 }] });
    assert.equal(editor.gradePattern(route), 'A, B, B');
    route.requirements[0].rows[1].value = 2;
    assert.equal(editor.gradePattern(route), 'A, B');
    const igcse = editor.makeRoute({ education_level: 'IGCSE', grading_scale: 'igcse_letters', grade_requirements: [{ grade: 'C', min_subjects: 5 }] });
    igcse.grading_scale = 'igcse_9_1';
    editor.scaleChanged(igcse);
    assert.deepEqual(igcse.requirements.map(condition => condition.type), ['grade']);
    assert.equal(editor.conditionStarted(igcse.requirements[0]), false);
    igcse.grading_scale = '';
    editor.scaleChanged(igcse);
    assert.deepEqual(igcse.requirements, []);
});

test('legacy grade thresholds form one shared requirement while separate types retain their settings', () => {
    const editor = qualificationEditor([], config);
    const route = editor.makeRoute({ education_level: 'STPM', min_cgpa: 3,
        grade_requirements: [{ grade: 'A', min_subjects: 1 }, { grade: 'B', min_subjects: 3 }],
        stpm_average: { subjects: 3, minimum_grade: 'F', min_average: 3 } });
    assert.deepEqual(route.requirements.map(item => item.type), ['min_cgpa', 'grade', 'stpm_average']);
    const group = route.requirements[1];
    assert.deepEqual(group.rows.map(row => ({grade: row.grade, count: row.value})), [{grade:'A', count:1}, {grade:'B', count:3}]);
    assert.equal(editor.availableTypes(route).some(type => type.value === 'grade'), false);
    assert.equal(editor.availableTypes(route, group).some(type => type.value === 'grade'), true);
    assert.match(editor.describe(route, group), /A, B, B/);
    assert.equal(editor.gradeRowName(0, 1, 'value'), 'qualification_routes[0][grade_requirements][1][min_subjects]');
    route.requirements.splice(1, 1);
    assert.equal(editor.availableTypes(route).some(type => type.value === 'grade'), true);
    assert.equal(route.requirements[0].value, 3);
    assert.equal(route.requirements[1].value, 3);
});

test('switching requirement type discards the entire old grade group', () => {
    const editor = qualificationEditor([], config);
    const route = editor.makeRoute({ education_level: 'UEC', grade_requirements: [{ grade: 'A1', min_subjects: 2 }, { grade: 'B6', min_subjects: 5 }] });
    const group = route.requirements[0];
    editor.removeGradeRow(route, group, 0);
    assert.equal(group.rows.length, 1);
    assert.equal(group.rows[0].grade, 'B6');
    const key = group._key;
    group.type = 'uec_total';
    editor.conditionChanged(group);
    assert.equal(group._key, key);
    assert.deepEqual(group.rows, []);
    assert.equal(group.value, '');
    group.type = 'grade';
    editor.conditionChanged(group);
    assert.equal(group.rows.length, 1);
    assert.equal(group.rows[0].grade, '');
    assert.equal(group.rows[0].value, '');
});

test('removing the final grade row removes only its group and makes the type available again', () => {
    const editor = qualificationEditor([], config);
    const route = editor.makeRoute({ education_level: 'STPM', min_cgpa: 3, grade_requirements: [{ grade: 'A', min_subjects: 1 }] });
    const group = route.requirements.find(item => item.type === 'grade');
    editor.removeGradeRow(route, group, 0);
    assert.deepEqual(route.requirements.map(item => item.type), ['min_cgpa']);
    assert.equal(route.requirements[0].value, 3);
    assert.equal(editor.availableTypes(route).some(item => item.value === 'grade'), true);
    editor.addRequirement(route);
    assert.equal(route.requirements[1].type, 'grade');
    assert.equal(route.requirements[1].rows.length, 1);
});

test('combined grade preview ignores untouched rows and waits for started rows to be completed', () => {
    const editor = qualificationEditor([], config);
    const route = editor.makeRoute({ education_level: 'A-Level', grade_requirements: [{ grade: 'A', min_subjects: 1 }] });
    const group = route.requirements[0];
    editor.addGradeRow(group);
    assert.equal(editor.gradePattern(route), 'A');
    group.rows[1].value = 0;
    assert.equal(editor.gradePattern(route), '');
    assert.match(editor.describe(route, group), /Complete each started grade row/);
    for (let i = 0; i < 30; i++) editor.addGradeRow(group);
    assert.equal(group.rows.length, 20);
    const zero = editor.makeRoute({ education_level: 'SPM', grade_requirements: [{ grade: 'A', min_subjects: 0 }] });
    assert.match(editor.describe(zero, zero.requirements[0]), /All grade minima are zero/);
});

test('completed STPM preview includes Pengajian Am once and rounds the best-four result consistently', () => {
    for (const [counts, paGrade, expected] of [
        [{ A: 4, C: 1 }, 'C', '3.50'], [{ A: 4, C: 1 }, 'A', '4.00'],
        [{ 'B+': 1, B: 1, 'A-': 1, A: 1 }, 'B+', '3.50'],
        [{ A: 2, 'A-': 2 }, 'A', '3.84'], [{ F: 4 }, 'F', '0.00'],
    ]) {
        const form = academicResultEditor(config, { level: 'STPM', studyStatus: 'completed', counts, paGrade });
        form.init();
        assert.equal(form.calculatedStpmCgpa, expected);
    }
});

test('STPM preview cannot calculate missing or inconsistent Pengajian Am and incomplete final results', () => {
    const form = academicResultEditor(config, { level: 'STPM', studyStatus: 'completed', counts: { A: 4, C: 1 } });
    form.init();
    assert.equal(form.calculatedStpmCgpa, '');
    form.paGrade = 'B';
    assert.equal(form.calculatedStpmCgpa, '');
    form.paGrade = 'C';
    form.counts.A = 2;
    assert.equal(form.calculatedStpmCgpa, '');
    form.counts.A = 5;
    assert.equal(form.calculatedStpmCgpa, '');
});

test('editing STPM counts or status clears calculation confirmation and stale Pengajian Am data', () => {
    const form = academicResultEditor(config, { level: 'STPM', studyStatus: 'completed', counts: { A: 3, C: 1 }, paGrade: 'C', cgpaMatchesOfficial: true });
    form.init();
    assert.equal(form.cgpaMatchesOfficial, true);
    form.adjust('A', 1);
    assert.equal(form.cgpaMatchesOfficial, false);
    form.cgpaMatchesOfficial = true;
    form.studyStatus = 'studying';
    form.statusChanged();
    assert.equal(form.autoStpmCgpa, false);
    assert.equal(form.calculatedStpmCgpa, '');
    assert.equal(form.paGrade, '');
    assert.equal(form.cgpaMatchesOfficial, false);
    form.cgpa = '3.20';
    assert.equal(form.cgpa, '3.20');
});

test('changing qualification clears Pengajian Am while an unchanged saved calculation retains confirmation', () => {
    const form = academicResultEditor(config, { level: 'STPM', studyStatus: 'completed', counts: { A: 4, C: 1 }, paGrade: 'C', cgpaMatchesOfficial: true });
    form.init();
    assert.equal(form.calculatedStpmCgpa, '3.50');
    assert.equal(form.cgpaMatchesOfficial, true);
    form.selectQualification('Diploma');
    assert.equal(form.paGrade, '');
    assert.equal(form.cgpaMatchesOfficial, false);
    assert.equal(form.calculatedStpmCgpa, '');
});
