// Audit Log dashboard interactivity
// Handles filtering, pagination, and exporting audit log data

(function () {
    const API_ENDPOINT = '../../api/get-audit-logs.php';

    const state = {
        page: 1,
        limit: 25,
        total: 0,
        totalPages: 1,
        filters: {
            action: '',
            date_from: '',
            date_to: '',
            search: ''
        },
        logs: [],
        summary: {
            total_entries: 0,
            today_entries: 0,
            security_events: 0,
            active_users: 0
        },
        actions: []
    };

    const elements = {};
    let searchDebounce;

    document.addEventListener('DOMContentLoaded', initialize);

    function initialize() {
        cacheElements();
        if (!elements.tableBody) {
            return;
        }

        hydrateBootstrap();
        bindEvents();
        render();
    }

    function cacheElements() {
        elements.tableBody = document.getElementById('audit-table-body');
        elements.summaryTotal = document.getElementById('audit-total-entries');
        elements.summaryToday = document.getElementById('audit-today-entries');
        elements.summarySecurity = document.getElementById('audit-security-events');
        elements.summaryUsers = document.getElementById('audit-active-users');
        elements.paginationSummary = document.getElementById('audit-pagination-summary');
        elements.currentPage = document.getElementById('audit-current-page');
        elements.totalPages = document.getElementById('audit-total-pages');
        elements.prevBtn = document.getElementById('audit-prev-page');
        elements.nextBtn = document.getElementById('audit-next-page');
        elements.limitSelect = document.getElementById('audit-limit-select');
        elements.actionFilter = document.getElementById('audit-action-filter');
        elements.dateFrom = document.getElementById('audit-date-from');
        elements.dateTo = document.getElementById('audit-date-to');
        elements.searchInput = document.getElementById('audit-search');
        elements.exportBtn = document.getElementById('audit-export');
    }

    function hydrateBootstrap() {
        const bootstrap = window.auditLogBootstrap || {};
        if (bootstrap.pagination) {
            state.page = bootstrap.pagination.page || 1;
            state.limit = bootstrap.pagination.limit || 25;
            state.total = bootstrap.pagination.total || 0;
            state.totalPages = bootstrap.pagination.total_pages || 1;
        }
        state.logs = Array.isArray(bootstrap.logs) ? bootstrap.logs : [];
        state.summary = bootstrap.summary || state.summary;
        state.actions = Array.isArray(bootstrap.actions) ? bootstrap.actions : [];

        if (elements.limitSelect) {
            elements.limitSelect.value = state.limit;
        }
    }

    function bindEvents() {
        if (elements.prevBtn) {
            elements.prevBtn.addEventListener('click', (event) => {
                event.preventDefault();
                if (state.page > 1) {
                    state.page -= 1;
                    fetchLogs();
                }
            });
        }

        if (elements.nextBtn) {
            elements.nextBtn.addEventListener('click', (event) => {
                event.preventDefault();
                if (state.page < state.totalPages) {
                    state.page += 1;
                    fetchLogs();
                }
            });
        }

        if (elements.limitSelect) {
            elements.limitSelect.addEventListener('change', () => {
                state.limit = parseInt(elements.limitSelect.value, 10) || 25;
                state.page = 1;
                fetchLogs();
            });
        }

        if (elements.actionFilter) {
            elements.actionFilter.addEventListener('change', () => {
                state.filters.action = elements.actionFilter.value;
                state.page = 1;
                fetchLogs();
            });
        }

        if (elements.dateFrom) {
            elements.dateFrom.addEventListener('change', () => {
                state.filters.date_from = elements.dateFrom.value;
                state.page = 1;
                fetchLogs();
            });
        }

        if (elements.dateTo) {
            elements.dateTo.addEventListener('change', () => {
                state.filters.date_to = elements.dateTo.value;
                state.page = 1;
                fetchLogs();
            });
        }

        if (elements.searchInput) {
            elements.searchInput.addEventListener('input', () => {
                clearTimeout(searchDebounce);
                searchDebounce = setTimeout(() => {
                    state.filters.search = elements.searchInput.value.trim();
                    state.page = 1;
                    fetchLogs();
                }, 350);
            });
        }

        if (elements.exportBtn) {
            elements.exportBtn.addEventListener('click', handleExport);
        }
    }

    function fetchLogs() {
        if (!elements.tableBody) {
            return;
        }

        setLoading(true);

        const params = new URLSearchParams({
            page: String(state.page),
            limit: String(state.limit),
        });

        Object.entries(state.filters).forEach(([key, value]) => {
            if (value) {
                params.append(key, value);
            }
        });

        fetch(`${API_ENDPOINT}?${params.toString()}`, { credentials: 'include' })
            .then((response) => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }
                return response.json();
            })
            .then((payload) => {
                if (!payload.success) {
                    throw new Error(payload.message || 'Failed to load audit logs');
                }

                state.logs = Array.isArray(payload.logs) ? payload.logs : [];
                const pagination = payload.pagination || {};
                state.page = pagination.page || state.page;
                state.limit = pagination.limit || state.limit;
                state.total = pagination.total || state.total;
                state.totalPages = pagination.total_pages || state.totalPages;
                state.summary = payload.summary || state.summary;
                state.actions = Array.isArray(payload.actions) ? payload.actions : state.actions;

                render();
            })
            .catch((error) => {
                console.error('Audit log fetch error:', error);
                showError('Unable to load audit logs.');
            })
            .finally(() => {
                setLoading(false);
            });
    }

    function render() {
        renderSummary();
        renderActions();
        renderTable();
        renderPagination();
    }

    function renderSummary() {
        if (elements.summaryTotal) {
            elements.summaryTotal.textContent = formatNumber(state.summary.total_entries || 0);
        }
        if (elements.summaryToday) {
            elements.summaryToday.textContent = formatNumber(state.summary.today_entries || 0);
        }
        if (elements.summarySecurity) {
            elements.summarySecurity.textContent = formatNumber(state.summary.security_events || 0);
        }
        if (elements.summaryUsers) {
            elements.summaryUsers.textContent = formatNumber(state.summary.active_users || 0);
        }
    }

    function renderActions() {
        if (!elements.actionFilter) {
            return;
        }

        const current = state.filters.action;
        const options = [''];
        state.actions.forEach((action) => {
            if (typeof action === 'string') {
                options.push(action);
            }
        });

        elements.actionFilter.innerHTML = options
            .map((value) => {
                const label = value ? toTitleCase(value.replace(/_/g, ' ')) : 'All Actions';
                const selected = value === current ? 'selected' : '';
                return `<option value="${escapeHtml(value)}" ${selected}>${escapeHtml(label)}</option>`;
            })
            .join('');
    }

    function renderTable() {
        if (!elements.tableBody) {
            return;
        }

        if (!state.logs.length) {
            elements.tableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="px-6 py-6 text-center text-sm text-gray-500">No audit logs match the current filters.</td>
                </tr>
            `;
            return;
        }

        elements.tableBody.innerHTML = state.logs
            .map((log) => {
                const createdAt = log.created_at ? formatDateTime(log.created_at) : '—';
                const name = log.user_name || 'System';
                const initials = getInitials(name);
                const roleLabel = log.user_role ? toTitleCase(log.user_role.replace(/_/g, ' ')) : '—';
                const actionLabel = log.action ? toTitleCase(log.action.replace(/_/g, ' ')) : '—';
                const details = log.details || '—';
                const ip = log.ip_address || '—';
                const isAlert = isAlertAction(log.action);
                const statusLabel = isAlert ? 'Attention' : 'Success';
                const statusClass = isAlert ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700';

                return `
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${escapeHtml(createdAt)}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-9 w-9">
                                    <div class="h-9 w-9 rounded-full bg-primary/10 text-primary flex items-center justify-center text-xs font-semibold">
                                        ${escapeHtml(initials)}
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">${escapeHtml(name)}</div>
                                    <div class="text-xs text-gray-500">${escapeHtml(roleLabel)}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${escapeHtml(actionLabel)}</td>
                        <td class="px-6 py-4 text-sm text-gray-600 max-w-xs">
                            <span class="line-clamp-2" title="${escapeHtml(details)}">${escapeHtml(details)}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${escapeHtml(ip)}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass}">${statusLabel}</span>
                        </td>
                    </tr>
                `;
            })
            .join('');
    }

    function renderPagination() {
        if (elements.currentPage) {
            elements.currentPage.textContent = String(state.page);
        }
        if (elements.totalPages) {
            elements.totalPages.textContent = String(state.totalPages);
        }
        if (elements.paginationSummary) {
            const start = state.total === 0 ? 0 : (state.page - 1) * state.limit + 1;
            const end = Math.min(state.page * state.limit, state.total);
            elements.paginationSummary.textContent = `Showing ${formatNumber(start)}-${formatNumber(end)} of ${formatNumber(state.total)} entries`;
        }
        if (elements.prevBtn) {
            elements.prevBtn.disabled = state.page <= 1;
        }
        if (elements.nextBtn) {
            elements.nextBtn.disabled = state.page >= state.totalPages;
        }
    }

    function setLoading(isLoading) {
        if (!elements.tableBody) {
            return;
        }
        if (isLoading) {
            elements.tableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="px-6 py-6 text-center text-sm text-gray-500">
                        <i class="fas fa-spinner fa-spin mr-2"></i>Loading audit logs...
                    </td>
                </tr>
            `;
        }
    }

    function handleExport() {
        const rows = state.logs;
        if (!rows.length) {
            showError('Nothing to export for the current filters.');
            return;
        }

        const header = ['Timestamp', 'User', 'Role', 'Action', 'Details', 'IP Address'];
        const csvRows = [header];

        rows.forEach((log) => {
            csvRows.push([
                log.created_at || '',
                log.user_name || 'System',
                log.user_role ? toTitleCase(log.user_role.replace(/_/g, ' ')) : '',
                log.action || '',
                (log.details || '').replace(/"/g, '""'),
                log.ip_address || ''
            ]);
        });

        const csvContent = csvRows
            .map((row) => row.map((value) => `"${value}"`).join(','))
            .join('\n');

        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `audit-log-export-${new Date().toISOString().slice(0, 10)}.csv`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);

        showSuccess('Audit log exported successfully.');
    }

    function isAlertAction(action) {
        if (!action) {
            return false;
        }
        const lower = action.toLowerCase();
        return lower.includes('fail') || lower.includes('error') || lower.includes('denied') || lower.includes('warning');
    }

    function getInitials(name) {
        if (!name) {
            return 'S';
        }
        const parts = name.trim().split(/\s+/);
        if (parts.length === 1) {
            return parts[0].slice(0, 1).toUpperCase();
        }
        return (parts[0].slice(0, 1) + parts[parts.length - 1].slice(0, 1)).toUpperCase();
    }

    function formatDateTime(value) {
        try {
            const date = new Date(value);
            return date.toLocaleString();
        } catch (error) {
            return value;
        }
    }

    function formatNumber(value) {
        return Number(value || 0).toLocaleString();
    }

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function toTitleCase(text) {
        return text.replace(/\w\S*/g, (word) => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase());
    }

    function showError(message) {
        if (window.HotelPMS && window.HotelPMS.Utils && typeof window.HotelPMS.Utils.showNotification === 'function') {
            window.HotelPMS.Utils.showNotification(message, 'error');
        }
    }

    function showSuccess(message) {
        if (window.HotelPMS && window.HotelPMS.Utils && typeof window.HotelPMS.Utils.showNotification === 'function') {
            window.HotelPMS.Utils.showNotification(message, 'success');
        }
    }
})();
