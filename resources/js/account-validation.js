export function initAccountValidation() {
    const isAccountField = (target) => target instanceof HTMLInputElement && target.hasAttribute('data-account-field');

    function update(field) {
        const group = field.closest('[data-account-group]');
        const feedback = group?.querySelector('[data-account-feedback]');
        if (!feedback) return;

        const invalid = !field.validity.valid;
        feedback.textContent = field.validity.valueMissing
            ? `Please enter your ${field.name}.`
            : invalid ? field.dataset.formatMessage : '';
        feedback.hidden = !invalid;
        field.setAttribute('aria-invalid', String(invalid));
        field.classList.toggle('border-error', invalid);
    }

    document.addEventListener('invalid', (event) => {
        if (!isAccountField(event.target)) return;
        event.preventDefault();
        update(event.target);
        if (event.target.form?.querySelector(':invalid') === event.target) event.target.focus();
    }, true);

    document.addEventListener('focusout', (event) => {
        if (!isAccountField(event.target)) return;
        const field = event.target;
        field.value = field.name === 'email' ? field.value.trim().toLowerCase() : field.value.trim();
        update(field);
    });

    document.addEventListener('input', (event) => {
        if (!isAccountField(event.target)) return;
        const field = event.target;
        const group = field.closest('[data-account-group]');
        const serverError = group?.querySelector('[data-account-server-error]');
        if (serverError) serverError.hidden = true;
        if (field.getAttribute('aria-invalid') === 'true') update(field);
    });
}
