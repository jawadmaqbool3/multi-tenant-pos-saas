<script>
    window.dashboardWidgetsUrl = "{{ route('dashboard.widgets') }}";
    window.dashboardCurrency = "{{ config('app.currency') }}";
</script>
<script>
    (function() {
        const PRIMARY = '#009EF7';
        const DANGER = '#F1416C';
        const SUCCESS = '#50CD89';

        const arrowUp = '<span class="svg-icon svg-icon-5 svg-icon-success ms-n1"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M13 11L21.2 2.8C21.6 2.4 21.6 1.8 21.2 1.4C20.8 1 20.2 1 19.8 1.4L11.6 9.6L3.4 1.4C3 1 2.4 1 2 1.4C1.6 1.8 1.6 2.4 2 2.8L10.2 11L2 19.2C1.6 19.6 1.6 20.2 2 20.6C2.2 20.8 2.4 20.9 2.7 20.9C3 20.9 3.2 20.8 3.4 20.6L11.6 12.4L19.8 20.6C20 20.8 20.2 20.9 20.5 20.9C20.8 20.9 21 20.8 21.2 20.6C21.6 20.2 21.6 19.6 21.2 19.2L13 11Z" fill="currentColor"/></svg></span>';
        const arrowDown = '<span class="svg-icon svg-icon-5 svg-icon-danger ms-n1"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M11 13L2.8 21.2C2.4 21.6 1.8 21.6 1.4 21.2C1 20.8 1 20.2 1.4 19.8L9.6 11.6L1.4 3.4C1 3 1 2.4 1.4 2C1.8 1.6 2.4 1.6 2.8 2L11 10.2L19.2 2C19.6 1.6 20.2 1.6 20.6 2C21 2.4 21 3 20.6 3.4L12.4 11.6L20.6 19.8C21 20.2 21 20.8 20.6 21.2C20.4 21.4 20.2 21.5 19.9 21.5C19.6 21.5 19.4 21.4 19.2 21.2L11 13Z" fill="currentColor"/></svg></span>';

        function directionBadge(direction, inverse) {
            const good = (direction === 'positive');
            const success = inverse ? !good : good;
            return '<span class="badge ' + (success ? 'badge-light-success' : 'badge-light-danger') + ' fs-base">' +
                (good ? arrowUp : arrowDown) + '</span>';
        }

        function setDirection(el, direction, inverse) {
            const node = el.querySelector('[data-role="direction"]');
            if (node && direction) {
                node.innerHTML = directionBadge(direction, inverse);
            }
        }

        function formatValue(value, format) {
            if (format === 'text') {
                return (value === null || value === undefined) ? '' : String(value);
            }
            if (value === null || value === undefined || value === '') {
                return '0';
            }
            return convertToAbbreviation(value);
        }

        function valueText(value, format) {
            const text = formatValue(value, format);
            if (format === 'currency') {
                return text;
            }
            return text;
        }

        function setKpi(el, kpi) {
            if (!kpi) return;
            const valueNode = el.querySelector('[data-role="value"]');
            if (valueNode) {
                valueNode.textContent = formatValue(kpi.value, kpi.format);
            }
            setDirection(el, kpi.direction, kpi.inverse);
        }

        function legendColors(data) {
            if (data.legend && data.legend.length) {
                return data.legend.map(function(item) {
                    if (item.color === 'danger') return DANGER;
                    if (item.color === 'success') return SUCCESS;
                    return PRIMARY;
                });
            }
            return [PRIMARY, DANGER];
        }

        function renderLegend(el, legend) {
            const node = el.querySelector('[data-role="legend"]');
            if (!node || !legend || !legend.length) return;
            let html = '';
            legend.forEach(function(item) {
                const color = item.color === 'danger' ? 'bg-danger' : (item.color === 'success' ? 'bg-success' : 'bg-primary');
                html += '<div class="d-flex fs-6 fw-semibold align-items-center mb-2">' +
                    '<div class="bullet w-8px h-6px rounded-2 ' + color + ' me-3"></div>' +
                    '<div class="text-gray-500 flex-grow-1 me-4">' + item.label + '</div>' +
                    '<div class="fw-bolder text-gray-700 text-xxl-end">' +
                    (item.prefix || '') + formatValue(item.value, item.format) + '</div></div>';
            });
            node.innerHTML = html;
        }

        const WidgetRenderers = {
            card: function(el, data) {
                if (data.empty) {
                    const emptyValue = el.querySelector('[data-role="value"]');
                    if (emptyValue) {
                        emptyValue.textContent = data.empty;
                        emptyValue.classList.remove('fs-2hx');
                        emptyValue.classList.add('fs-3');
                    }
                    const emptyLink = el.querySelector('[data-role="link"]');
                    if (emptyLink && data.link) {
                        emptyLink.innerHTML = '<a href="' + data.link.url + '" class="btn btn-sm btn-primary">' +
                            data.link.label + '</a>';
                    }
                    return;
                }

                const valueNode = el.querySelector('[data-role="value"]');
                if (valueNode) {
                    const prefix = data.format === 'currency' ? ((data.currency || window.dashboardCurrency) + ' ') : '';
                    valueNode.textContent = prefix + formatValue(data.value, data.format);
                }
                setDirection(el, data.direction, data.inverse);

                const badgeNode = el.querySelector('[data-role="badge"]');
                if (badgeNode && data.badge) {
                    badgeNode.innerHTML = '<span class="badge badge-light-' + (data.badge.color || 'primary') + '">' +
                        data.badge.text + '</span>';
                }

                const secondary = el.querySelector('[data-role="secondary"]');
                if (secondary && data.secondary !== undefined && data.secondary !== null) {
                    secondary.innerHTML = '<span class="text-gray-400 fs-7">' +
                        (data.secondary_label || '') + '</span>' +
                        '<span class="text-gray-700 fw-bold fs-5">' +
                        formatValue(data.secondary, data.secondary_format) + '</span>';
                }

                const rows = el.querySelector('[data-role="rows"]');
                if (rows && data.rows && data.rows.length) {
                    let html = '';
                    data.rows.forEach(function(row) {
                        html += '<div class="d-flex fs-6 fw-semibold align-items-center mb-2">' +
                            '<div class="text-gray-500 flex-grow-1 me-4">' + row.label + '</div>' +
                            '<div class="fw-bolder text-gray-700 text-xxl-end">' +
                            formatValue(row.value, row.format) + '</div></div>';
                    });
                    rows.innerHTML = html;
                }

                const link = el.querySelector('[data-role="link"]');
                if (link && data.link) {
                    link.innerHTML = '<a href="' + data.link.url + '" class="btn btn-sm btn-light">' +
                        data.link.label + '</a>';
                }
            },

            card_customers: function(el, data) {
                WidgetRenderers.card(el, data);
                const list = el.querySelector('[data-role="heroes"]');
                if (!list) return;
                const heroes = data.heroes || [];
                if (!heroes.length) {
                    const body = list.parentElement;
                    if (body) body.querySelector('[data-role="heroes"]').remove();
                    return;
                }
                const palette = ['primary', 'success', 'warning', 'info'];
                const base = "{{ url('/assets/media/users/profile') }}";
                heroes.slice(0, 7).forEach(function(hero) {
                    const name = hero.name || '?';
                    const color = palette[name.charCodeAt(0) % palette.length];
                    if (hero.image) {
                        list.insertAdjacentHTML('beforeend',
                            '<div class="symbol symbol-35px symbol-circle" data-bs-toggle="tooltip" title="' + name + '">' +
                            '<img alt="Pic" src="' + base + '/' + hero.image + '" /></div>');
                    } else {
                        list.insertAdjacentHTML('beforeend',
                            '<div class="symbol symbol-35px symbol-circle" data-bs-toggle="tooltip" title="' + name + '">' +
                            '<span class="symbol-label bg-' + color + ' text-inverse-' + color + ' fw-bold">' +
                            name.charAt(0) + '</span></div>');
                    }
                });
                if (heroes.length > 7) {
                    list.insertAdjacentHTML('beforeend',
                        '<span class="symbol symbol-35px symbol-circle"><span class="symbol-label bg-light text-gray-400 fs-8 fw-bold">+' +
                        (heroes.length - 7) + '</span></span>');
                }
            },

            chart: function(el, data) {
                setKpi(el, data.kpi);
                renderLegend(el, data.legend);
                const node = el.querySelector('[data-role="chart"]');
                if (!node || typeof ApexCharts === 'undefined') return;

                let options;
                if (data.chart_type === 'donut') {
                    options = {
                        series: data.series,
                        labels: data.labels,
                        chart: { type: 'donut', height: 200, toolbar: { show: false } },
                        legend: { show: false },
                        dataLabels: { enabled: false },
                        colors: legendColors(data),
                    };
                } else if (data.chart_type === 'line') {
                    options = {
                        series: data.series,
                        chart: { type: 'line', height: 200, toolbar: { show: false } },
                        xaxis: { categories: data.categories || [] },
                        dataLabels: { enabled: false },
                        colors: data.series && data.series.length > 1 ? [PRIMARY, SUCCESS] : [PRIMARY],
                    };
                } else {
                    options = {
                        series: data.series,
                        chart: { type: 'bar', height: 200, toolbar: { show: false } },
                        plotOptions: { bar: { borderRadius: 4, columnWidth: '45%' } },
                        xaxis: { categories: data.categories || [], axisBorder: { show: false }, axisTicks: { show: false } },
                        dataLabels: { enabled: false },
                        colors: data.series && data.series.length > 1 ? [PRIMARY, SUCCESS] : [PRIMARY],
                    };
                }

                new ApexCharts(node, options).render();
            },

            table: function(el, data) {
                const wrap = el.querySelector('[data-role="table"]');
                if (!wrap) return;

                const link = el.querySelector('[data-role="link"]');
                if (link && data.link) {
                    link.innerHTML = '<a href="' + data.link.url + '" class="btn btn-sm btn-light">' + data.link.label + '</a>';
                }

                if (!data.rows || !data.rows.length) {
                    wrap.innerHTML = '<div class="text-muted fs-7 py-5">' + (data.empty || 'No data') + '</div>';
                    return;
                }

                let html = '<table class="table align-middle table-row-dashed fs-7 gy-3">';
                html += '<thead><tr class="text-start text-gray-400 fw-bold text-uppercase gs-0">';
                (data.columns || []).forEach(function(col) {
                    html += '<th class="min-w-80px">' + col + '</th>';
                });
                html += '</tr></thead><tbody class="fw-semibold text-gray-600">';
                data.rows.forEach(function(row) {
                    html += '<tr>';
                    row.forEach(function(cell) {
                        if (cell && typeof cell === 'object') {
                            if (cell.badge) {
                                html += '<td><span class="badge badge-light-' + cell.badge + '">' + cell.text + '</span></td>';
                            } else {
                                html += '<td>' + (cell.text === undefined || cell.text === null ? '' : cell.text) + '</td>';
                            }
                        } else {
                            html += '<td>' + (cell === null || cell === undefined ? '' : cell) + '</td>';
                        }
                    });
                    html += '</tr>';
                });
                html += '</tbody></table>';
                wrap.innerHTML = html;
            }
        };

        function loadWidgets() {
            if (!window.dashboardWidgetsUrl) return;
            $.ajax({
                url: window.dashboardWidgetsUrl,
                method: 'GET',
                success: function(res) {
                    if (!res || !res.success || !res.widgets) return;
                    Object.keys(res.widgets).forEach(function(key) {
                        const el = document.querySelector('[data-widget-key="' + key + '"]');
                        if (!el) return;
                        const data = res.widgets[key];
                        const renderer = WidgetRenderers[data.renderer] || WidgetRenderers[data.type];
                        if (renderer) renderer(el, data);
                    });
                }
            });
        }

        $(document).ready(loadWidgets);

        window.WidgetRenderers = WidgetRenderers;
        window.loadDashboardWidgets = loadWidgets;
    })();
</script>
