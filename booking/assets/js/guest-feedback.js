(function () {
    const API_BASE = '../../api/';
    const ENDPOINTS = {
        list: API_BASE + 'get-guest-feedback.php',
        stats: API_BASE + 'get-guest-feedback-stats.php'
    };

    const state = {
        items: [],
        summary: null,
        distribution: [],
        categories: [],
        recent: [],
        filters: {},
        sort: 'newest',
        page: 1,
        limit: 10,
        total: 0,
        totalPages: 1,
        isLoading: false
    };

    const selectors = {
        refreshButton: '#feedback-refresh',
        tableCount: '#feedback-table-count',
        tableBody: '#feedback-table-body',
        pageSize: '#feedback-page-size',
        prevBtn: '#feedback-prev',
        nextBtn: '#feedback-next',
        filterForm: '#feedback-filter-form',
        searchInput: '#feedback-search',
        ratingFilter: '#feedback-rating-filter',
        statusFilter: '#feedback-status-filter',
        categoryFilter: '#feedback-category-filter',
        typeFilter: '#feedback-type-filter',
        dateFrom: '#feedback-date-from',
        dateTo: '#feedback-date-to',
        sort: '#feedback-sort',
        reset: '#feedback-reset',
        distribution: '#feedback-distribution',
        categoriesBody: '#feedback-categories-body',
        recentList: '#feedback-recent-list',
        viewAllButton: '#feedback-view-all',
        stats: {
            averageRating: '#feedback-average-rating',
            totalReviews: '#feedback-total-reviews',
            pendingResponse: '#feedback-pending-response',
            responseRate: '#feedback-response-rate',
            satisfactionRate: '#feedback-satisfaction-rate',
            complaints: '#feedback-complaints',
            resolved: '#feedback-resolved'
        }
    };

    const elements = {};

    document.addEventListener('DOMContentLoaded', init);

    function init() {
        cacheElements();
        hydrateBootstrap();
        bindEvents();
        updatePaginationControls();
    }

    function cacheElements() {
        Object.keys(selectors).forEach(key => {
            if (typeof selectors[key] === 'string') {
                elements[key] = document.querySelector(selectors[key]);
            } else {
                elements[key] = {};
                Object.keys(selectors[key]).forEach(innerKey => {
                    elements[key][innerKey] = document.querySelector(selectors[key][innerKey]);
                });
            }
        });
    }

    function hydrateBootstrap() {
        const bootstrap = window.guestFeedbackBootstrap;
        if (!bootstrap) {
            return;
        }

        state.summary = bootstrap.summary || null;
        state.distribution = bootstrap.distribution || [];
        state.categories = bootstrap.categories || [];
        state.recent = bootstrap.recent || [];

        if (bootstrap.table) {
            state.items = bootstrap.table.items || [];
            state.filters = bootstrap.table.filters || {};
            state.sort = bootstrap.table.sort || 'newest';
            state.page = bootstrap.table.pagination?.page || 1;
            state.limit = bootstrap.table.pagination?.limit || 10;
            state.total = bootstrap.table.pagination?.total || 0;
            state.totalPages = bootstrap.table.pagination?.total_pages || 1;
        }

        populateFiltersFromState();
        renderAll();
    }

    function bindEvents() {
        if (elements.refreshButton) {
            elements.refreshButton.addEventListener('click', handleRefresh);
        }

        if (elements.pageSize) {
            elements.pageSize.addEventListener('change', handlePageSizeChange);
        }

        if (elements.prevBtn) {
            elements.prevBtn.addEventListener('click', () => changePage(state.page - 1));
        }

        if (elements.nextBtn) {
            elements.nextBtn.addEventListener('click', () => changePage(state.page + 1));
        }

        if (elements.filterForm) {
            elements.filterForm.addEventListener('change', handleFiltersChanged);
        }

        if (elements.searchInput) {
            elements.searchInput.addEventListener('input', debounce(handleFiltersChanged, 300));
        }

        if (elements.reset) {
            elements.reset.addEventListener('click', resetFilters);
        }

        if (elements.viewAllButton) {
            elements.viewAllButton.addEventListener('click', () => {
                const table = elements.tableBody;
                if (table) {
                    table.closest('#feedback-explorer')?.scrollIntoView({ behavior: 'smooth' });
                }
            });
        }
    }

    async function handleRefresh() {
        await Promise.all([
            fetchStats(),
            fetchList()
        ]);
    }

    function populateFiltersFromState() {
        const filters = state.filters || {};
        if (elements.ratingFilter) {
            elements.ratingFilter.value = filters.rating || '';
        }
        if (elements.statusFilter) {
            elements.statusFilter.value = filters.status || '';
        }
        if (elements.categoryFilter) {
            elements.categoryFilter.value = filters.category || '';
        }
        if (elements.typeFilter) {
            elements.typeFilter.value = filters.feedback_type || '';
        }
        if (elements.dateFrom) {
            elements.dateFrom.value = filters.date_from || '';
        }
        if (elements.dateTo) {
            elements.dateTo.value = filters.date_to || '';
        }
        if (elements.sort) {
            elements.sort.value = state.sort;
        }
        if (elements.pageSize) {
            elements.pageSize.value = String(state.limit);
        }
    }

    function buildRequestParams() {
        const params = new URLSearchParams();
        params.set('page', String(state.page));
        params.set('limit', String(state.limit));
        params.set('sort', state.sort);

        Object.entries(state.filters || {}).forEach(([key, value]) => {
            if (value !== undefined && value !== null && String(value).trim() !== '') {
                params.set(key, String(value));
            }
        });

        return params;
    }

    async function fetchList() {
        setLoading(true);
        try {
            const params = buildRequestParams();
            const response = await fetch(`${ENDPOINTS.list}?${params.toString()}`, { credentials: 'include' });
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            const payload = await response.json();
            if (!payload.success) {
                throw new Error(payload.message || 'Unable to load feedback list');
            }
            state.items = payload.data || [];
            state.filters = payload.filters || state.filters;
            state.sort = payload.sort || state.sort;
            state.page = payload.pagination?.page || state.page;
            state.limit = payload.pagination?.limit || state.limit;
            state.total = payload.pagination?.total || state.total;
            state.totalPages = payload.pagination?.total_pages || state.totalPages;
            renderTable();
            updatePaginationControls();
        } catch (error) {
            console.error('Feedback list error:', error);
            showErrorState(elements.tableBody, 'Unable to load feedback entries.');
        } finally {
            setLoading(false);
        }
    }

    async function fetchStats() {
        try {
            const response = await fetch(ENDPOINTS.stats, { credentials: 'include' });
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            const payload = await response.json();
            if (!payload.success) {
                throw new Error(payload.message || 'Unable to load feedback stats');
            }
            state.summary = payload.summary || state.summary;
            state.distribution = payload.distribution || state.distribution;
            state.categories = payload.categories || state.categories;
            state.recent = payload.recent || state.recent;
            renderStats();
            renderDistribution();
            renderCategories();
            renderRecent();
        } catch (error) {
            console.error('Feedback stats error:', error);
        }
    }

    function handlePageSizeChange(event) {
        const newLimit = parseInt(event.target.value, 10);
        if (!Number.isNaN(newLimit) && newLimit > 0) {
            state.limit = newLimit;
            state.page = 1;
            fetchList();
        }
    }

    function changePage(newPage) {
        if (newPage < 1 || newPage > state.totalPages) {
            return;
        }
        state.page = newPage;
        fetchList();
    }

    function handleFiltersChanged() {
        state.filters = {
            rating: elements.ratingFilter?.value || '',
            status: elements.statusFilter?.value || '',
            category: elements.categoryFilter?.value || '',
            feedback_type: elements.typeFilter?.value || '',
            search: elements.searchInput?.value || '',
            date_from: elements.dateFrom?.value || '',
            date_to: elements.dateTo?.value || ''
        };
        state.sort = elements.sort?.value || state.sort;
        state.page = 1;
        fetchList();
    }

    function resetFilters() {
        state.filters = {};
        state.sort = 'newest';
        state.page = 1;
        populateFiltersFromState();
        fetchList();
    }

    function renderAll() {
        renderStats();
        renderDistribution();
        renderCategories();
        renderRecent();
        renderTable();
        updatePaginationControls();
    }

    function renderStats() {
        const summary = state.summary || {};
        updateText(elements.stats.averageRating, formatAverageRating(summary.average_rating));
        updateText(elements.stats.totalReviews, formatNumber(summary.total_reviews));
        updateText(elements.stats.pendingResponse, formatNumber(summary.pending_response));
        updateText(elements.stats.responseRate, formatPercent(summary.response_rate));
        updateText(elements.stats.satisfactionRate, formatPercent(summary.satisfaction_rate));
        updateText(elements.stats.complaints, formatNumber(summary.complaints));
        updateText(elements.stats.resolved, formatNumber(summary.resolved));
        updateTableCount();
    }

    function renderDistribution() {
        const container = elements.distribution;
        if (!container) {
            return;
        }
        const data = state.distribution || [];
        if (!data.length) {
            container.innerHTML = '<p class="text-sm text-gray-500">No ratings recorded yet.</p>';
            return;
        }
        container.innerHTML = data.map(item => {
            const rating = Number(item.rating || 0);
            const count = Number(item.count || 0);
            const percentage = clamp(Number(item.percentage || 0), 0, 100);
            return `
                <div class="flex items-center gap-3">
                    <div class="w-12 text-sm font-semibold text-gray-600">${rating}★</div>
                    <div class="flex-1">
                        <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-2 bg-blue-500 rounded-full" style="width: ${percentage}%"></div>
                        </div>
                    </div>
                    <div class="w-12 text-right text-sm text-gray-500">${formatNumber(count)}</div>
                </div>
            `;
        }).join('');
    }

    function renderCategories() {
        const body = elements.categoriesBody;
        if (!body) {
            return;
        }
        const data = state.categories || [];
        if (!data.length) {
            body.innerHTML = '<tr><td colspan="4" class="py-4 text-center text-gray-500">No category insights available yet.</td></tr>';
            return;
        }
        body.innerHTML = data.map(item => {
            const label = capitalize((item.category || 'other').replace(/_/g, ' '));
            const count = formatNumber(item.count);
            const share = item.percentage !== null && item.percentage !== undefined ? `${Number(item.percentage).toFixed(1)}%` : '—';
            const average = item.average_rating !== null && item.average_rating !== undefined ? Number(item.average_rating).toFixed(1) : '—';
            return `
                <tr class="border-b last:border-b-0 border-gray-100">
                    <td class="py-2 font-medium text-gray-800">${escapeHtml(label)}</td>
                    <td class="py-2 text-right">${count}</td>
                    <td class="py-2 text-right">${share}</td>
                    <td class="py-2 text-right">${average}</td>
                </tr>
            `;
        }).join('');
    }

    function renderRecent() {
        const container = elements.recentList;
        if (!container) {
            return;
        }
        const data = state.recent || [];
        if (!data.length) {
            container.innerHTML = '<p class="text-sm text-gray-500">We haven’t received any guest feedback yet. Encourage guests to share their experience.</p>';
            return;
        }
        container.innerHTML = data.map(item => {
            const rating = item.rating !== null && item.rating !== undefined ? Number(item.rating) : null;
            const guest = escapeHtml(item.guest_name || 'Guest');
            const room = item.room_number ? ` • Room ${escapeHtml(item.room_number)}` : '';
            const comments = escapeHtml(truncate(item.comments || '', 160));
            const createdAt = item.created_at ? formatDateTime(item.created_at) : 'Recently';
            const stars = rating !== null ? renderStars(rating) : '<span class="text-xs text-gray-400">No rating</span>';
            return `
                <article class="border border-gray-100 rounded-lg p-4 hover:border-blue-200 transition group">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2 text-sm text-gray-600">
                                <div class="flex text-yellow-400">${stars}</div>
                                <span class="font-medium text-gray-700">${guest}</span>
                                <span class="text-gray-300">${room}</span>
                            </div>
                            <p class="mt-2 text-sm text-gray-700 leading-relaxed">“${comments}”</p>
                        </div>
                        <div class="text-xs text-gray-400 whitespace-nowrap">${escapeHtml(createdAt)}</div>
                    </div>
                </article>
            `;
        }).join('');
    }

    function renderTable() {
        const body = elements.tableBody;
        if (!body) {
            return;
        }
        const rows = state.items || [];
        if (!rows.length) {
            body.innerHTML = `
                <tr>
                    <td colspan="8" class="px-6 py-10 text-center text-gray-500">
                        <div class="flex flex-col items-center gap-3">
                            <i class="fas fa-comments text-2xl text-gray-300"></i>
                            <p class="font-medium">No feedback captured yet</p>
                            <p class="text-sm">Guest submissions will appear here once available.</p>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }
        body.innerHTML = rows.map(renderRow).join('');
    }

    function renderRow(row) {
        const guest = row.guest || {};
        const reservation = row.reservation || {};
        const statusInfo = getStatusBadge(row.status);
        const rating = row.rating !== null && row.rating !== undefined ? Number(row.rating) : null;
        const createdAt = row.created_at ? formatDateTime(row.created_at, { short: true }) : '—';
        const categoryLabel = capitalize((row.category || 'other').replace(/_/g, ' '));
        const typeLabel = capitalize((row.feedback_type || 'general').replace(/_/g, ' '));
        const commentPreview = escapeHtml(truncate(row.comments || '', 120));
        const room = reservation.room_number ? `<span class="block text-xs text-gray-400 mt-1">Room ${escapeHtml(reservation.room_number)}</span>` : '';
        const stars = rating !== null ? renderStars(rating, true) + `<span class="ml-2 text-gray-500">${rating.toFixed(1)}</span>` : '<span class="text-gray-400">No rating</span>';

        return `
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 text-white flex items-center justify-center font-semibold">
                            ${escapeHtml((guest.initials || 'GF').toUpperCase())}
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">${escapeHtml(guest.name || 'Guest')}</p>
                            ${guest.email ? `<p class="text-xs text-gray-500">${escapeHtml(guest.email)}</p>` : ''}
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center text-yellow-400 text-sm">${stars}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-700">${escapeHtml(categoryLabel)}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">${escapeHtml(typeLabel)}</td>
                <td class="px-6 py-4 text-sm text-gray-700">
                    ${commentPreview}
                    ${room}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${statusInfo.class}">${escapeHtml(statusInfo.label)}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${escapeHtml(createdAt)}</td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <button type="button" class="text-blue-600 hover:text-blue-800 feedback-view" data-feedback-id="${Number(row.id)}">View</button>
                </td>
            </tr>
        `;
    }

    function updatePaginationControls() {
        if (elements.prevBtn) {
            elements.prevBtn.disabled = state.page <= 1;
        }
        if (elements.nextBtn) {
            elements.nextBtn.disabled = state.page >= state.totalPages;
        }
        updateText(elements.tableCount, `Showing ${Math.min(state.page * state.limit, state.total)} of ${formatNumber(state.total)} feedback entries`);
    }

    function updateTableCount() {
        updateText(elements.tableCount, `Showing ${Math.min(state.page * state.limit, state.total)} of ${formatNumber(state.total)} feedback entries`);
    }

    function setLoading(isLoading) {
        state.isLoading = isLoading;
        if (!elements.tableBody) {
            return;
        }
        if (isLoading) {
            elements.tableBody.innerHTML = `
                <tr>
                    <td colspan="8" class="px-6 py-10 text-center text-sm text-gray-500">
                        <i class="fas fa-spinner fa-spin mr-2"></i>Loading feedback entries...
                    </td>
                </tr>
            `;
        }
    }

    function getStatusBadge(status) {
        switch (status) {
            case 'resolved':
                return { class: 'bg-green-100 text-green-800', label: 'Resolved' };
            case 'in_progress':
                return { class: 'bg-amber-100 text-amber-800', label: 'In Progress' };
            default:
                return { class: 'bg-blue-100 text-blue-800', label: 'New' };
        }
    }

    function renderStars(rating, solidOnly = false) {
        let stars = '';
        for (let i = 1; i <= 5; i += 1) {
            const filled = i <= rating;
            stars += `<i class="${filled ? 'fas' : (solidOnly ? 'fas' : 'far')} fa-star"></i>`;
        }
        return stars;
    }

    function updateText(element, value) {
        if (element && value !== undefined && value !== null) {
            element.textContent = value;
        }
    }

    function showErrorState(container, message) {
        if (!container) {
            return;
        }
        container.innerHTML = `
            <tr>
                <td colspan="8" class="px-6 py-10 text-center text-red-500">
                    <i class="fas fa-exclamation-triangle mr-2"></i>${escapeHtml(message)}
                </td>
            </tr>
        `;
    }

    function formatAverageRating(value) {
        if (value === null || value === undefined) {
            return '—';
        }
        const numberValue = Number(value);
        if (Number.isNaN(numberValue)) {
            return '—';
        }
        return `${numberValue.toFixed(1)}/5`;
    }

    function formatNumber(value) {
        const numberValue = Number(value || 0);
        return numberValue.toLocaleString('en-US');
    }

    function formatPercent(value) {
        if (value === null || value === undefined) {
            return '0.0%';
        }
        const numberValue = Number(value);
        if (Number.isNaN(numberValue)) {
            return '0.0%';
        }
        return `${numberValue.toFixed(1)}%`;
    }

    function formatDateTime(dateString, options = {}) {
        const date = new Date(dateString);
        if (Number.isNaN(date.getTime())) {
            return '—';
        }
        if (options.short) {
            return date.toLocaleString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
        return date.toLocaleString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function truncate(value, length) {
        if (!value) {
            return '';
        }
        const str = String(value);
        return str.length > length ? `${str.slice(0, length - 1)}…` : str;
    }

    function capitalize(value) {
        if (!value) {
            return '';
        }
        return value.charAt(0).toUpperCase() + value.slice(1);
    }

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function clamp(value, min, max) {
        return Math.max(min, Math.min(max, value));
    }

    function debounce(fn, delay) {
        let timeoutId;
        return (...args) => {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => fn(...args), delay);
        };
    }
})();
