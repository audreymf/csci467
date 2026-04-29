<?php
/**
 * HQ quote operations — native PHP / MySQL (same behavior as legacy backend/api.js).
 * No Node.js server required for review.sansction.order.
 */
require_once __DIR__ . '/db.php';

function quote_log_fake_email(string $eventName, string $email, array $payload): void
{
    unset($payload['secretNotes'], $payload['notes']);

    $message = 'EMAIL_EVENT:' . $eventName . ' To:' . $email . ' ' . json_encode($payload) . PHP_EOL;

    file_put_contents(__DIR__ . '/email_log.txt', $message, FILE_APPEND);
}

/**
 * @param array<int, array<string, mixed>> $items
 * @return array{subtotal: float, total: float}
 */
function quote_compute_totals(array $items, ?string $discountType, $discountAmt): array
{
    $subtotal = 0.0;
    foreach ($items as $i) {
        $subtotal += (float)($i['price'] ?? 0);
    }
    $discountValue = (float)($discountAmt ?? 0);
    $total = $subtotal;
    if ($discountType === 'percentage') {
        $total = $subtotal - $subtotal * ($discountValue / 100);
    } elseif ($discountType === 'amount') {
        $total = $subtotal - $discountValue;
    }
    return [
        'subtotal' => round($subtotal, 2),
        'total' => round(max(0, $total), 2),
    ];
}

/**
 * @return array<int, array<string, mixed>>
 */
function quote_get_line_items(PDO $pdo, int $quoteId): array
{
    $stmt = $pdo->prepare(
        'SELECT id, quoteID, item, price FROM line_items WHERE quoteID = ? ORDER BY id ASC'
    );
    $stmt->execute([$quoteId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * @return array<string, mixed>|null
 */
function quote_get_by_id(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare(
        'SELECT id, associateID, customerID, email, status, discountType, discountAmt, `date`, commission
         FROM quotes WHERE id = ?'
    );
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

/**
 * @return array{status: int, data: array<string, mixed>}
 */
function apiRequest(string $method, string $path, ?array $payload = null): array
{
    global $pdo;

    $path = '/' . ltrim($path, '/');

    try {
        if ($method === 'GET' && preg_match('#^/status/([a-z]+)$#', $path, $m)) {
            return quote_api_get_by_status($pdo, $m[1]);
        }
        if ($method === 'GET' && preg_match('#^/(\d+)$#', $path, $m)) {
            return quote_api_get_one($pdo, (int)$m[1]);
        }
        if ($method === 'POST' && preg_match('#^/(\d+)/notes$#', $path, $m)) {
            return quote_api_post_note($pdo, (int)$m[1], $payload ?? []);
        }
        if ($method === 'PUT' && preg_match('#^/(\d+)/line-items/(\d+)$#', $path, $m)) {
            return quote_api_put_line_item($pdo, (int)$m[1], (int)$m[2], $payload ?? []);
        }
        if ($method === 'POST' && preg_match('#^/(\d+)/sanction$#', $path, $m)) {
            return quote_api_post_sanction($pdo, (int)$m[1], $payload ?? []);
        }
        if ($method === 'POST' && preg_match('#^/(\d+)/order$#', $path, $m)) {
            return quote_api_post_order($pdo, (int)$m[1], $payload ?? []);
        }
    } catch (Throwable $e) {
        error_log('api_client: ' . $e->getMessage());
        return ['status' => 500, 'data' => ['error' => 'Server error']];
    }

    return ['status' => 404, 'data' => ['error' => 'Not found']];
}

/**
 * @return array{status: int, data: array<int, array<string, mixed>>|array<string, mixed>}
 */
function quote_api_get_by_status(PDO $pdo, string $status): array
{
    $allowed = ['draft', 'finalized', 'sanctioned', 'ordered'];
    if (!in_array($status, $allowed, true)) {
        return ['status' => 400, 'data' => ['error' => 'Invalid status']];
    }

    $stmt = $pdo->prepare(
        'SELECT id, associateID, customerID, email, status, discountType, discountAmt, `date`, commission
         FROM quotes WHERE status = ? ORDER BY `date` DESC'
    );
    $stmt->execute([$status]);
    $quotes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $enriched = [];
    foreach ($quotes as $q) {
        $items = quote_get_line_items($pdo, (int)$q['id']);
        $totals = quote_compute_totals($items, $q['discountType'] ?: null, $q['discountAmt']);
        $enriched[] = array_merge($q, $totals);
    }

    return ['status' => 200, 'data' => $enriched];
}

/**
 * @return array{status: int, data: array<string, mixed>}
 */
function quote_api_get_one(PDO $pdo, int $id): array
{
    $quote = quote_get_by_id($pdo, $id);
    if (!$quote) {
        return ['status' => 404, 'data' => ['error' => 'Quote not found']];
    }

    $items = quote_get_line_items($pdo, $id);
    $stmt = $pdo->prepare(
        'SELECT id, quoteID, content, is_secret FROM notes WHERE quoteID = ? ORDER BY id DESC'
    );
    $stmt->execute([$id]);
    $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $totals = quote_compute_totals($items, $quote['discountType'] ?: null, $quote['discountAmt']);

    return ['status' => 200, 'data' => array_merge($quote, $totals, ['items' => $items, 'notes' => $notes])];
}

/**
 * @param array<string, mixed> $payload
 * @return array{status: int, data: array<string, mixed>}
 */
function quote_api_post_note(PDO $pdo, int $quoteId, array $payload): array
{
    $content = $payload['content'] ?? '';
    $isSecret = $payload['is_secret'] ?? 0;

    $stmt = $pdo->prepare(
        'INSERT INTO notes (quoteID, content, is_secret) VALUES (?, ?, ?)'
    );
    $stmt->execute([$quoteId, $content, (int)$isSecret]);

    return ['status' => 200, 'data' => ['message' => 'Note added']];
}

/**
 * @param array<string, mixed> $payload
 * @return array{status: int, data: array<string, mixed>}
 */
function quote_api_put_line_item(PDO $pdo, int $quoteId, int $itemId, array $payload): array
{
    $item = $payload['item'] ?? '';
    $price = $payload['price'] ?? null;

    if (!is_string($item) || $item === '' || !is_numeric($price) || (float)$price < 0) {
        return ['status' => 400, 'data' => ['error' => 'Invalid item payload']];
    }

    $check = $pdo->prepare('SELECT id FROM line_items WHERE id = ? AND quoteID = ?');
    $check->execute([$itemId, $quoteId]);
    if (!$check->fetch()) {
        return ['status' => 404, 'data' => ['error' => 'Line item not found for this quote']];
    }

    $upd = $pdo->prepare('UPDATE line_items SET item = ?, price = ? WHERE id = ?');
    $upd->execute([$item, $price, $itemId]);

    return ['status' => 200, 'data' => ['message' => 'Line item updated']];
}

/**
 * @param array<string, mixed> $payload
 * @return array{status: int, data: array<string, mixed>}
 */
function quote_api_post_sanction(PDO $pdo, int $id, array $payload): array
{
    $discountType = $payload['discountType'] ?? '';
    $discountAmt = $payload['discountAmt'] ?? 0;
    $normalizedType = $discountType !== '' ? $discountType : null;

    if (!in_array($normalizedType, [null, 'percentage', 'amount'], true)) {
        return ['status' => 400, 'data' => ['error' => 'Invalid discountType']];
    }

    $quote = quote_get_by_id($pdo, $id);
    if (!$quote || ($quote['status'] ?? '') !== 'finalized') {
        return ['status' => 400, 'data' => ['error' => 'Quote must be finalized to sanction']];
    }

    $upd = $pdo->prepare(
        'UPDATE quotes SET status = ?, discountType = ?, discountAmt = ? WHERE id = ?'
    );
    $upd->execute(['sanctioned', $normalizedType, (float)($discountAmt ?: 0), $id]);

    $items = quote_get_line_items($pdo, $id);
    $totals = quote_compute_totals($items, $normalizedType, (float)($discountAmt ?: 0));

    $stmt = $pdo->prepare('SELECT id, content, is_secret FROM notes WHERE quoteID = ?');
    $stmt->execute([$id]);
    $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $publicNotes = [];
    foreach ($notes as $n) {
        if (empty($n['is_secret'])) {
            $publicNotes[] = ['id' => (int)$n['id'], 'content' => $n['content']];
        }
    }

    $emailItems = array_map(static function ($i) {
        return ['item' => $i['item'], 'price' => (float)$i['price']];
    }, $items);

    quote_log_fake_email('quote_sanctioned', (string)$quote['email'], [
        'quoteId' => $id,
        'status' => 'sanctioned',
        'discountType' => $normalizedType,
        'discountAmt' => (float)($discountAmt ?: 0),
        'items' => $emailItems,
        'publicNotes' => $publicNotes,
        'subtotal' => $totals['subtotal'],
        'total' => $totals['total'],
    ]);

    return [
        'status' => 200,
        'data' => array_merge(['message' => 'Quote sanctioned'], $totals),
    ];
}

/**
 * @param array<string, mixed> $payload
 * @return array{status: int, data: array<string, mixed>}
 */
function quote_api_post_order(PDO $pdo, int $id, array $payload): array
{
    $finalDiscountType = $payload['finalDiscountType'] ?? '';
    $finalDiscountAmt = $payload['finalDiscountAmt'] ?? 0;
    $commissionRate = $payload['commissionRate'] ?? 10;
    $normalizedType = $finalDiscountType !== '' ? $finalDiscountType : null;

    if (!in_array($normalizedType, [null, 'percentage', 'amount'], true)) {
        return ['status' => 400, 'data' => ['error' => 'Invalid finalDiscountType']];
    }

    $quote = quote_get_by_id($pdo, $id);
    if (!$quote || ($quote['status'] ?? '') !== 'sanctioned') {
        return ['status' => 400, 'data' => ['error' => 'Quote must be sanctioned to order']];
    }

    $items = quote_get_line_items($pdo, $id);
    $effType = $normalizedType ?? ($quote['discountType'] ?: null);
    $effAmt = $normalizedType ? (float)($finalDiscountAmt ?: 0) : (float)($quote['discountAmt'] ?? 0);
    $totals = quote_compute_totals($items, $effType, $effAmt);
    $commission = round($totals['total'] * ((float)$commissionRate / 100), 2);

    $upd = $pdo->prepare(
        'UPDATE quotes SET status = ?, discountType = ?, discountAmt = ?, commission = ? WHERE id = ?'
    );
    $upd->execute([
        'ordered',
        $effType,
        $effAmt,
        $commission,
        $id,
    ]);

    $stmt = $pdo->prepare('SELECT id, content, is_secret FROM notes WHERE quoteID = ?');
    $stmt->execute([$id]);
    $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $publicNotes = [];
    foreach ($notes as $n) {
        if (empty($n['is_secret'])) {
            $publicNotes[] = ['id' => (int)$n['id'], 'content' => $n['content']];
        }
    }

    $processingDate = (new DateTimeImmutable('today'))->format('Y-m-d');
    $emailItems = array_map(static function ($i) {
        return ['item' => $i['item'], 'price' => (float)$i['price']];
    }, $items);

    quote_log_fake_email('purchase_order_created', (string)$quote['email'], [
        'quoteId' => $id,
        'status' => 'ordered',
        'items' => $emailItems,
        'publicNotes' => $publicNotes,
        'processingDate' => $processingDate,
        'commissionRate' => (float)$commissionRate,
        'commissionAmount' => $commission,
        'finalAmount' => $totals['total'],
    ]);

    return [
        'status' => 200,
        'data' => [
            'message' => 'Quote converted to order',
            'processingDate' => $processingDate,
            'commissionRate' => (float)$commissionRate,
            'commissionAmount' => $commission,
            'finalAmount' => $totals['total'],
        ],
    ];
}
