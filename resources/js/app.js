

import Alpine from 'alpinejs';
import { authCard } from './auth-card';
import { incomeCategoryPreview } from './income-category-preview';
import { rulePreview } from './rule-preview';
import { academicResultEditor, qualificationEditor } from './academic-qualifications';

window.Alpine = Alpine;
Alpine.data('authCard', authCard);
Alpine.data('incomeCategoryPreview', incomeCategoryPreview);
Alpine.data('rulePreview', rulePreview);

Alpine.data('academicResultEditor', academicResultEditor);
Alpine.data('qualificationEditor', qualificationEditor);

Alpine.start();
