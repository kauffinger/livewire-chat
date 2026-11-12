export default function loadHighlightTheme(isDark) {
    let link = document.head.querySelector('link[data-highlight-theme]');

    if (!link) {
        link = document.createElement('link');
        link.rel = 'stylesheet';
        link.setAttribute('data-highlight-theme', 'dynamic');
        document.head.appendChild(link);
    }

    const themeUrl = isDark ? '/css/highlight/github-dark.css' : '/css/highlight/github-light.css';

    if (link.getAttribute('href') !== themeUrl) {
        link.href = themeUrl;
    }
}
