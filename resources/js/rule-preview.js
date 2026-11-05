export function rulePreview(url, defaults = {}) {
    return {
        busy: false, result: null, errors: [], generation: 0,
        init() {
            this.form = this.$el.closest('form');
            this.invalidate = () => { this.generation++; this.result = null; this.errors = []; };
            this.form.addEventListener('input', this.invalidate);
            this.form.addEventListener('change', this.invalidate);
            this.$el.addEventListener('input', this.invalidate);
            this.$el.addEventListener('change', this.invalidate);
        },
        destroy() {
            this.form.removeEventListener('input', this.invalidate);
            this.form.removeEventListener('change', this.invalidate);
            this.$el.removeEventListener('input', this.invalidate);
            this.$el.removeEventListener('change', this.invalidate);
        },
        async run() {
            if (this.busy) return;
            this.busy = true; this.result = null; this.errors = [];
            await this.$nextTick();
            const generation = ++this.generation;
            const body = new FormData(this.form);
            body.delete('_method');
            for (const [key, value] of Object.entries(defaults)) if (!body.has(key) && value !== null) body.set(key, value);
            for (const [key, value] of new FormData(document.getElementById('rule-preview-sample'))) body.append(key, value);
            try {
                const response = await fetch(url, {method: 'POST', body, headers: {'Accept': 'application/json'}});
                const data = await response.json();
                if (generation !== this.generation) return;
                if (response.ok) this.result = data;
                else this.errors = Object.values(data.errors ?? {}).flat().length ? Object.values(data.errors).flat() : ['Preview unavailable. Refresh the page and try again.'];
            } catch {
                if (generation === this.generation) this.errors = ['Preview unavailable. Check your connection and refresh the page.'];
            } finally { this.busy = false; }
        },
    };
}
