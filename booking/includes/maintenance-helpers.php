<?php
/**
 * Maintenance helper functions
 */

require_once __DIR__ . '/config.php';

function getMaintenanceRequests(array $options = []): array
{
    global $pdo;

    $limit = isset($options['limit']) ? max(1, min(200, (int)$options['limit'])) : 25;
    $offset = max(0, (int)($options['offset'] ?? 0));
    $applyLimit = !array_key_exists('no_limit', $options) || $options['no_limit'] === false;

    $conditions = [];
    $params = [];

    if (!empty($options['status'])) {
        $conditions[] = 'mr.status = :status';
        $params[':status'] = $options['status'];
    }

    if (!empty($options['priority'])) {
        $conditions[] = 'mr.priority = :priority';
        $params[':priority'] = $options['priority'];
    }

    if (!empty($options['assigned_to'])) {
        $conditions[] = 'mr.assigned_to = :assigned_to';
        $params[':assigned_to'] = (int)$options['assigned_to'];
    }

    if (!empty($options['search'])) {
        $conditions[] = '(
            mr.description LIKE :search
            OR mr.issue_type LIKE :search
            OR r.room_number LIKE :search
            OR u_reporter.name LIKE :search
            OR u_assigned.name LIKE :search
        )';
        $params[':search'] = '%' . $options['search'] . '%';
    }

    if (!empty($options['date_from'])) {
        $dateFrom = DateTimeImmutable::createFromFormat('Y-m-d', $options['date_from']);
        if ($dateFrom instanceof DateTimeImmutable) {
            $conditions[] = 'mr.created_at >= :date_from';
            $params[':date_from'] = $dateFrom->format('Y-m-d 00:00:00');
        }
    }

    if (!empty($options['date_to'])) {
        $dateTo = DateTimeImmutable::createFromFormat('Y-m-d', $options['date_to']);
        if ($dateTo instanceof DateTimeImmutable) {
            $conditions[] = 'mr.created_at <= :date_to';
            $params[':date_to'] = $dateTo->format('Y-m-d 23:59:59');
        }
    }

    $query = "
        SELECT
            mr.id,
            mr.room_id,
            r.room_number,
            mr.issue_type,
            mr.priority,
            mr.status,
            mr.description,
            mr.reported_by,
            u_reporter.name AS reported_by_name,
            mr.assigned_to,
            u_assigned.name AS assigned_to_name,
            mr.created_at,
            mr.updated_at,
            mr.updated_at as completed_at,
            '' as notes
        FROM maintenance_requests mr
        LEFT JOIN rooms r ON mr.room_id = r.id
        LEFT JOIN users u_reporter ON mr.reported_by = u_reporter.id
        LEFT JOIN users u_assigned ON mr.assigned_to = u_assigned.id
    ";

    if ($conditions) {
        $query .= ' WHERE ' . implode(' AND ', $conditions);
    }

    $query .= ' ORDER BY mr.created_at DESC';

    if ($applyLimit) {
        $query .= ' LIMIT :limit OFFSET :offset';
    }

    try {
        $stmt = $pdo->prepare($query);

        foreach ($params as $placeholder => $value) {
            $stmt->bindValue($placeholder, $value);
        }

        if ($applyLimit) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (PDOException $e) {
        error_log('Maintenance request fetch error: ' . $e->getMessage());
        return [];
    }
}

function countMaintenanceRequests(array $options = []): int
{
    global $pdo;

    $conditions = [];
    $params = [];

    if (!empty($options['status'])) {
        $conditions[] = 'mr.status = :status';
        $params[':status'] = $options['status'];
    }

    if (!empty($options['priority'])) {
        $conditions[] = 'mr.priority = :priority';
        $params[':priority'] = $options['priority'];
    }

    if (!empty($options['assigned_to'])) {
        $conditions[] = 'mr.assigned_to = :assigned_to';
        $params[':assigned_to'] = (int)$options['assigned_to'];
    }

    if (!empty($options['search'])) {
        $conditions[] = '(
            mr.description LIKE :search
            OR mr.issue_type LIKE :search
            OR r.room_number LIKE :search
            OR u_reporter.name LIKE :search
            OR u_assigned.name LIKE :search
        )';
        $params[':search'] = '%' . $options['search'] . '%';
    }

    if (!empty($options['date_from'])) {
        $dateFrom = DateTimeImmutable::createFromFormat('Y-m-d', $options['date_from']);
        if ($dateFrom instanceof DateTimeImmutable) {
            $conditions[] = 'mr.created_at >= :date_from';
            $params[':date_from'] = $dateFrom->format('Y-m-d 00:00:00');
        }
    }

    if (!empty($options['date_to'])) {
        $dateTo = DateTimeImmutable::createFromFormat('Y-m-d', $options['date_to']);
        if ($dateTo instanceof DateTimeImmutable) {
            $conditions[] = 'mr.created_at <= :date_to';
            $params[':date_to'] = $dateTo->format('Y-m-d 23:59:59');
        }
    }

    $query = 'SELECT COUNT(*) FROM maintenance_requests mr LEFT JOIN rooms r ON mr.room_id = r.id LEFT JOIN users u_reporter ON mr.reported_by = u_reporter.id LEFT JOIN users u_assigned ON mr.assigned_to = u_assigned.id';
    if ($conditions) {
        $query .= ' WHERE ' . implode(' AND ', $conditions);
    }

    try {
        $stmt = $pdo->prepare($query);
        foreach ($params as $placeholder => $value) {
            $stmt->bindValue($placeholder, $value);
        }
        $stmt->execute();

        return (int)($stmt->fetchColumn() ?: 0);
    } catch (PDOException $e) {
        error_log('Maintenance request count error: ' . $e->getMessage());
        return 0;
    }
}

function getMaintenanceSummary(): array
{
    global $pdo;

    $summary = [
        'active' => 0,
        'completed_today' => 0,
        'urgent' => 0,
        'average_completion_minutes' => 0,
    ];

    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM maintenance_requests WHERE status IN ('reported', 'assigned', 'in_progress')");
        $summary['active'] = (int)($stmt->fetchColumn() ?: 0);

        $stmt = $pdo->query("SELECT COUNT(*) FROM maintenance_requests WHERE status = 'completed' AND DATE(updated_at) = CURDATE()");
        $summary['completed_today'] = (int)($stmt->fetchColumn() ?: 0);

        $stmt = $pdo->query("SELECT COUNT(*) FROM maintenance_requests WHERE priority = 'urgent' AND status != 'completed'");
        $summary['urgent'] = (int)($stmt->fetchColumn() ?: 0);

        $stmt = $pdo->query("SELECT AVG(TIMESTAMPDIFF(MINUTE, created_at, updated_at)) FROM maintenance_requests WHERE status = 'completed' AND updated_at > created_at");
        $summary['average_completion_minutes'] = (float)round($stmt->fetchColumn() ?: 0, 1);
    } catch (PDOException $e) {
        error_log('Maintenance summary error: ' . $e->getMessage());
    }

    return $summary;
}

function getMaintenanceAssignees(): array
{
    global $pdo;

    try {
        $stmt = $pdo->query("SELECT id, name, role FROM users WHERE role IN ('maintenance', 'housekeeping', 'manager') ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (PDOException $e) {
        error_log('Maintenance assignee fetch error: ' . $e->getMessage());
        return [];
    }
}

function updateMaintenanceRequest(int $requestId, array $data): array
{
    global $pdo;

    $fields = [];
    $params = [];

    if (!empty($data['status'])) {
        $fields[] = 'status = :status';
        $params[':status'] = $data['status'];
    }

    if (array_key_exists('assigned_to', $data)) {
        $fields[] = 'assigned_to = :assigned_to';
        $params[':assigned_to'] = $data['assigned_to'] !== '' ? (int)$data['assigned_to'] : null;
    }

    if (!empty($data['notes'])) {
        $fields[] = 'notes = :notes';
        $params[':notes'] = $data['notes'];
    }

    if (empty($fields)) {
        return ['success' => false, 'message' => 'No changes supplied'];
    }

    $fields[] = 'updated_at = NOW()';
    if (!empty($data['status']) && $data['status'] === 'completed') {
        $fields[] = 'updated_at = NOW()';
    }

    $sql = 'UPDATE maintenance_requests SET ' . implode(', ', $fields) . ' WHERE id = :id';
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':id', $requestId, PDO::PARAM_INT);

    try {
        $stmt->execute();
        logActivity($_SESSION['user_id'] ?? null, 'maintenance_update', "Updated maintenance request {$requestId}");
        return ['success' => true];
    } catch (PDOException $e) {
        error_log('Maintenance update error: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to update maintenance request'];
    }
}
