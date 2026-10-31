

import Alpine from 'alpinejs';
import { authCard } from './auth-card';
import { academicResultEditor, qualificationEditor } from './academic-qualifications';

window.Alpine = Alpine;
Alpine.data('authCard', authCard);

Alpine.data('academicResultEditor', academicResultEditor);
Alpine.data('qualificationEditor', qualificationEditor);

Alpine.start();
