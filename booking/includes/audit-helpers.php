<?php
/**
 * Audit log helper functions
 */

require_once __DIR__ . '/config.php';

function getAuditLogs(array $options = []): array
{
    global $pdo;

    $limit = isset($options['limit']) ? max(1, min(200, (int)$options['limit'])) : 25;
    $offset = max(0, (int)($options['offset'] ?? 0));
    $applyLimit = !array_key_exists('no_limit', $options) || $options['no_limit'] === false;

    $conditions = [];
    $params = [];

    if (!empty($options['action'])) {
        $conditions[] = 'al.action = :action';
        $params[':action'] = $options['action'];
    }

    if (!empty($options['search'])) {
        $conditions[] = '(al.details LIKE :search OR al.action LIKE :search OR u.name LIKE :search OR u.username LIKE :search)';
        $params[':search'] = '%' . $options['search'] . '%';
    }

    if (!empty($options['date_from'])) {
        $dateFrom = DateTimeImmutable::createFromFormat('Y-m-d', $options['date_from']);
        if ($dateFrom instanceof DateTimeImmutable) {
            $conditions[] = 'al.created_at >= :date_from';
            $params[':date_from'] = $dateFrom->format('Y-m-d 00:00:00');
        }
    }

    if (!empty($options['date_to'])) {
        $dateTo = DateTimeImmutable::createFromFormat('Y-m-d', $options['date_to']);
        if ($dateTo instanceof DateTimeImmutable) {
            $conditions[] = 'al.created_at <= :date_to';
            $params[':date_to'] = $dateTo->format('Y-m-d 23:59:59');
        }
    }

    $query = "SELECT al.*, u.name AS user_name, u.username AS user_username, u.role AS user_role
        FROM activity_logs al
        LEFT JOIN users u ON al.user_id = u.id";

    if ($conditions) {
        $query .= ' WHERE ' . implode(' AND ', $conditions);
    }

    $query .= ' ORDER BY al.created_at DESC';

    if ($applyLimit) {
        $query .= ' LIMIT :limit OFFSET :offset';
    }

    $stmt = $pdo->prepare($query);

    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }

    if ($applyLimit) {
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    }

    $stmt->execute();

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    return array_map(static function (array $row): array {
        $row['user_name'] = $row['user_name'] ?? ($row['user_username'] ?? 'System');
        return $row;
    }, $rows);
}

function countAuditLogs(array $options = []): int
{
    global $pdo;

    $conditions = [];
    $params = [];

    if (!empty($options['action'])) {
        $conditions[] = 'al.action = :action';
        $params[':action'] = $options['action'];
    }

    if (!empty($options['search'])) {
        $conditions[] = '(al.details LIKE :search OR al.action LIKE :search OR u.name LIKE :search OR u.username LIKE :search)';
        $params[':search'] = '%' . $options['search'] . '%';
    }

    if (!empty($options['date_from'])) {
        $dateFrom = DateTimeImmutable::createFromFormat('Y-m-d', $options['date_from']);
        if ($dateFrom instanceof DateTimeImmutable) {
            $conditions[] = 'al.created_at >= :date_from';
            $params[':date_from'] = $dateFrom->format('Y-m-d 00:00:00');
        }
    }

    if (!empty($options['date_to'])) {
        $dateTo = DateTimeImmutable::createFromFormat('Y-m-d', $options['date_to']);
        if ($dateTo instanceof DateTimeImmutable) {
            $conditions[] = 'al.created_at <= :date_to';
            $params[':date_to'] = $dateTo->format('Y-m-d 23:59:59');
        }
    }

    $query = 'SELECT COUNT(*) FROM activity_logs al LEFT JOIN users u ON al.user_id = u.id';
    if ($conditions) {
        $query .= ' WHERE ' . implode(' AND ', $conditions);
    }

    $stmt = $pdo->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();

    return (int)($stmt->fetchColumn() ?: 0);
}

function getAuditLogSummary(): array
{
    global $pdo;

    $totalEntries = (int)($pdo->query('SELECT COUNT(*) FROM activity_logs')->fetchColumn() ?: 0);
    $todayEntries = (int)($pdo->query("SELECT COUNT(*) FROM activity_logs WHERE DATE(created_at) = CURDATE()")
        ->fetchColumn() ?: 0);

    $securityActions = ['failed_login', 'security_alert', 'password_change', 'permission_change'];
    $securityEvents = 0;
    if ($securityActions) {
        $placeholders = implode(',', array_fill(0, count($securityActions), '?'));
        $securityStmt = $pdo->prepare("SELECT COUNT(*) FROM activity_logs WHERE action IN ($placeholders)");
        $securityStmt->execute($securityActions);
        $securityEvents = (int)($securityStmt->fetchColumn() ?: 0);
    }

    $activeUsersStmt = $pdo->query("SELECT COUNT(DISTINCT user_id) FROM activity_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
    $activeUsers = (int)($activeUsersStmt->fetchColumn() ?: 0);

    return [
        'total_entries' => $totalEntries,
        'today_entries' => $todayEntries,
        'security_events' => $securityEvents,
        'active_users' => $activeUsers,
    ];
}

function getAuditLogActions(): array
{
    global $pdo;

    try {
        $stmt = $pdo->query('SELECT DISTINCT action FROM activity_logs ORDER BY action ASC');
        $actions = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            if (!empty($row['action'])) {
                $actions[] = (string)$row['action'];
            }
        }
        return $actions;
    } catch (PDOException $e) {
        error_log('Error fetching audit actions: ' . $e->getMessage());
        return [];
    }
}
