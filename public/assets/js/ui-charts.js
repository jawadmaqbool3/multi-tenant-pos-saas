/* ==========================================================================
   POS UI chart helpers
   Shared, theme-aware palette for ApexCharts so charts stop hardcoding hex
   values. Values are read from the live --kt-* CSS custom properties, so a
   theme switch is picked up automatically by re-reading the palette.
   ========================================================================== */
(function () {
    "use strict";

    function read(name, fallback) {
        var value = getComputedStyle(document.documentElement)
            .getPropertyValue(name)
            .trim();
        return value || fallback;
    }

    function isDark() {
        var root = document.documentElement;
        return (
            root.getAttribute("data-theme") === "dark" ||
            root.classList.contains("dark")
        );
    }

    window.UiTheme = {
        isDark: isDark,
        read: read,

        palette: function () {
            return {
                primary: read("--kt-primary", "#4f46e5"),
                success: read("--kt-success", "#16a34a"),
                danger: read("--kt-danger", "#dc2626"),
                warning: read("--kt-warning", "#d97706"),
                info: read("--kt-info", "#0ea5e9"),
                dark: read("--kt-dark", "#0f172a"),
                gray: read("--kt-gray-500", "#94a3b8"),
                grid: read("--kt-gray-200", "#e2e8f0"),
                label: read("--kt-text-muted", "#64748b"),
                text: read("--kt-body-color", "#0f172a"),
                surface: read("--kt-card-bg", "#ffffff"),
            };
        },

        /* Subscribe to theme changes; returns the MutationObserver. */
        onThemeChange: function (callback) {
            var observer = new MutationObserver(function () {
                callback(isDark());
            });
            observer.observe(document.documentElement, {
                attributes: true,
                attributeFilter: ["data-theme"],
            });
            return observer;
        },
    };
})();
