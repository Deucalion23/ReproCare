{{-- Shared Light/Dark control logic (no <script> wrapper — include inside one).
     Writes the rc_theme key that layouts/app.blade.php reads pre-paint,
     so the choice persists across every portal page. Safe to include
     alongside page-specific init code; definitions are guarded. --}}
if (!window.setRcTheme) {
    window.currentRcTheme = function () {
        return localStorage.getItem('rc_theme') === 'dark' ? 'dark' : 'light';
    };
    window.paintRcThemeControls = function (mode) {
        var l = document.getElementById('theme-opt-light');
        var d = document.getElementById('theme-opt-dark');
        var sw = document.querySelector('.theme-switch-input');
        if (l) l.classList.toggle('active', mode === 'light');
        if (d) d.classList.toggle('active', mode === 'dark');
        if (sw) sw.checked = (mode === 'dark');
    };
    window.setRcTheme = function (mode) {
        mode = (mode === 'dark') ? 'dark' : 'light';
        try { localStorage.setItem('rc_theme', mode); } catch (e) {}
        document.documentElement.setAttribute('data-theme', mode);
        document.documentElement.classList.toggle('dark', mode === 'dark');
        window.paintRcThemeControls(mode);
    };
    document.addEventListener('DOMContentLoaded', function () {
        window.paintRcThemeControls(window.currentRcTheme());
    });
}
