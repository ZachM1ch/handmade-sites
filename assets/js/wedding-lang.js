// Wedding section language toggle
// Defaults to English, persists choice via localStorage

function applyLang(lang) {
    document.querySelectorAll('[data-en]').forEach(function(el) {
        if (lang === 'es' && el.getAttribute('data-es')) {
            el.innerHTML = el.getAttribute('data-es');
        } else {
            el.innerHTML = el.getAttribute('data-en');
        }
    });
    var btn = document.getElementById('lang-toggle');
    if (btn) btn.textContent = (lang === 'es') ? 'English' : 'Español';
    document.documentElement.lang = lang;
    localStorage.setItem('weddingLang', lang);
}

function toggleLang() {
    var current = localStorage.getItem('weddingLang') || 'en';
    applyLang(current === 'en' ? 'es' : 'en');
}

// Apply saved preference on page load, defaulting to English
(function() {
    var saved = localStorage.getItem('weddingLang') || 'en';
    applyLang(saved);
})();
