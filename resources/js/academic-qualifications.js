function categoryFor(config, level) {
    return Object.keys(config.categories).find(key => config.categories[key].qualifications.includes(level)) ?? '';
}

function scaleFor(config, level) {
    const scales = config.qualificationScales[level] ?? [];
    return scales.length === 1 ? scales[0] : '';
}

export function academicResultEditor(config, initial = {}) {
    return {
        config,
        category: initial.category ?? categoryFor(config, initial.level),
        level: initial.level ?? '',
        studyStatus: initial.studyStatus ?? '',
        year: initial.year ?? '',
        scale: initial.scale || scaleFor(config, initial.level),
        counts: initial.counts && typeof initial.counts === 'object' && !Array.isArray(initial.counts) ? initial.counts : {},
        cgpa: initial.cgpa ?? '',
        stamRank: initial.stamRank ?? '',
        paGrade: initial.paGrade ?? '',
        cgpaMatchesOfficial: Boolean(initial.cgpaMatchesOfficial),
        official: Boolean(initial.official),
        init() {
            if (this.level && !this.cgpaBased) this.studyStatus = 'completed';
            this.counts = Object.fromEntries(this.grades.map(grade => [grade, this.counts[grade] ?? 0]));
        },
        get cgpaBased() { return this.config.cgpaLevels.includes(this.level); },
        get autoStpmCgpa() { return this.level === 'STPM' && this.studyStatus === 'completed'; },
        get calculatedStpmCgpa() {
            if (!this.autoStpmCgpa || !this.validCounts || this.totalSubjects < 4 || !this.grades.includes(this.paGrade) || this.count(this.paGrade) < 1) return '';
            const points = this.config.stpmPoints;
            if (!points) return '';
            const counts = Object.fromEntries(this.grades.map(grade => [grade, this.count(grade)]));
            counts[this.paGrade]--;
            let total = Math.round(points[this.paGrade] * 100);
            let remaining = 3;
            for (const grade of this.grades) {
                const take = Math.min(remaining, counts[grade]);
                total += take * Math.round(points[grade] * 100);
                remaining -= take;
            }
            return (Math.floor((total + 2) / 4) / 100).toFixed(2);
        },
        get gradeBased() { return (this.config.qualificationScales[this.level] ?? []).length > 0; },
        get grades() { return this.config.scales[this.scale]?.grades ?? []; },
        get maxSubjects() { return this.config.scales[this.scale]?.max_subjects ?? 20; },
        get totalSubjects() { return this.grades.reduce((total, grade) => total + this.count(grade), 0); },
        get validCounts() {
            return this.grades.length > 0 && this.totalSubjects >= 1 && this.totalSubjects <= this.maxSubjects
                && this.grades.every(grade => {
                    const count = Number(this.counts[grade] ?? 0);
                    return Number.isInteger(count) && count >= 0 && count <= this.maxSubjects;
                });
        },
        get summary() {
            return this.grades.filter(grade => this.count(grade) > 0).map(grade => `${this.count(grade)} × ${grade}`).join(', ');
        },
        count(grade) {
            const value = Number(this.counts[grade] ?? 0);
            return Number.isInteger(value) && value >= 0 ? value : 0;
        },
        countAtOrAbove(grade) {
            const index = this.grades.indexOf(grade);
            return index < 0 ? 0 : this.grades.slice(0, index + 1).reduce((sum, item) => sum + this.count(item), 0);
        },
        adjust(grade, delta) {
            if (delta > 0 && this.totalSubjects >= this.maxSubjects) return;
            this.counts[grade] = Math.max(0, this.count(grade) + delta);
            this.stpmInputsChanged();
        },
        stpmInputsChanged() { this.cgpaMatchesOfficial = false; },
        resetResults() {
            this.counts = {};
            this.cgpa = '';
            this.stamRank = '';
            this.paGrade = '';
            this.cgpaMatchesOfficial = false;
            this.year = '';
            this.studyStatus = '';
            this.scale = '';
            this.official = false;
        },
        selectCategory(category) {
            if (category === this.category) return;
            this.resetResults();
            this.category = category;
            this.level = '';
        },
        selectQualification(level) {
            if (level === this.level) return;
            this.resetResults();
            this.level = level;
            this.scale = scaleFor(this.config, level);
            this.counts = Object.fromEntries(this.grades.map(grade => [grade, 0]));
            if (!this.cgpaBased) this.studyStatus = 'completed';
        },
        scaleChanged() {
            this.counts = Object.fromEntries(this.grades.map(grade => [grade, 0]));
            this.official = false;
        },
        statusChanged() {
            this.year = '';
            this.cgpa = '';
            this.paGrade = '';
            this.cgpaMatchesOfficial = false;
            if (this.level === 'STPM') this.counts = Object.fromEntries(this.grades.map(grade => [grade, 0]));
            this.official = false;
        },
    };
}

export function qualificationEditor(initialRoutes = [], config) {
    return {
        config,
        routes: [],
        nextKey: 0,
        nextConditionKey: 0,
        nextGradeRowKey: 0,
        init() { this.routes = initialRoutes.map(route => this.makeRoute(route)); },
        makeRoute(values = {}) {
            const source = typeof values === 'object' && values !== null ? values : {};
            const route = {
                _key: this.nextKey++, education_level: source.education_level ?? '', study_status: source.study_status ?? '',
                min_year_of_study: source.min_year_of_study ?? '', max_year_of_study: source.max_year_of_study ?? '',
                grading_scale: source.grading_scale || scaleFor(this.config, source.education_level), requirements: [],
            };
            for (const type of ['min_cgpa', 'min_spm_as', 'min_spm_credits', 'min_stam_rank']) {
                if (source[type] !== null && source[type] !== undefined && source[type] !== '') {
                    route.requirements.push(this.makeCondition(type, { value: source[type] }));
                }
            }
            if (Array.isArray(source.grade_requirements) && source.grade_requirements.length) {
                route.requirements.push(this.makeCondition('grade', { rows: source.grade_requirements }));
            }
            for (const type of ['stpm_average', 'uec_average', 'uec_total']) {
                if (source[type]) {
                    const settings = source[type];
                    route.requirements.push(this.makeCondition(type, {
                        value: settings[this.thresholdField(type)], subjects: settings.subjects, grade: settings.minimum_grade,
                    }));
                }
            }
            return route;
        },
        makeCondition(type, values = {}) {
            return {
                _key: this.nextConditionKey++, type, value: values.value ?? '',
                subjects: values.subjects ?? '',
                grade: values.grade ?? '',
                rows: type === 'grade'
                    ? (Array.isArray(values.rows) ? values.rows : [{}]).map(row => this.makeGradeRow(row)) : [],
            };
        },
        makeGradeRow(values = {}) {
            const row = values && typeof values === 'object' ? values : {};
            return { _key: this.nextGradeRowKey++, value: row.min_subjects ?? row.value ?? '', grade: row.grade ?? '' };
        },
        addGradeRow(condition) {
            if (condition.rows.length < 20) condition.rows.push(this.makeGradeRow());
        },
        removeGradeRow(route, condition, rowIndex) {
            condition.rows.splice(rowIndex, 1);
            if (!condition.rows.length) {
                route.requirements = route.requirements.filter(item => item._key !== condition._key);
            }
        },
        cgpaBased(route) { return this.config.cgpaLevels.includes(route.education_level); },
        gradeBased(route) { return (this.config.qualificationScales[route.education_level] ?? []).length > 0; },
        grades(route) { return this.config.scales[route.grading_scale]?.grades ?? []; },
        maxSubjects(route) { return this.config.scales[route.grading_scale]?.max_subjects ?? 20; },
        types(route) {
            const types = [];
            if (this.cgpaBased(route)) types.push({ value: 'min_cgpa', label: 'Minimum overall CGPA' });
            if (this.gradeBased(route) && route.grading_scale) {
                types.push({ value: 'grade', label: 'Number of subjects at a grade or better' });
            }
            if (route.education_level === 'SPM') types.push(
                { value: 'min_spm_as', label: 'Minimum SPM As (A+, A, A-)' },
                { value: 'min_spm_credits', label: 'Minimum SPM credits (C or better)' },
            );
            if (route.education_level === 'STAM') types.push({ value: 'min_stam_rank', label: 'Minimum overall STAM pangkat' });
            if (route.education_level === 'STPM') types.push({ value: 'stpm_average', label: 'Minimum average points across best subjects' });
            if (route.education_level === 'UEC') types.push(
                { value: 'uec_average', label: 'Maximum average points across best subjects' },
                { value: 'uec_total', label: 'Maximum total points across best subjects' },
            );
            return types;
        },
        availableTypes(route, condition = null) {
            return this.types(route).filter(type => !route.requirements.some(item => item._key !== condition?._key && item.type === type.value));
        },
        hasInput(value) { return value !== '' && value !== null && value !== undefined; },
        gradeRowStarted(row) { return this.hasInput(row.value) || this.hasInput(row.grade); },
        conditionStarted(condition) {
            if (condition.type === 'grade') return condition.rows.some(row => this.gradeRowStarted(row));
            if (this.isPoints(condition)) return [condition.value, condition.subjects, condition.grade].some(value => this.hasInput(value));
            return this.hasInput(condition.value);
        },
        addRoute() {
            if (this.routes.length >= 50) return;
            this.routes.push(this.makeRoute());
            this.$nextTick(() => document.getElementById(`qualification-${this.routes.at(-1)._key}-level`)?.focus());
        },
        levelChanged(route) {
            route.grading_scale = scaleFor(this.config, route.education_level);
            route.requirements = [];
            route.study_status = this.cgpaBased(route) ? '' : 'completed';
            route.min_year_of_study = '';
            route.max_year_of_study = '';
            this.addRequirement(route);
        },
        scaleChanged(route) {
            route.requirements = route.requirements.filter(condition => condition.type !== 'grade');
            if (route.grading_scale && !route.requirements.length) this.addRequirement(route);
        },
        statusChanged(route) {
            if (!this.cgpaBased(route) || route.study_status !== 'studying') {
                route.min_year_of_study = '';
                route.max_year_of_study = '';
            }
        },
        addRequirement(route) {
            if (route.requirements.length >= 20) return;
            const type = this.availableTypes(route)[0]?.value;
            if (type) route.requirements.push(this.makeCondition(type));
        },
        conditionChanged(condition) {
            const key = condition._key;
            Object.assign(condition, this.makeCondition(condition.type), { _key: key });
        },
        isPoints(condition) { return ['stpm_average', 'uec_average', 'uec_total'].includes(condition.type); },
        thresholdField(type) { return { stpm_average: 'min_average', uec_average: 'max_average', uec_total: 'max_total' }[type]; },
        conditionName(index, conditionIndex, condition, field = 'value') {
            const base = this.fieldName(index, condition.type === 'grade' ? 'grade_requirements' : condition.type);
            if (condition.type === 'grade') return `${base}[${conditionIndex}][${field === 'value' ? 'min_subjects' : 'grade'}]`;
            if (this.isPoints(condition)) return `${base}[${field === 'value' ? this.thresholdField(condition.type) : field === 'grade' ? 'minimum_grade' : 'subjects'}]`;
            return base;
        },
        gradeRowName(index, rowIndex, field) {
            return `${this.fieldName(index, 'grade_requirements')}[${rowIndex}][${field === 'value' ? 'min_subjects' : 'grade'}]`;
        },
        valueLabel(condition) {
            return {
                min_cgpa: 'Minimum Overall CGPA', min_spm_as: 'Minimum SPM As', min_spm_credits: 'Minimum SPM Credits',
                grade: 'Minimum Number of Subjects', stpm_average: 'Minimum Average Points',
                uec_average: 'Maximum Average Points', uec_total: 'Maximum Total Points',
            }[condition.type] ?? 'Minimum Overall STAM Pangkat';
        },
        valueMax(route, condition) {
            if (condition.type === 'min_cgpa' || condition.type === 'stpm_average') return 4;
            if (condition.type === 'uec_average') return 9;
            if (condition.type === 'uec_total') return Number(condition.subjects || 1) * 9;
            return this.maxSubjects(route);
        },
        valueMin(condition) {
            if (condition.type === 'uec_total') return Number(condition.subjects || 1);
            return condition.type === 'uec_average' ? 1 : 0;
        },
        valueStep(condition) { return ['min_cgpa', 'stpm_average', 'uec_average'].includes(condition.type) ? '0.01' : '1'; },
        describe(route, condition) {
            if (!this.conditionStarted(condition)) return 'Leave these fields blank if there is no result minimum.';
            const label = this.config.labels?.[route.education_level] ?? route.education_level;
            if (condition.type === 'grade') {
                const pattern = this.gradePattern(route);
                if (pattern) return `Minimum grade pattern: ${pattern} (or better). Every row must pass; stronger grades count towards lower thresholds.`;
                const enteredRows = condition.rows.filter(row => this.gradeRowStarted(row));
                const complete = enteredRows.every(row => this.grades(route).includes(row.grade) && this.hasInput(row.value) && Number(row.value) === 0);
                return complete ? 'All grade minima are zero; no subject-count minimum applies.' : 'Complete each started grade row to see the combined minimum pattern.';
            }
            if (condition.value === '' || condition.value === null) return 'Complete this condition to see its meaning.';
            if (condition.type === 'min_cgpa') return `Official overall CGPA must be at least ${condition.value}; this is separate from a best-subject average.`;
            if (condition.type === 'min_stam_rank') return `Overall STAM pangkat must be ${condition.value} or better.`;
            if (condition.type === 'min_spm_as') return `At least ${condition.value} SPM As, counting A+, A and A-.`;
            if (condition.type === 'min_spm_credits') return `At least ${condition.value} SPM credits, counting C and all stronger grades.`;
            if (!this.hasInput(condition.subjects) || !this.hasInput(condition.grade)) return 'Complete the subject count and grade cutoff to see this condition.';
            const comparison = condition.type === 'stpm_average' ? 'at least' : 'no more than';
            const calculation = condition.type === 'uec_total' ? 'total points' : 'average points';
            return `Best ${condition.subjects} ${label} subjects at ${condition.grade} or better: ${calculation} must be ${comparison} ${condition.value}.`;
        },
        gradePattern(route) {
            const rows = (route.requirements.find(item => item.type === 'grade')?.rows ?? []).filter(row => this.gradeRowStarted(row));
            if (rows.some(row => !this.grades(route).includes(row.grade) || !this.hasInput(row.value))) return '';
            const ordered = [...rows]
                .sort((a, b) => this.grades(route).indexOf(a.grade) - this.grades(route).indexOf(b.grade));
            let used = 0;
            const parts = [];
            for (const condition of ordered) {
                const count = Number(condition.value);
                if (!Number.isInteger(count) || count < 0 || count > this.maxSubjects(route)) return '';
                const additional = Math.max(0, count - used);
                parts.push(...Array(additional).fill(condition.grade));
                used = Math.max(used, count);
            }
            return parts.length ? parts.join(', ') : '';
        },
        fieldName(index, field) { return `qualification_routes[${index}][${field}]`; },
        fieldId(route, field) { return `qualification-${route._key}-${field}`; },
    };
}
