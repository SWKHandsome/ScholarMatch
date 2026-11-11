export function autoGrowingTextarea() {
    let observer;
    let width;

    return {
        init() {
            this.$el.style.overflowY = 'hidden';
            this.$el.style.resize = 'none';
            this.resize();

            observer = new ResizeObserver(([entry]) => {
                if (entry.contentRect.width === width) return;
                width = entry.contentRect.width;
                this.resize();
            });
            observer.observe(this.$el);
        },
        resize() {
            const textarea = this.$el;
            textarea.style.height = 'auto';
            const borderHeight = textarea.offsetHeight - textarea.clientHeight;
            textarea.style.height = `${textarea.scrollHeight + borderHeight}px`;
        },
        destroy() {
            observer?.disconnect();
        },
    };
}
