// Analytics Dashboard interactions for management console
(function () {
    const API_BASE = '../../api/';
    const ENDPOINTS = {
        revenue: API_BASE + 'get-revenue-data.php',
        occupancy: API_BASE + 'get-occupancy-data.php',
        breakdown: API_BASE + 'get-revenue-breakdown.php',
        kpis: API_BASE + 'get-analytics-kpis.php'
    };

    let revenueChartInstance = null;
    let occupancyChartInstance = null;
    let currentBreakdownDays = 30;

    document.addEventListener('DOMContentLoaded', initialiseDashboard);

    function initialiseDashboard() {
        const bootstrap = window.analyticsDashboardBootstrap || {};
        const breakdownSelect = document.getElementById('revenueBreakdownRange');
        currentBreakdownDays = breakdownSelect ? parseInt(breakdownSelect.value, 10) || 30 : 30;

        if (breakdownSelect) {
            breakdownSelect.addEventListener('change', event => {
                currentBreakdownDays = parseInt(event.target.value, 10) || 30;
                loadRevenueBreakdown(currentBreakdownDays, false);
            });
        }

        hydrateRevenueBreakdown(bootstrap.revenueBreakdown || []);
        hydrateSentiment(bootstrap.guestSentiment || null);
        hydrateAutomation(bootstrap.automation || []);

        attachRefreshHandlers();
        loadRevenueChart();
        loadOccupancyChart();
        // Refresh KPIs in background to keep sentiment data fresh
        refreshKpis();
    }

    function attachRefreshHandlers() {
        document.querySelectorAll('.refresh-chart').forEach(button => {
            button.addEventListener('click', () => {
                const type = button.getAttribute('data-chart');
                if (type === 'revenue') {
                    loadRevenueChart(true);
                } else if (type === 'occupancy') {
                    loadOccupancyChart(true);
                }
            });
        });
    }

    async function refreshKpis() {
        try {
            const payload = await fetchJson(ENDPOINTS.kpis);
            if (!payload.success || !payload.data) {
                return;
            }
            hydrateSentiment(mapKpiToSentiment(payload.data));
        } catch (_) {
            // Silently ignore KPI refresh failure – charts will still load
        }
    }

    function mapKpiToSentiment(kpiData) {
        if (!kpiData) {
            return null;
        }
        return {
            positive_pct: Number(kpiData.positive_feedback_pct ?? 0),
            resolved_pct: Number(kpiData.resolved_feedback_pct ?? 0),
            average_response_hours: kpiData.average_response_hours !== null ? Number(kpiData.average_response_hours) : null,
            sample_size: Number(kpiData.feedback_sample ?? 0),
            average_rating: kpiData.guest_satisfaction_score !== null ? Number(kpiData.guest_satisfaction_score) : null,
            top_drivers: []
        };
    }

    async function loadRevenueChart(forceReload = false) {
        const canvas = document.getElementById('analyticsRevenueChart');
        const loader = document.querySelector('.chart-loading');
        if (!canvas) return;

        if (forceReload && loader) {
            loader.classList.remove('hidden');
            canvas.classList.add('hidden');
        }

        try {
            const payload = await fetchJson(ENDPOINTS.revenue);
            if (!payload.success) throw new Error(payload.message || 'Failed to load revenue data');

            toggleCanvas(canvas, loader, true);
            renderRevenueChart(canvas, payload.data || []);
        } catch (error) {
            showLoaderError(loader, error);
        }
    }

    async function loadOccupancyChart(forceReload = false) {
        const canvases = document.querySelectorAll('#analyticsOccupancyChart');
        if (!canvases.length) return;
        const canvas = canvases[0];
        const loaders = document.querySelectorAll('.chart-loading');
        const loader = loaders.length > 1 ? loaders[1] : null;

        if (forceReload && loader) {
            loader.classList.remove('hidden');
            canvas.classList.add('hidden');
        }

        try {
            const payload = await fetchJson(ENDPOINTS.occupancy);
            if (!payload.success) throw new Error(payload.message || 'Failed to load occupancy data');

            toggleCanvas(canvas, loader, true);
            renderOccupancyChart(canvas, payload.data || []);
        } catch (error) {
            showLoaderError(loader, error);
        }
    }

    async function loadRevenueBreakdown(days = 30, useLoader = true) {
        const body = document.getElementById('revenueBreakdownBody');
        if (!body) return;

        if (useLoader) {
            body.innerHTML = `
                <tr class="border-b">
                    <td colspan="5" class="py-4 text-center text-gray-400">Loading segments...</td>
                </tr>`;
        }

        try {
            const payload = await fetchJson(`${ENDPOINTS.breakdown}?days=${days}`);
            if (!payload.success) throw new Error(payload.message || 'Failed to load breakdown');
            hydrateRevenueBreakdown(payload.segments || []);
        } catch (error) {
            body.innerHTML = `
                <tr class="border-b">
                    <td colspan="5" class="py-4 text-center text-red-500">Unable to load revenue breakdown.</td>
                </tr>`;
        }
    }

    function hydrateRevenueBreakdown(segments) {
        const body = document.getElementById('revenueBreakdownBody');
        if (!body) return;

        if (!segments || segments.length === 0) {
            body.innerHTML = `
                <tr class="border-b">
                    <td colspan="5" class="py-4 text-center text-gray-400">No revenue segments available.</td>
                </tr>`;
            return;
        }

        body.innerHTML = segments.map(segment => `
            <tr class="border-b last:border-b-0">
                <td class="py-2 font-medium text-gray-800">${escapeHtml(segment.segment || 'Unknown')}</td>
                <td class="py-2 text-gray-600">${formatPercent(segment.occupancy_pct)}</td>
                <td class="py-2 text-gray-600">${formatCurrency(segment.adr)}</td>
                <td class="py-2 text-gray-600">${formatCurrency(segment.revpar)}</td>
                <td class="py-2 text-gray-600">${formatPercent(segment.contribution_pct)}</td>
            </tr>
        `).join('');
    }

    function hydrateSentiment(sentiment) {
        if (!sentiment) return;

        updateText('dashboardSentimentPositive', formatPercent(sentiment.positive_pct, true));
        setWidth('dashboardSentimentPositiveBar', sentiment.positive_pct);
        const responseHours = sentiment.average_response_hours;
        updateText('dashboardSentimentResponse', responseHours != null ? `${Number(responseHours).toFixed(1)} hrs` : '—');
        setWidth('dashboardSentimentResponseBar', responseHours != null ? (Number(responseHours) / 12) * 100 : 0);
        updateText('dashboardSentimentResolved', formatPercent(sentiment.resolved_pct, true));
        setWidth('dashboardSentimentResolvedBar', sentiment.resolved_pct);
        updateText('dashboardSentimentSample', Number(sentiment.sample_size || 0).toLocaleString());
        updateText('dashboardSentimentRating', sentiment.average_rating != null ? `${Number(sentiment.average_rating).toFixed(1)}/5` : '—');

        const drivers = sentiment.top_drivers || [];
        const list = document.getElementById('dashboardSentimentDrivers');
        if (list) {
            list.innerHTML = drivers.length
                ? drivers.map(driver => `<li>• ${escapeHtml(driver.category || 'General')} (${Number(driver.count || 0)})</li>`).join('')
                : '<li class="text-gray-400">No key drivers identified.</li>';
        }
    }

    function hydrateAutomation(cards) {
        const container = document.getElementById('automationSummary');
        if (!container) return;

        const statusClassMap = {
            active: 'bg-green-100 text-green-600',
            monitoring: 'bg-blue-100 text-blue-600',
            stable: 'bg-purple-100 text-purple-600',
            review: 'bg-amber-100 text-amber-600'
        };

        container.querySelectorAll('.automation-status').forEach((badge, index) => {
            const card = cards[index];
            const statusKey = (card ? card.status : badge.dataset.status || '').toLowerCase();
            Object.values(statusClassMap).forEach(cls => badge.classList.remove(...cls.split(' ')));
            if (statusClassMap[statusKey]) {
                badge.classList.add(...statusClassMap[statusKey].split(' '));
            } else {
                badge.classList.add('bg-gray-100', 'text-gray-700');
            }
        });
    }

    function renderRevenueChart(canvas, data) {
        if (revenueChartInstance) {
            revenueChartInstance.destroy();
        }
        const labels = data.map(item => formatDate(item.date));
        const values = data.map(item => Number(item.revenue || 0));
        revenueChartInstance = new Chart(canvas.getContext('2d'), {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: 'Revenue',
                    data: values,
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.15)',
                    tension: 0.35,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        ticks: {
                            callback: value => formatCurrency(value)
                        }
                    }
                }
            }
        });
    }

    function renderOccupancyChart(canvas, data) {
        if (occupancyChartInstance) {
            occupancyChartInstance.destroy();
        }
        const labels = data.map(item => formatDate(item.date));
        const values = data.map(item => Number(item.occupancy_rate || 0));
        occupancyChartInstance = new Chart(canvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Occupancy %',
                    data: values,
                    backgroundColor: '#10B981'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: value => `${value}%`
                        }
                    }
                }
            }
        });
    }

    function toggleCanvas(canvas, loader, show) {
        if (!canvas) return;
        if (loader) loader.classList.toggle('hidden', show);
        canvas.classList.toggle('hidden', !show);
    }

    function showLoaderError(loader, error) {
        if (!loader) return;
        loader.classList.remove('hidden');
        loader.innerHTML = `<div class="text-center text-sm text-red-500">${escapeHtml(error.message || 'Unable to load chart data')}</div>`;
    }

    async function fetchJson(url) {
        const response = await fetch(url, { credentials: 'include' });
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        return await response.json();
    }

    function updateText(id, value) {
        const el = document.getElementById(id);
        if (el) {
            el.textContent = value;
        }
    }

    function setWidth(id, value) {
        const el = document.getElementById(id);
        if (!el) return;
        const width = Math.max(0, Math.min(100, Number(value) || 0));
        el.style.width = `${width}%`;
    }

    function formatCurrency(amount) {
        if (typeof Utils !== 'undefined' && typeof Utils.formatCurrency === 'function') {
            return Utils.formatCurrency(amount);
        }
        return '₱' + Number(amount || 0).toLocaleString('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function formatPercent(value, includeSymbol = false) {
        if (value == null || isNaN(value)) {
            return includeSymbol ? '0.0%' : '0.0';
        }
        const formatted = Number(value).toFixed(1);
        return includeSymbol ? `${formatted}%` : formatted;
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        if (Number.isNaN(date.getTime())) return '';
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    }

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
})();
