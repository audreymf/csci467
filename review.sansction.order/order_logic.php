<?php
function sanctionQuote(PDO $pdo, int $quoteId, array $discount): void
{
    // Get the sum of all line item prices
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(price), 0) FROM line_items WHERE quoteID = ?");
    $stmt->execute([$quoteId]);
    $subtotal = (float)$stmt->fetchColumn();

    $type  = $discount['type']  ?? '';
    $value = (float)($discount['value'] ?? 0);

    // Calculate the discount amount
    if ($type === 'percentage') {
        $discountAmount = round($subtotal * $value / 100, 2);
    } elseif ($type === 'amount') {
        $discountAmount = min($value, $subtotal);
    } else {
        $discountAmount = 0.0;
        $type  = null;
        $value = 0.0;
    }

    $total = round($subtotal - $discountAmount, 2);

    $pdo->prepare("
        UPDATE quotes
        SET status = 'sanctioned',
            discountType   = ?,
            discountAmt    = ?,
            subtotal       = ?,
            discountAmount = ?,
            total          = ?,
            sanctioned_at  = NOW()
        WHERE id = ?
    ")->execute([$type ?: null, $value, $subtotal, $discountAmount, $total, $quoteId]);
}


function recalculateQuoteTotals(PDO $pdo, int $quoteId): void
{
    // New subtotal from line items
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(price), 0) FROM line_items WHERE quoteID = ?");
    $stmt->execute([$quoteId]);
    $subtotal = (float)$stmt->fetchColumn();

    // Get current discount settings from the quote
    $stmt = $pdo->prepare("SELECT discountType, discountAmt FROM quotes WHERE id = ?");
    $stmt->execute([$quoteId]);
    $q = $stmt->fetch(PDO::FETCH_ASSOC);

    $type  = $q['discountType'] ?? null;
    $value = (float)($q['discountAmt'] ?? 0);

    if ($type === 'percentage') {
        $discountAmount = round($subtotal * $value / 100, 2);
    } elseif ($type === 'amount') {
        $discountAmount = min($value, $subtotal);
    } else {
        $discountAmount = 0.0;
    }

    $total = round($subtotal - $discountAmount, 2);

    $pdo->prepare("UPDATE quotes SET subtotal = ?, discountAmount = ?, total = ? WHERE id = ?")
        ->execute([$subtotal, $discountAmount, $total, $quoteId]);
}



function callExternalProcessingSystem(int $quoteId, float $finalAmount, array $quoteData): ?array
{
    require_once __DIR__ . '/external_system.php';

    $result = sendToExternalSystem($quoteId, $finalAmount);

    if (!is_array($result) || empty($result['processing_date']) || !isset($result['commission_rate'])) {
        return null;
    }

    return [
        'processingDate' => $result['processing_date'],
        'commissionRate' => (float)$result['commission_rate'],
    ];
}