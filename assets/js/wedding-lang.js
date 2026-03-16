/**
 * wedding-lang.js
 * Bilingual toggle: English / Spanish
 * Default: English, remembers last choice via localStorage
 */

(function () {
    const STORAGE_KEY = 'weddingLang';
    const DEFAULT_LANG = 'en';

    function getSavedLang() {
        try {
            return localStorage.getItem(STORAGE_KEY) || DEFAULT_LANG;
        } catch (e) {
            return DEFAULT_LANG;
        }
    }

    function saveLang(lang) {
        try {
            localStorage.setItem(STORAGE_KEY, lang);
        } catch (e) { /* ignore */ }
    }

    function applyLang(lang) {
        const other = lang === 'en' ? 'es' : 'en';

        // Show/hide block and inline elements
        document.querySelectorAll('.lang-' + lang).forEach(el => el.classList.add('active'));
        document.querySelectorAll('.lang-' + other).forEach(el => el.classList.remove('active'));

        // Update toggle button label
        const btn = document.getElementById('langToggleBtn');
        if (btn) {
            if (lang === 'en') {
                btn.innerHTML = '<span class="lang-flag">🇪🇸</span> Español';
            } else {
                btn.innerHTML = '<span class="lang-flag">🇺🇸</span> English';
            }
            btn.setAttribute('data-lang', lang);
            btn.setAttribute('aria-label', lang === 'en' ? 'Switch to Spanish' : 'Switch to English');
        }

        // Update <html> lang attribute
        document.documentElement.lang = lang === 'en' ? 'en' : 'es';
    }

    function toggleLang() {
        const btn = document.getElementById('langToggleBtn');
        const current = btn ? btn.getAttribute('data-lang') : getSavedLang();
        const next = current === 'en' ? 'es' : 'en';
        saveLang(next);
        applyLang(next);
    }

    // Initialize on DOM ready
    function init() {
        const lang = getSavedLang();
        applyLang(lang);

        const btn = document.getElementById('langToggleBtn');
        if (btn) {
            btn.addEventListener('click', toggleLang);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
