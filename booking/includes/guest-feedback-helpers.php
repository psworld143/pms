<?php

declare(strict_types=1);

// Remove unused use statements that cause warnings

if (!function_exists('getGuestFeedbackFilterDefaults')) {
    function getGuestFeedbackFilterDefaults(): array
    {
        return [
            'rating' => null,
            'status' => '',
            'category' => '',
            'feedback_type' => '',
            'search' => '',
            'date_from' => '',
            'date_to' => ''
        ];
    }
}

if (!function_exists('getGuestFeedbackAllowedCategories')) {
    function getGuestFeedbackAllowedCategories(): array
    {
        return ['service', 'cleanliness', 'facilities', 'staff', 'food', 'other'];
    }
}

if (!function_exists('getGuestFeedbackAllowedStatuses')) {
    function getGuestFeedbackAllowedStatuses(): array
    {
        return ['new', 'in_progress', 'resolved'];
    }
}

if (!function_exists('getGuestFeedbackAllowedTypes')) {
    function getGuestFeedbackAllowedTypes(): array
    {
        return ['compliment', 'complaint', 'suggestion', 'general'];
    }
}

if (!function_exists('normalizeGuestFeedbackFilters')) {
    function normalizeGuestFeedbackFilters(array $input): array
    {
        $filters = getGuestFeedbackFilterDefaults();

        if (isset($input['rating'])) {
            $rating = (int)$input['rating'];
            if ($rating >= 1 && $rating <= 5) {
                $filters['rating'] = $rating;
            }
        }

        if (!empty($input['status']) && in_array($input['status'], getGuestFeedbackAllowedStatuses(), true)) {
            $filters['status'] = $input['status'];
        }

        if (!empty($input['category']) && in_array($input['category'], getGuestFeedbackAllowedCategories(), true)) {
            $filters['category'] = $input['category'];
        }

        if (!empty($input['feedback_type']) && in_array($input['feedback_type'], getGuestFeedbackAllowedTypes(), true)) {
            $filters['feedback_type'] = $input['feedback_type'];
        }

        if (!empty($input['search'])) {
            $filters['search'] = trim((string)$input['search']);
        }

        foreach (['date_from', 'date_to'] as $dateKey) {
            if (!empty($input[$dateKey]) && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$input[$dateKey])) {
                $filters[$dateKey] = $input[$dateKey];
            }
        }

        return $filters;
    }
}

if (!function_exists('getGuestFeedbackSummary')) {
    function getGuestFeedbackSummary(PDO $pdo): array
    {
        $defaults = [
            'average_rating' => null,
            'total_reviews' => 0,
            'pending_response' => 0,
            'satisfaction_rate' => 0.0,
            'response_rate' => 0.0,
            'complaints' => 0,
            'resolved' => 0,
        ];

        try {
            $stmt = $pdo->query('
                SELECT
                    AVG(rating) AS average_rating,
                    COUNT(*) AS total_reviews,
                    SUM(CASE WHEN feedback_type = "complaint" THEN 1 ELSE 0 END) AS complaints,
                    SUM(CASE WHEN feedback_type = "compliment" OR rating >= 4 THEN 1 ELSE 0 END) AS positive_reviews
                FROM guest_feedback
                WHERE rating IS NOT NULL
            ');
            $row = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;
            if (!$row) {
                return $defaults;
            }

            $total = (int)($row['total_reviews'] ?? 0);
            $positive = (int)($row['positive_reviews'] ?? 0);
            $complaints = (int)($row['complaints'] ?? 0);

            return [
                'average_rating' => $row['average_rating'] !== null ? round((float)$row['average_rating'], 1) : null,
                'total_reviews' => $total,
                'pending_response' => $complaints, // For now, treat complaints as pending
                'satisfaction_rate' => $total > 0 ? round(($positive / $total) * 100, 1) : 0.0,
                'response_rate' => 0.0, // No response tracking in current table structure
                'complaints' => $complaints,
                'resolved' => 0, // No resolution tracking in current table structure
            ];
        } catch (PDOException $e) {
            error_log('Guest feedback summary error: ' . $e->getMessage());
            return $defaults;
        }
    }
}

if (!function_exists('getGuestFeedbackRatingDistribution')) {
    function getGuestFeedbackRatingDistribution(PDO $pdo): array
    {
        $distribution = [];
        $total = 0;

        try {
            $stmt = $pdo->query('
                SELECT rating, COUNT(*) AS count
                FROM guest_feedback
                WHERE rating IS NOT NULL
                GROUP BY rating
            ');
            $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
            foreach ($rows as $row) {
                $rating = (int)$row['rating'];
                $count = (int)$row['count'];
                if ($rating >= 1 && $rating <= 5) {
                    $distribution[$rating] = $count;
                    $total += $count;
                }
            }
        } catch (PDOException $e) {
            error_log('Guest feedback distribution error: ' . $e->getMessage());
        }

        $result = [];
        for ($rating = 5; $rating >= 1; $rating--) {
            $count = $distribution[$rating] ?? 0;
            $percentage = $total > 0 ? round(($count / $total) * 100, 1) : 0.0;
            $result[] = [
                'rating' => $rating,
                'count' => $count,
                'percentage' => $percentage
            ];
        }

        return $result;
    }
}

if (!function_exists('getGuestFeedbackCategoryBreakdown')) {
    function getGuestFeedbackCategoryBreakdown(PDO $pdo): array
    {
        try {
            $stmt = $pdo->query('
                SELECT
                    category,
                    COUNT(*) AS count,
                    AVG(rating) AS average_rating
                FROM guest_feedback
                GROUP BY category
                ORDER BY count DESC, category ASC
            ');
            $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
            $total = array_reduce($rows, static function ($carry, $row) {
                return $carry + (int)($row['count'] ?? 0);
            }, 0);

            return array_map(static function ($row) use ($total) {
                $category = $row['category'] ?? 'other';
                $count = (int)($row['count'] ?? 0);
                $avg = $row['average_rating'] !== null ? round((float)$row['average_rating'], 1) : null;
                $percentage = $total > 0 ? round(($count / $total) * 100, 1) : 0.0;

                return [
                    'category' => $category,
                    'count' => $count,
                    'percentage' => $percentage,
                    'average_rating' => $avg,
                ];
            }, $rows);
        } catch (PDOException $e) {
            error_log('Guest feedback categories error: ' . $e->getMessage());
            return [];
        }
    }
}

if (!function_exists('getRecentGuestFeedback')) {
    function getRecentGuestFeedback(PDO $pdo, int $limit = 5): array
    {
        $limit = max(1, min($limit, 20));

        try {
            $stmt = $pdo->prepare('
                SELECT
                    gf.id,
                    gf.rating,
                    gf.feedback_type,
                    gf.category,
                    gf.comments,
                    gf.created_at,
                    g.first_name,
                    g.last_name,
                    g.email,
                    rm.room_number
                FROM guest_feedback gf
                JOIN guests g ON gf.guest_id = g.id
                LEFT JOIN reservations r ON gf.reservation_id = r.id
                LEFT JOIN rooms rm ON r.room_id = rm.id
                ORDER BY gf.created_at DESC
                LIMIT :limit
            ');
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            return array_map(static function ($row) {
                return [
                    'id' => (int)$row['id'],
                    'rating' => $row['rating'] !== null ? (int)$row['rating'] : null,
                    'feedback_type' => $row['feedback_type'] ?? 'general',
                    'category' => $row['category'] ?? 'other',
                    'comments' => $row['comments'] ?? '',
                    'created_at' => $row['created_at'] ?? null,
                    'guest_name' => trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')),
                    'email' => $row['email'] ?? null,
                    'room_number' => $row['room_number'] ?? null,
                ];
            }, $stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            error_log('Guest feedback recent error: ' . $e->getMessage());
            return [];
        }
    }
}

if (!function_exists('getGuestFeedbackList')) {
    function getGuestFeedbackList(PDO $pdo, array $filters, int $limit = 20, int $offset = 0, string $sort = 'newest'): array
    {
        $limit = max(1, min($limit, 100));
        $offset = max(0, $offset);
        $filters = normalizeGuestFeedbackFilters($filters);
        $sortSql = getGuestFeedbackSortClause($sort);

        $params = [];
        $where = buildGuestFeedbackWhereClause($filters, $params);

        $sql = '
            SELECT
                gf.id,
                gf.rating,
                gf.feedback_type,
                gf.category,
                gf.comments,
                gf.is_resolved,
                gf.resolution_notes,
                gf.created_at,
                gf.updated_at,
                g.first_name,
                g.last_name,
                g.email,
                r.id AS reservation_id,
                r.reservation_number,
                rm.room_number,
                rm.room_type,
                CASE
                    WHEN gf.is_resolved = 1 THEN "resolved"
                    WHEN gf.feedback_type = "complaint" THEN "in_progress"
                    ELSE "new"
                END AS status
            FROM guest_feedback gf
            JOIN guests g ON gf.guest_id = g.id
            LEFT JOIN reservations r ON gf.reservation_id = r.id
            LEFT JOIN rooms rm ON r.room_id = rm.id
        ' . $where . '
            ' . $sortSql . '
            LIMIT :limit OFFSET :offset
        ';

        try {
            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            return array_map('mapGuestFeedbackRow', $stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            error_log('Guest feedback list error: ' . $e->getMessage());
            return [];
        }
    }
}

if (!function_exists('countGuestFeedback')) {
    function countGuestFeedback(PDO $pdo, array $filters): int
    {
        $filters = normalizeGuestFeedbackFilters($filters);
        $params = [];
        $where = buildGuestFeedbackWhereClause($filters, $params);

        $sql = '
            SELECT COUNT(*)
            FROM guest_feedback gf
            JOIN guests g ON gf.guest_id = g.id
            LEFT JOIN reservations r ON gf.reservation_id = r.id
            LEFT JOIN rooms rm ON r.room_id = rm.id
        ' . $where;

        try {
            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('Guest feedback count error: ' . $e->getMessage());
            return 0;
        }
    }
}

if (!function_exists('buildGuestFeedbackWhereClause')) {
    function buildGuestFeedbackWhereClause(array $filters, array &$params): string
    {
        $conditions = [];
        $params = [];

        if (!empty($filters['rating'])) {
            $conditions[] = 'gf.rating = :rating';
            $params[':rating'] = (int)$filters['rating'];
        }

        if (!empty($filters['status'])) {
            switch ($filters['status']) {
                case 'resolved':
                    $conditions[] = 'gf.is_resolved = 1';
                    break;
                case 'in_progress':
                    $conditions[] = '(gf.is_resolved = 0 AND gf.feedback_type = "complaint")';
                    break;
                case 'new':
                    $conditions[] = '(gf.is_resolved = 0 AND gf.feedback_type <> "complaint")';
                    break;
            }
        }

        if (!empty($filters['category'])) {
            $conditions[] = 'gf.category = :category';
            $params[':category'] = $filters['category'];
        }

        if (!empty($filters['feedback_type'])) {
            $conditions[] = 'gf.feedback_type = :feedback_type';
            $params[':feedback_type'] = $filters['feedback_type'];
        }

        if (!empty($filters['search'])) {
            $conditions[] = '(
                CONCAT_WS(" ", g.first_name, g.last_name) LIKE :search
                OR g.email LIKE :search
                OR gf.comments LIKE :search
                OR r.reservation_number LIKE :search
                OR rm.room_number LIKE :search
            )';
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['date_from'])) {
            $conditions[] = 'gf.created_at >= :date_from';
            $params[':date_from'] = $filters['date_from'] . ' 00:00:00';
        }

        if (!empty($filters['date_to'])) {
            $conditions[] = 'gf.created_at <= :date_to';
            $params[':date_to'] = $filters['date_to'] . ' 23:59:59';
        }

        if (empty($conditions)) {
            return '';
        }

        return 'WHERE ' . implode(' AND ', $conditions);
    }
}

if (!function_exists('getGuestFeedbackSortClause')) {
    function getGuestFeedbackSortClause(string $sort): string
    {
        switch ($sort) {
            case 'oldest':
                return 'ORDER BY gf.created_at ASC';
            case 'rating_high':
                return 'ORDER BY (gf.rating IS NULL) ASC, gf.rating DESC, gf.created_at DESC';
            case 'rating_low':
                return 'ORDER BY (gf.rating IS NULL) ASC, gf.rating ASC, gf.created_at DESC';
            default:
                return 'ORDER BY gf.created_at DESC';
        }
    }
}

if (!function_exists('mapGuestFeedbackRow')) {
    function mapGuestFeedbackRow(array $row): array
    {
        $guestName = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));

        return [
            'id' => (int)$row['id'],
            'rating' => $row['rating'] !== null ? (int)$row['rating'] : null,
            'feedback_type' => $row['feedback_type'] ?? 'general',
            'category' => $row['category'] ?? 'other',
            'comments' => $row['comments'] ?? '',
            'is_resolved' => (int)($row['is_resolved'] ?? 0) === 1,
            'status' => $row['status'] ?? 'new',
            'resolution_notes' => $row['resolution_notes'] ?? null,
            'created_at' => $row['created_at'] ?? null,
            'updated_at' => $row['updated_at'] ?? null,
            'guest' => [
                'name' => $guestName,
                'email' => $row['email'] ?? null,
                'initials' => strtoupper(substr($row['first_name'] ?? '', 0, 1) . substr($row['last_name'] ?? '', 0, 1))
            ],
            'reservation' => [
                'id' => $row['reservation_id'] !== null ? (int)$row['reservation_id'] : null,
                'number' => $row['reservation_number'] ?? null,
                'room_number' => $row['room_number'] ?? null,
                'room_type' => $row['room_type'] ?? null,
            ],
        ];
    }
}
