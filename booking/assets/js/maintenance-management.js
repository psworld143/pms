// Maintenance management dashboard logic
// Handles loading, filtering, creation, and updates of maintenance requests

(function () {
    const API_ENDPOINTS = {
        list: '../../api/get-maintenance-requests.php',
        create: '../../api/create-maintenance-request.php',
        update: '../../api/update-maintenance-request.php'
    };

    const state = {
        page: 1,
        limit: 25,
        total: 0,
        totalPages: 1,
        filters: {
            status: '',
            priority: '',
            assigned_to: '',
            date_from: '',
            date_to: '',
            search: ''
        },
        requests: [],
        summary: {
            active: 0,
            completed_today: 0,
            urgent: 0,
            average_completion_minutes: 0
        },
        assignees: [],
        issueTypes: [],
        priorities: [],
        statuses: []
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
        elements.tableBody = document.getElementById('maintenance-table-body');
        elements.summaryActive = document.getElementById('maintenance-summary-active');
        elements.summaryCompleted = document.getElementById('maintenance-summary-completed-today');
        elements.summaryUrgent = document.getElementById('maintenance-summary-urgent');
        elements.summaryAverage = document.getElementById('maintenance-summary-average');
        elements.limitSelect = document.getElementById('maintenance-limit');
        elements.pageCurrent = document.getElementById('maintenance-page-current');
        elements.pageTotal = document.getElementById('maintenance-page-total');
        elements.pagePrev = document.getElementById('maintenance-page-prev');
        elements.pageNext = document.getElementById('maintenance-page-next');
        elements.refreshBtn = document.getElementById('maintenance-refresh');
        elements.exportBtn = document.getElementById('maintenance-export');

        elements.filterStatus = document.getElementById('maintenance-filter-status');
        elements.filterPriority = document.getElementById('maintenance-filter-priority');
        elements.filterAssignee = document.getElementById('maintenance-filter-assignee');
        elements.filterSearch = document.getElementById('maintenance-filter-search');

        elements.createForm = document.getElementById('maintenance-create-form');
        elements.createRoom = document.getElementById('maintenance-room');
        elements.createIssue = document.getElementById('maintenance-issue');
        elements.createPriority = document.getElementById('maintenance-priority');
        elements.createDescription = document.getElementById('maintenance-description');
    }

    function hydrateBootstrap() {
        const bootstrap = window.maintenanceBootstrap || {};
        state.page = bootstrap.pagination?.page || 1;
        state.limit = bootstrap.pagination?.limit || 25;
        state.total = bootstrap.pagination?.total || 0;
        state.totalPages = bootstrap.pagination?.total_pages || 1;
        state.requests = Array.isArray(bootstrap.requests) ? bootstrap.requests : [];
        state.summary = bootstrap.summary || state.summary;
        state.filters = bootstrap.filters || state.filters;
        state.assignees = Array.isArray(bootstrap.assignees) ? bootstrap.assignees : [];
        state.issueTypes = Array.isArray(bootstrap.issue_types) ? bootstrap.issue_types : [];
        state.priorities = Array.isArray(bootstrap.priorities) ? bootstrap.priorities : [];
        state.statuses = Array.isArray(bootstrap.statuses) ? bootstrap.statuses : [];

        if (elements.limitSelect) {
            elements.limitSelect.value = state.limit;
        }
    }

    function bindEvents() {
        if (elements.pagePrev) {
            elements.pagePrev.addEventListener('click', (event) => {
                event.preventDefault();
                if (state.page > 1) {
                    state.page -= 1;
                    fetchRequests();
                }
            });
        }

        if (elements.pageNext) {
            elements.pageNext.addEventListener('click', (event) => {
                event.preventDefault();
                if (state.page < state.totalPages) {
                    state.page += 1;
                    fetchRequests();
                }
            });
        }

        if (elements.limitSelect) {
            elements.limitSelect.addEventListener('change', () => {
                state.limit = parseInt(elements.limitSelect.value, 10) || 25;
                state.page = 1;
                fetchRequests();
            });
        }

        if (elements.filterStatus) {
            elements.filterStatus.addEventListener('change', () => {
                state.filters.status = elements.filterStatus.value;
                state.page = 1;
                fetchRequests();
            });
        }

        if (elements.filterPriority) {
            elements.filterPriority.addEventListener('change', () => {
                state.filters.priority = elements.filterPriority.value;
                state.page = 1;
                fetchRequests();
            });
        }

        if (elements.filterAssignee) {
            elements.filterAssignee.addEventListener('change', () => {
                state.filters.assigned_to = elements.filterAssignee.value;
                state.page = 1;
                fetchRequests();
            });
        }

        if (elements.filterSearch) {
            elements.filterSearch.addEventListener('input', () => {
                clearTimeout(searchDebounce);
                searchDebounce = setTimeout(() => {
                    state.filters.search = elements.filterSearch.value.trim();
                    state.page = 1;
                    fetchRequests();
                }, 350);
            });
        }

        if (elements.refreshBtn) {
            elements.refreshBtn.addEventListener('click', (event) => {
                event.preventDefault();
                fetchRequests();
            });
        }

        if (elements.exportBtn) {
            elements.exportBtn.addEventListener('click', handleExport);
        }

        if (elements.createForm) {
            elements.createForm.addEventListener('submit', handleCreateSubmit);
        }

        if (elements.tableBody) {
            elements.tableBody.addEventListener('click', handleTableClick);
        }
    }

    function fetchRequests() {
        if (!elements.tableBody) {
            return;
        }

        setLoading(true);

        const params = new URLSearchParams({
            page: String(state.page),
            limit: String(state.limit)
        });

        Object.entries(state.filters).forEach(([key, value]) => {
            if (value) {
                params.append(key, value);
            }
        });

        fetch(`${API_ENDPOINTS.list}?${params.toString()}`, { credentials: 'include' })
            .then((response) => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }
                return response.json();
            })
            .then((payload) => {
                if (!payload.success) {
                    throw new Error(payload.message || 'Failed to load maintenance requests');
                }

                state.requests = Array.isArray(payload.requests) ? payload.requests : [];
                const pagination = payload.pagination || {};
                state.page = pagination.page || state.page;
                state.limit = pagination.limit || state.limit;
                state.total = pagination.total || state.total;
                state.totalPages = pagination.total_pages || state.totalPages;
                state.summary = payload.summary || state.summary;
                state.assignees = Array.isArray(payload.assignees) ? payload.assignees : state.assignees;

                render();
            })
            .catch((error) => {
                console.error('Maintenance fetch error:', error);
                showError('Unable to load maintenance requests.');
            })
            .finally(() => {
                setLoading(false);
            });
    }

    function render() {
        renderSummary();
        renderTable();
        renderPagination();
    }

    function renderSummary() {
        if (elements.summaryActive) {
            elements.summaryActive.textContent = formatNumber(state.summary.active || 0);
        }
        if (elements.summaryCompleted) {
            elements.summaryCompleted.textContent = formatNumber(state.summary.completed_today || 0);
        }
        if (elements.summaryUrgent) {
            elements.summaryUrgent.textContent = formatNumber(state.summary.urgent || 0);
        }
        if (elements.summaryAverage) {
            elements.summaryAverage.textContent = formatNumber(state.summary.average_completion_minutes || 0, 1);
        }
    }

    function renderTable() {
        if (!elements.tableBody) {
            return;
        }

        if (!state.requests.length) {
            elements.tableBody.innerHTML = '
                <tr>
                    <td colspan="4" class="px-6 py-6 text-center text-sm text-gray-500">No maintenance requests match the current filters.</td>
                </tr>
            ';
            return;
        }

        elements.tableBody.innerHTML = state.requests
            .map((request) => renderRequestRow(request))
            .join('');
    }

    function renderRequestRow(request) {
        const roomLabel = request.room_number ? `Room ${request.room_number}` : `Room #${request.room_id}`;
        const issueLabel = request.issue_type ? toTitleCase(request.issue_type.replace(/_/g, ' ')) : 'Maintenance';
        const priorityLabel = request.priority ? toTitleCase(request.priority) : 'Medium';
        const statusLabel = request.status ? toTitleCase(request.status.replace(/_/g, ' ')) : 'Reported';
        const createdAt = request.created_at ? formatDateTime(request.created_at) : '—';
        const reportedBy = request.reported_by_name || 'System';
        const notes = request.notes || '';

        const priorityClass = {
            urgent: 'bg-red-100 text-red-700',
            high: 'bg-amber-100 text-amber-700',
            low: 'bg-blue-100 text-blue-700'
        }[request.priority] || 'bg-green-100 text-green-700';

        const assigneeOptions = ['<option value="">Unassigned</option>']
            .concat(state.assignees.map((assignee) => {
                const selected = Number(assignee.id) === Number(request.assigned_to) ? 'selected' : '';
                const roleLabel = toTitleCase((assignee.role || '').replace(/_/g, ' '));
                return `<option value="${assignee.id}" ${selected}>${escapeHtml(assignee.name)} (${escapeHtml(roleLabel)})</option>`;
            }))
            .join('');

        const statusOptions = (state.statuses.length ? state.statuses : ['reported', 'assigned', 'in_progress', 'completed'])
            .map((status) => {
                const label = toTitleCase(status.replace(/_/g, ' '));
                const selected = status === request.status ? 'selected' : '';
                return `<option value="${status}" ${selected}>${escapeHtml(label)}</option>`;
            })
            .join('');

        return `
            <tr data-request-id="${Number(request.id)}">
                <td class="px-6 py-4 align-top text-sm text-gray-700">
                    <div class="font-semibold text-gray-900">${escapeHtml(roomLabel)} &middot; ${escapeHtml(issueLabel)}</div>
                    <div class="text-xs text-gray-500 mb-1 flex flex-wrap gap-2">
                        <span class="px-2 py-0.5 rounded-full text-xs ${priorityClass}">${escapeHtml(priorityLabel)}</span>
                        <span class="text-gray-500">Reported by: ${escapeHtml(reportedBy)}</span>
                        <span class="text-gray-400">${escapeHtml(createdAt)}</span>
                    </div>
                    <div class="text-sm text-gray-600">${escapeHtml(request.description || '—').replace(/\n/g, '<br>')}</div>
                    ${notes ? `<div class="mt-2 text-xs text-gray-500">Notes: ${escapeHtml(notes).replace(/\n/g, '<br>')}</div>` : ''}
                </td>
                <td class="px-6 py-4 align-top text-sm text-gray-700">
                    <select class="maintenance-assignee-select w-full border border-gray-300 rounded-md text-sm px-2 py-2 focus:outline-none focus:ring-2 focus:ring-primary">
                        ${assigneeOptions}
                    </select>
                </td>
                <td class="px-6 py-4 align-top text-sm text-gray-700">
                    <select class="maintenance-status-select w-full border border-gray-300 rounded-md text-sm px-2 py-2 focus:outline-none focus:ring-2 focus:ring-primary">
                        ${statusOptions}
                    </select>
                </td>
                <td class="px-6 py-4 align-top text-right text-sm text-gray-600">
                    <button class="maintenance-save-btn inline-flex items-center gap-1 px-3 py-2 bg-primary text-white rounded-md text-sm hover:bg-secondary">
                        <i class="fas fa-save"></i>
                        <span>Save</span>
                    </button>
                </td>
            </tr>
        `;
    }

    function renderPagination() {
        if (elements.pageCurrent) {
            elements.pageCurrent.textContent = String(state.page);
        }
        if (elements.pageTotal) {
            elements.pageTotal.textContent = String(state.totalPages);
        }
        if (elements.pagePrev) {
            elements.pagePrev.disabled = state.page <= 1;
        }
        if (elements.pageNext) {
            elements.pageNext.disabled = state.page >= state.totalPages;
        }
    }

    function setLoading(isLoading) {
        if (!elements.tableBody) {
            return;
        }
        if (isLoading) {
            elements.tableBody.innerHTML = '
                <tr>
                    <td colspan="4" class="px-6 py-6 text-center text-sm text-gray-500">
                        <i class="fas fa-spinner fa-spin mr-2"></i>Loading maintenance requests...
                    </td>
                </tr>
            ';
        }
    }

    function handleTableClick(event) {
        const target = event.target;
        if (!(target instanceof HTMLElement)) {
            return;
        }

        if (target.closest('.maintenance-save-btn')) {
            const row = target.closest('tr[data-request-id]');
            if (!row) {
                return;
            }
            const requestId = Number(row.getAttribute('data-request-id'));
            const assigneeSelect = row.querySelector('.maintenance-assignee-select');
            const statusSelect = row.querySelector('.maintenance-status-select');

            const payload = {
                id: requestId,
                assigned_to: assigneeSelect ? assigneeSelect.value : '',
                status: statusSelect ? statusSelect.value : undefined
            };

            saveRequestUpdate(payload);
        }
    }

    function saveRequestUpdate(payload) {
        fetch(API_ENDPOINTS.update, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'include',
            body: JSON.stringify(payload)
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }
                return response.json();
            })
            .then((result) => {
                if (!result.success) {
                    throw new Error(result.message || 'Update failed');
                }
                showSuccess('Maintenance request updated.');
                fetchRequests();
            })
            .catch((error) => {
                console.error('Maintenance update error:', error);
                showError('Unable to update maintenance request.');
            });
    }

    function handleCreateSubmit(event) {
        event.preventDefault();
        if (!elements.createForm) {
            return;
        }

        const formData = new FormData(elements.createForm);

        fetch(API_ENDPOINTS.create, {
            method: 'POST',
            credentials: 'include',
            body: formData
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }
                return response.json();
            })
            .then((result) => {
                if (!result.success) {
                    throw new Error(result.message || 'Failed to create maintenance request');
                }
                showSuccess('Maintenance request created.');
                elements.createForm.reset();
                fetchRequests();
            })
            .catch((error) => {
                console.error('Maintenance creation error:', error);
                showError('Unable to create maintenance request.');
            });
    }

    function handleExport() {
        if (!state.requests.length) {
            showError('Nothing to export for the current filters.');
            return;
        }

        const headers = ['ID', 'Room', 'Issue', 'Priority', 'Status', 'Reported By', 'Created At', 'Assigned To', 'Notes'];
        const rows = state.requests.map((request) => [
            request.id,
            request.room_number ? `Room ${request.room_number}` : `Room #${request.room_id}`,
            request.issue_type,
            request.priority,
            request.status,
            request.reported_by_name || 'System',
            request.created_at || '',
            request.assigned_to_name || '',
            (request.notes || '').replace(/"/g, '""')
        ]);

        const csvContent = [headers]
            .concat(rows)
            .map((row) => row.map((value) => `"${value}"`).join(','))
            .join('\n');

        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = `maintenance-requests-${new Date().toISOString().slice(0, 10)}.csv`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);

        showSuccess('Maintenance export generated.');
    }

    function formatDateTime(value) {
        try {
            return new Date(value).toLocaleString();
        } catch (error) {
            return value;
        }
    }

    function formatNumber(value, fractionDigits = 0) {
        return Number(value || 0).toLocaleString(undefined, {
            minimumFractionDigits: fractionDigits,
            maximumFractionDigits: fractionDigits
        });
    }

    function toTitleCase(text) {
        return String(text || '').replace(/\w\S*/g, (word) => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase());
    }

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function showError(message) {
        if (window.HotelPMS?.Utils?.showNotification) {
            window.HotelPMS.Utils.showNotification(message, 'error');
        }
    }

    function showSuccess(message) {
        if (window.HotelPMS?.Utils?.showNotification) {
            window.HotelPMS.Utils.showNotification(message, 'success');
        }
    }
})();
