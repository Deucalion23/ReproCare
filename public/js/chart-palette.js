/* One fixed reporting palette; clinical series explicitly select status tokens. */
(() => {
    'use strict';
    const palette = ['purple', 'pink', 'green', 'teal', 'amber', 'peach'];
    const color = name => getComputedStyle(document.documentElement).getPropertyValue(`--color-${name}`).trim();
    const alpha = (hex, opacity) => /^#[\da-f]{6}$/i.test(hex)
        ? `rgba(${[1, 3, 5].map(i => parseInt(hex.slice(i, i + 2), 16)).join(',')},${opacity})` : hex;
    window.ReproCareCharts = { palette, color };
    if (!window.Chart) return;
    Chart.register({
        id: 'reprocarePalette',
        beforeUpdate(chart) {
            chart.options.color = color('text-muted');
            chart.options.borderColor = color('border');
            const legend = chart.options.plugins?.legend;
            if (legend?.labels) legend.labels.color = color('text-muted');
            for (const scale of Object.values(chart.options.scales || {})) {
                if (scale.ticks) scale.ticks.color = color('text-muted');
                if (scale.title) scale.title.color = color('text-muted');
                if (scale.grid) scale.grid.color = color('border');
                if (scale.border) scale.border.color = color('border');
            }
            chart.data.datasets.forEach((dataset, index) => {
                const tokens = dataset.rcColors;
                const base = tokens ? tokens.map(color) : color(dataset.rcColor || palette[index % palette.length]);
                const type = dataset.type || chart.config.type;
                dataset.borderColor = type === 'doughnut' || type === 'pie' ? color('surface') : base;
                dataset.backgroundColor = type === 'line' ? alpha(base, .14) : base;
                dataset.pointBackgroundColor = base;
                dataset.pointBorderColor = color('surface');
                dataset.hoverBackgroundColor = base;
            });
        },
    });
    document.addEventListener('rc:theme-changed', () => {
        Object.values(Chart.instances).forEach(chart => chart.update('none'));
    });
})();
