// Analytics Dashboard Client Logic
// Handles KPI cards, charts, and recent activity for management analytics

(function () {
    const API_BASE = '../../api/';
    const ENDPOINTS = {
        kpis: API_BASE + 'get-analytics-kpis.php',
        revenue: API_BASE + 'get-revenue-data.php',
        occupancy: API_BASE + 'get-occupancy-data.php',
        activity: API_BASE + 'get-recent-activities.php?limit=10',
        revenueBreakdown: API_BASE + 'get-revenue-breakdown.php'
    };

    let revenueChartInstance = null;
    let occupancyChartInstance = null;
    let currentBreakdownDays = 30;

    document.addEventListener('DOMContentLoaded', initializeAnalyticsPage);

    function initializeAnalyticsPage() {
        console.log('🚀 Initializing analytics page...');
        console.log('API endpoints:', ENDPOINTS);
        
        updateCurrentDate();
        const refreshButton = document.getElementById('analytics-refresh');
        const activityRefreshButton = document.getElementById('analytics-activity-refresh');
        const breakdownSelect = document.getElementById('revenueBreakdownRange');

        if (refreshButton) {
            refreshButton.addEventListener('click', () => reloadAnalytics(refreshButton));
        }
        if (activityRefreshButton) {
            activityRefreshButton.addEventListener('click', () => loadRecentActivity(true));
        }

        if (breakdownSelect) {
            currentBreakdownDays = parseInt(breakdownSelect.value, 10) || 30;
            breakdownSelect.addEventListener('change', event => {
                currentBreakdownDays = parseInt(event.target.value, 10) || 30;
                loadRevenueBreakdown(currentBreakdownDays);
            });
        }

        hydrateBootstrapData();

        console.log('🔄 Starting initial data load...');
        reloadAnalytics();
    }

    async function reloadAnalytics(button) {
        setButtonLoadingState(button, true);
        let hasErrors = false;
        let errorMessages = [];
        
        try {
            // Load KPIs data
            try {
                console.log('Loading KPIs from:', ENDPOINTS.kpis);
                const kpiData = await fetchJson(ENDPOINTS.kpis);
                console.log('KPIs response:', kpiData);
                if (kpiData.success) {
                    renderKpis(kpiData.data || {});
                    if (kpiData.guest_sentiment) {
                        renderGuestSentiment(kpiData.guest_sentiment);
                    }
                    console.log('✅ KPIs loaded successfully');
                } else {
                    console.error('❌ KPIs API error:', kpiData.message);
                    errorMessages.push('KPIs: ' + kpiData.message);
                    hasErrors = true;
                }
            } catch (error) {
                console.error('❌ Error loading KPIs:', error);
                errorMessages.push('KPIs: ' + error.message);
                hasErrors = true;
            }

            // Load revenue data
            try {
                console.log('Loading revenue from:', ENDPOINTS.revenue);
                const revenueData = await fetchJson(ENDPOINTS.revenue);
                console.log('Revenue response:', revenueData);
                if (revenueData.success) {
                    renderRevenueChart(revenueData.data || []);
                    console.log('✅ Revenue loaded successfully');
                } else {
                    console.error('❌ Revenue API error:', revenueData.message);
                    errorMessages.push('Revenue: ' + revenueData.message);
                    hasErrors = true;
                }
            } catch (error) {
                console.error('❌ Error loading revenue data:', error);
                errorMessages.push('Revenue: ' + error.message);
                hasErrors = true;
            }

            // Load occupancy data
            try {
                console.log('Loading occupancy from:', ENDPOINTS.occupancy);
                const occupancyData = await fetchJson(ENDPOINTS.occupancy);
                console.log('Occupancy response:', occupancyData);
                if (occupancyData.success) {
                    renderOccupancySummary(occupancyData.summary || {}, occupancyData.data || []);
                    renderOccupancyChart(occupancyData.data || []);
                    console.log('✅ Occupancy loaded successfully');
                } else {
                    console.error('❌ Occupancy API error:', occupancyData.message);
                    errorMessages.push('Occupancy: ' + occupancyData.message);
                    hasErrors = true;
                }
            } catch (error) {
                console.error('❌ Error loading occupancy data:', error);
                errorMessages.push('Occupancy: ' + error.message);
                hasErrors = true;
            }

            // Load revenue breakdown
            try {
                console.log('Loading revenue breakdown...');
                await loadRevenueBreakdown(currentBreakdownDays, true);
                console.log('✅ Revenue breakdown loaded successfully');
            } catch (error) {
                console.error('❌ Error loading revenue breakdown:', error);
                errorMessages.push('Revenue Breakdown: ' + error.message);
                hasErrors = true;
            }

            // Show success/error notification
            if (hasErrors) {
                console.error('❌ Analytics errors:', errorMessages);
                showErrorNotification('Some analytics data could not be loaded: ' + errorMessages.join(', '));
            } else {
                console.log('✅ All analytics data loaded successfully');
                showSuccessNotification('Analytics data refreshed successfully.');
            }

        } catch (error) {
            console.error('❌ Critical error loading analytics data:', error);
            showErrorNotification('Unable to load analytics data. Please check your connection and try again.');
        } finally {
            setButtonLoadingState(button, false);
            loadRecentActivity();
        }
    }

    async function loadRecentActivity(showToast = false) {
        const container = document.getElementById('analytics-recent-activity');
        if (!container) return;

        setContainerLoading(container);

        try {
            const activityData = await fetchJson(ENDPOINTS.activity);
            if (!activityData.success) {
                throw new Error(activityData.message || 'Unable to load activity');
            }
            renderRecentActivity(container, activityData.activities || []);
            if (showToast) {
                showSuccessNotification('Recent activity refreshed.');
            }
        } catch (error) {
            console.error('Error loading recent activity:', error);
            container.innerHTML = renderEmptyState('Unable to load recent activity.');
            if (showToast) {
                showErrorNotification('Unable to refresh recent activity.');
            }
        }
    }

    function hydrateBootstrapData() {
        const bootstrap = window.analyticsBootstrap;
        if (!bootstrap) {
            return;
        }

        if (bootstrap.kpis) {
            renderKpis(bootstrap.kpis);
        }

        if (bootstrap.guestSentiment) {
            renderGuestSentiment(bootstrap.guestSentiment);
        }

        if (bootstrap.revenueBreakdown) {
            renderRevenueBreakdown(bootstrap.revenueBreakdown);
        }
    }

    function renderKpis(kpis) {
        if (!kpis) {
            return;
        }

        updatePercentCard('analytics-revenue-growth', Number(kpis.revenue_growth_pct ?? 0), true);
        updatePercentCard('analytics-occupancy', Number(kpis.today_occupancy_pct ?? kpis.average_occupancy_pct ?? 0));
        updateCard('analytics-satisfaction', kpis.guest_satisfaction_score ? `${Number(kpis.guest_satisfaction_score).toFixed(1)}/5` : '—', Number(kpis.guest_satisfaction_score ?? 0));
        updateCurrencyCard('analytics-room-rate', Number(kpis.average_room_rate ?? 0), '₱');

        updatePercentCard('analytics-room-utilization', Number(kpis.average_occupancy_pct ?? 0));
        updateCurrencyCard('analytics-today-revenue', Number(kpis.today_revenue ?? 0), '₱');
        updatePercentCard('analytics-returning-guests', Number(kpis.returning_guests_pct ?? 0));
    }

    function renderOccupancySummary(summary, timeline) {
        const occupancyToday = summary.today_rate ?? null;
        const occupancyAverage = summary.average_rate ?? null;

        updatePercentCard('analytics-occupancy', occupancyToday);
        updatePercentCard('analytics-room-utilization', occupancyAverage);

        // Guest satisfaction heuristic based on occupancy average
        const satisfaction = occupancyAverage != null
            ? Math.min(5, 3.8 + (occupancyAverage / 100) * 1.2)
            : null;
        updateCard('analytics-satisfaction', satisfaction ? `${satisfaction.toFixed(1)}/5` : '—');

        if (summary.average_rate != null) {
            updateCurrencyCard('analytics-room-rate', summary.average_rate, '₱');
        }
    }

    function renderRevenueChart(data) {
        const canvas = document.getElementById('analytics-revenue-chart');
        const loader = document.getElementById('analytics-revenue-loading');
        if (!canvas) return;

        toggleElementVisibility(loader, false);
        canvas.classList.remove('hidden');

        if (revenueChartInstance) {
            revenueChartInstance.destroy();
        }

        // Handle empty or invalid data
        if (!data || !Array.isArray(data) || data.length === 0) {
            canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
            const ctx = canvas.getContext('2d');
            ctx.fillStyle = '#6B7280';
            ctx.font = '16px Arial';
            ctx.textAlign = 'center';
            ctx.fillText('No revenue data available', canvas.width / 2, canvas.height / 2);
            return;
        }

        const labels = data.map(item => formatDateLabel(item.date));
        const revenueDataset = data.map(item => item.revenue || item.daily_revenue || 0);
        const transactionDataset = data.map(item => item.transactions || 0);

        revenueChartInstance = new Chart(canvas.getContext('2d'), {
            type: 'line',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Revenue (₱)',
                        data: revenueDataset,
                        borderColor: '#3B82F6',
                        backgroundColor: 'rgba(59, 130, 246, 0.15)',
                        tension: 0.35,
                        fill: true
                    },
                    {
                        label: 'Transactions',
                        data: transactionDataset,
                        borderColor: '#10B981',
                        backgroundColor: 'rgba(16, 185, 129, 0.15)',
                        tension: 0.35,
                        fill: true,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: value => formatCurrency(value)
                        }
                    },
                    y1: {
                        position: 'right',
                        grid: {
                            drawOnChartArea: false
                        },
                        ticks: {
                            callback: value => formatNumber(value)
                        }
                    }
                }
            }
        });
    }

    function renderOccupancyChart(data) {
        console.log('🎯 Rendering occupancy chart with data:', data);
        
        const canvas = document.getElementById('analytics-occupancy-chart');
        const loader = document.getElementById('analytics-occupancy-loading');
        
        if (!canvas) {
            console.error('❌ Canvas element not found!');
            return;
        }

        // Hide loader and show canvas
        if (loader) {
            loader.style.display = 'none';
        }
        canvas.style.display = 'block';
        canvas.classList.remove('hidden');

        if (occupancyChartInstance) {
            occupancyChartInstance.destroy();
        }

        // Handle empty or invalid data
        if (!data || !Array.isArray(data) || data.length === 0) {
            console.log('❌ No occupancy data available');
            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.fillStyle = '#6B7280';
            ctx.font = '16px Arial';
            ctx.textAlign = 'center';
            ctx.fillText('No occupancy data available', canvas.width / 2, canvas.height / 2);
            return;
        }

        console.log('Processing occupancy data...');
        const labels = data.map(item => formatDateLabel(item.date));
        const occupancyDataset = data.map(item => {
            const rate = parseFloat(item.occupancy_rate) || 0;
            return Math.max(0, Math.min(100, rate)); // Ensure it's between 0-100
        });
        
        console.log('Labels:', labels);
        console.log('Dataset:', occupancyDataset);

        try {
            console.log('Creating Chart.js instance...');
            occupancyChartInstance = new Chart(canvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels,
                    datasets: [
                        {
                            label: 'Occupancy (%)',
                            data: occupancyDataset,
                            backgroundColor: '#22C55E',
                            borderColor: '#16A34A',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: value => `${value}%`
                            }
                        },
                        x: {
                            ticks: {
                                maxRotation: 45
                            }
                        }
                    }
                }
            });
            
            console.log('✅ Occupancy chart created successfully!');
        } catch (error) {
            console.error('❌ Error creating occupancy chart:', error);
            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.fillStyle = '#DC2626';
            ctx.font = '16px Arial';
            ctx.textAlign = 'center';
            ctx.fillText('Error creating chart', canvas.width / 2, canvas.height / 2);
        }
    }

    function renderRecentActivity(container, activities) {
        if (!activities.length) {
            container.innerHTML = renderEmptyState('No recent activity recorded yet.');
            return;
        }

        container.innerHTML = activities.map(activity => `
            <div class="border border-gray-100 rounded-lg p-4 flex items-start gap-3 hover:bg-gray-50 transition-colors">
                <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
                    <i class="fas fa-history text-primary"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-gray-800">${escapeHtml(activity.title || activity.type || 'Activity')}</p>
                    <p class="text-sm text-gray-600 mt-1">${escapeHtml(activity.description || activity.message || 'Updated data')}</p>
                    <p class="text-xs text-gray-400 mt-2">${formatDateTime(activity.created_at)}</p>
                </div>
            </div>
        `).join('');
    }

    async function fetchJson(url) {
        try {
            const response = await fetch(url, {
                credentials: 'include',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const payload = await response.json();

            if (payload.redirect) {
                window.location.href = payload.redirect;
                return payload;
            }

            if (!payload.success) {
                throw new Error(payload.message || 'API request failed');
            }

            return payload;
        } catch (error) {
            console.error(`Fetch error for ${url}:`, error);
            throw error;
        }
    }

    function updateCurrentDate() {
        const currentDateEl = document.getElementById('analytics-current-date');
        if (currentDateEl) {
            currentDateEl.textContent = new Date().toLocaleDateString();
        }
    }

    function setButtonLoadingState(button, isLoading) {
        if (!button) return;
        if (isLoading) {
            button.dataset.originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Loading...</span>';
            button.disabled = true;
        } else {
            if (button.dataset.originalText) {
                button.innerHTML = button.dataset.originalText;
                delete button.dataset.originalText;
            }
            button.disabled = false;
        }
    }

    function setContainerLoading(container) {
        if (!container) return;
        container.innerHTML = `
            <div class="bg-gray-50 rounded-lg p-4 text-center text-gray-500">
                <i class="fas fa-spinner fa-spin mr-2"></i>Loading...
            </div>
        `;
    }

    function updatePercentCard(elementId, value, allowSigned = false) {
        if (value == null || isNaN(value) || typeof value !== 'number') {
            updateCard(elementId, '—');
            return;
        }

        const formatted = `${allowSigned && value > 0 ? '+' : ''}${Number(value).toFixed(1)}%`;
        updateCard(elementId, formatted, value);
    }

    function updateCurrencyCard(elementId, amount, currencySymbol = '₱') {
        const formatted = formatCurrency(amount, currencySymbol);
        updateCard(elementId, formatted, amount);
    }

    function updateCard(elementId, text, value) {
        const el = document.getElementById(elementId);
        if (!el) return;

        el.textContent = text;

        if (typeof value === 'number') {
            el.classList.remove('text-green-600', 'text-red-600');
            if (value > 0 && elementId.includes('growth')) {
                el.classList.add('text-green-600');
            } else if (value < 0 && elementId.includes('growth')) {
                el.classList.add('text-red-600');
            }
        }
    }

    function calculateGrowth(current, previous) {
        if (!current && !previous) return 0;
        if (!previous) return current > 0 ? 100 : 0;
        return ((current - previous) / previous) * 100;
    }

    async function loadRevenueBreakdown(days = 30, skipLoader = false) {
        const body = document.getElementById('revenueBreakdownBody');
        if (!body) {
            return;
        }

        if (!skipLoader) {
            body.innerHTML = `
                <tr class="border-b">
                    <td colspan="5" class="py-4 text-center text-gray-400">Loading segments...</td>
                </tr>
            `;
        }

        try {
            const response = await fetchJson(`${ENDPOINTS.revenueBreakdown}?days=${days}`);
            if (!response.success) {
                throw new Error(response.message || 'Failed to load breakdown');
            }
            renderRevenueBreakdown(response.segments || []);
        } catch (error) {
            console.error('Revenue breakdown error:', error);
            body.innerHTML = `
                <tr class="border-b">
                    <td colspan="5" class="py-4 text-center text-red-500">Unable to load revenue breakdown.</td>
                </tr>
            `;
        }
    }

    function renderRevenueBreakdown(segments) {
        const body = document.getElementById('revenueBreakdownBody');
        if (!body) {
            return;
        }

        if (!segments.length) {
            body.innerHTML = `
                <tr class="border-b">
                    <td colspan="5" class="py-4 text-center text-gray-400">No revenue segments available.</td>
                </tr>
            `;
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

    function renderGuestSentiment(sentiment) {
        if (!sentiment) {
            return;
        }

        updatePercentCard('guestSentimentPositiveValue', Number(sentiment.positive_pct ?? 0));
        setBarWidth('guestSentimentPositiveBar', Number(sentiment.positive_pct ?? 0));

        const responseHours = Number(sentiment.average_response_hours ?? 0);
        const responseText = responseHours ? `${responseHours.toFixed(1)} hrs` : '—';
        updateCard('guestSentimentResponseValue', responseText, responseHours);
        setBarWidth('guestSentimentResponseBar', Math.min(100, (responseHours / 12) * 100));

        updatePercentCard('guestSentimentResolvedValue', Number(sentiment.resolved_pct ?? 0));
        setBarWidth('guestSentimentResolvedBar', Number(sentiment.resolved_pct ?? 0));

        const sampleEl = document.getElementById('guestSentimentSample');
        if (sampleEl) {
            sampleEl.textContent = Number(sentiment.sample_size ?? 0).toLocaleString();
        }

        const ratingEl = document.getElementById('guestSentimentRating');
        if (ratingEl) {
            ratingEl.textContent = sentiment.average_rating ? `${Number(sentiment.average_rating).toFixed(1)}/5` : '—';
        }

        const driversEl = document.getElementById('guestSentimentDrivers');
        if (driversEl) {
            const drivers = sentiment.top_drivers || [];
            driversEl.innerHTML = drivers.length ? drivers.map(driver => `
                <li>• ${escapeHtml(driver.category ?? 'General')} (${Number(driver.count ?? 0)})</li>
            `).join('') : '<li class="text-gray-400">No key drivers identified.</li>';
        }
    }

    function setBarWidth(elementId, percentage) {
        const el = document.getElementById(elementId);
        if (!el) {
            return;
        }
        const clamped = Math.max(0, Math.min(100, Number(percentage) || 0));
        el.style.width = `${clamped}%`;
    }

    function formatPercent(value) {
        if (value == null || isNaN(value)) {
            return '—';
        }
        return `${Number(value).toFixed(1)}%`;
    }

    function toggleElementVisibility(element, show) {
        if (!element) return;
        if (show) {
            element.classList.remove('hidden');
        } else {
            element.classList.add('hidden');
        }
    }

    function formatCurrency(amount, prefix = '₱') {
        if (typeof Utils !== 'undefined' && typeof Utils.formatCurrency === 'function') {
            return Utils.formatCurrency(amount)
                .replace('₱', prefix);
        }
        return `${prefix}${Number(amount || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    }

    function formatNumber(value) {
        return Number(value || 0).toLocaleString('en-US');
    }

    function formatDateLabel(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    }

    function formatDateTime(dateString) {
        if (typeof Utils !== 'undefined' && typeof Utils.formatDateTime === 'function') {
            return Utils.formatDateTime(dateString);
        }
        const date = new Date(dateString);
        return date.toLocaleString();
    }

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function renderEmptyState(message) {
        return `
            <div class="bg-gray-50 rounded-lg p-4 text-center text-gray-500">
                <i class="fas fa-info-circle mr-2"></i>${message}
            </div>
        `;
    }

    function showErrorNotification(message) {
        if (typeof Utils !== 'undefined' && typeof Utils.showNotification === 'function') {
            Utils.showNotification(message, 'error');
        }
    }

    function showSuccessNotification(message) {
        if (typeof Utils !== 'undefined' && typeof Utils.showNotification === 'function') {
            Utils.showNotification(message, 'success');
        }
    }
})();
