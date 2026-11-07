export function authCard(initial, loginUrl, registerUrl) {
    return {
        panel: initial,
        switchTo(panel, event) {
            if (event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
            event.preventDefault();
            if (panel === this.panel) return;
            this.panel = panel;
            window.history.pushState(null, '', panel === 'login' ? loginUrl : registerUrl);
            window.dispatchEvent(new Event('auth-panel-changed'));
        },
        syncLocation() {
            const path = window.location.pathname;
            if (path === new URL(loginUrl).pathname) this.panel = 'login';
            if (path === new URL(registerUrl).pathname) this.panel = 'register';
            window.dispatchEvent(new Event('auth-panel-changed'));
        },
    };
}
