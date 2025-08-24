// List of available themes
const themes = [
    //'coastal-dawn-theme.css',
    //'autumn-harbor-theme.css',
    //'rosewood-vintage-theme.css',
	//'steampunk-metal-theme.css'
];

// Default theme index (0 = coastal dawn)
let currentThemeIndex = 0;

// Load saved theme from localStorage if it exists
const savedTheme = localStorage.getItem('siteTheme');
if (savedTheme && themes.includes(savedTheme)) {
    currentThemeIndex = themes.indexOf(savedTheme);
}

// Create <link> for theme if it doesn't exist
let themeLink = document.getElementById('themeStylesheet');
if (!themeLink) {
    themeLink = document.createElement('link');
    themeLink.id = 'themeStylesheet';
    themeLink.rel = 'stylesheet';
    document.head.appendChild(themeLink);
}

// Apply current theme
function applyTheme(index) {
    themeLink.href = `/assets/css/${themes[index]}`;
    localStorage.setItem('siteTheme', themes[index]);
}

// Toggle to next theme
function toggleTheme() {
    currentThemeIndex = (currentThemeIndex + 1) % themes.length;
    applyTheme(currentThemeIndex);
}

// Apply saved/default theme on page load
applyTheme(currentThemeIndex);

// Optionally: hook this to a button with id="themeToggleBtn"
document.getElementById('themeToggleBtn')?.addEventListener('click', toggleTheme);
