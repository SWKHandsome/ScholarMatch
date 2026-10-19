

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('qualificationEditor', (initialRoutes = []) => ({
    routes: [],
    nextKey: 0,
    init() {
        this.routes = initialRoutes.map(route => this.makeRoute(route));
    },
    makeRoute(values = {}) {
        const route = typeof values === 'object' && values !== null ? values : {};
        return {
            _key: this.nextKey++,
            education_level: route.education_level ?? '',
            study_status: route.study_status ?? '',
            min_spm_as: route.min_spm_as ?? '',
            min_spm_credits: route.min_spm_credits ?? '',
            min_cgpa: route.min_cgpa ?? '',
            min_year_of_study: route.min_year_of_study ?? '',
            max_year_of_study: route.max_year_of_study ?? '',
        };
    },
    addRoute() {
        if (this.routes.length < 50) {
            this.routes.push(this.makeRoute());
            this.$nextTick(() => document.getElementById(`qualification-${this.routes.at(-1)._key}-level`)?.focus());
        }
    },
    levelChanged(route) {
        if (route.education_level === 'SPM') {
            route.min_cgpa = '';
            route.study_status = 'completed';
        } else {
            route.min_spm_as = '';
            route.min_spm_credits = '';
        }
        this.statusChanged(route);
    },
    statusChanged(route) {
        if (route.study_status !== 'studying') {
            route.min_year_of_study = '';
            route.max_year_of_study = '';
        }
    },
    fieldName(index, field) {
        return `qualification_routes[${index}][${field}]`;
    },
    fieldId(route, field) {
        return `qualification-${route._key}-${field}`;
    },
}));

Alpine.start();
