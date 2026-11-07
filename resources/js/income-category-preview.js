export function incomeCategoryPreview({ value = '', categories = [] } = {}) {
    return {
        value: String(value ?? ''), categories, badInput: false,
        setIncome(value, badInput = false) {
            this.value = String(value);
            this.badInput = badInput;
        },
        get empty() { return !this.badInput && this.value.trim() === ''; },
        get valid() {
            const value = this.value.trim();
            return !this.badInput && /^[+-]?(?:\d+(?:\.\d{0,2})?|\.\d{1,2})$/.test(value)
                && Number.isFinite(Number(value)) && Number(value) >= 0 && Number(value) <= 9999999999.99;
        },
        get match() {
            if (!this.valid) return null;
            const income = Number(this.value);
            // The server supplies the same min-income/id ordering used for classification.
            return this.categories.find(category => income >= Number(category.min)
                && (category.max === null || income <= Number(category.max))) ?? null;
        },
        get heading() {
            if (this.match) return `Your income category: ${this.match.name}`;
            if (this.empty) return 'Income category';
            return this.valid ? 'Income category unavailable' : 'Check the income amount';
        },
        get detail() {
            if (this.match) return `Current range: ${this.match.range}`;
            if (this.empty) return 'Enter your monthly household income to see its category.';
            if (!this.valid) return 'Enter a non-negative amount with up to 2 decimal places.';
            return this.categories.length
                ? 'No category matches this amount. Please contact the administrator.'
                : 'No income ranges are configured. Please contact the administrator.';
        },
    };
}
