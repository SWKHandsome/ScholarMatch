

import Alpine from 'alpinejs';
import { initAccountValidation } from './account-validation';
import { authCard } from './auth-card';
import { autoGrowingTextarea } from './auto-growing-textarea';
import { incomeCategoryPreview } from './income-category-preview';
import { rulePreview } from './rule-preview';
import { academicResultEditor, qualificationEditor } from './academic-qualifications';

window.Alpine = Alpine;
Alpine.data('authCard', authCard);
Alpine.data('autoGrowingTextarea', autoGrowingTextarea);
Alpine.data('incomeCategoryPreview', incomeCategoryPreview);
Alpine.data('rulePreview', rulePreview);

Alpine.data('academicResultEditor', academicResultEditor);
Alpine.data('qualificationEditor', qualificationEditor);

initAccountValidation();
Alpine.start();
