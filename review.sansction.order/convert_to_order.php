<?php
require_once 'api_client.php';

$quoteId = $_GET['id'] ?? null;
if (!$quoteId || !ctype_digit((string)$quoteId)) {
    die('Invalid quote ID.');
}
$quoteId = (int)$quoteId;
$errors = [];
$confirmation = null;

$quoteRes = apiRequest('GET', '/' . $quoteId);
$quote = ($quoteRes['status'] >= 200 && $quoteRes['status'] < 300 && is_array($quoteRes['data']))
    ? $quoteRes['data']
    : null;

if (!$quote || ($quote['status'] ?? '') !== 'sanctioned') {
    die('Quote not found or not in sanctioned status.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $discountType = $_POST['final_discount_type'] ?? '';
    $discountValue = filter_input(INPUT_POST, 'final_discount_value', FILTER_VALIDATE_FLOAT);

    if (!in_array($discountType, ['', 'percentage', 'amount'], true)) {
        $errors[] = 'Invalid discount type.';
    }
    if ($discountType !== '' && ($discountValue === false || $discountValue < 0)) {
        $errors[] = 'Discount value must be a positive number.';
    }
    if ($discountType === 'percentage' && $discountValue > 100) {
        $errors[] = 'Percentage cannot exceed 100.';
    }

    if (empty($errors)) {
        $res = apiRequest('POST', '/' . $quoteId . '/order', [
            'finalDiscountType' => $discountType,
            'finalDiscountAmt' => $discountValue ?: 0,
            'commissionRate' => 10
        ]);

        if ($res['status'] >= 200 && $res['status'] < 300) {
            $confirmation = [
                'quote_id' => $quoteId,
                'customer_id' => (int)($quote['customerID'] ?? 0),
                'associate_id' => (int)($quote['associateID'] ?? 0),
                'sanctioned_total' => (float)($quote['subtotal'] ?? $quote['total']),
                'discount_type' => $discountType,
                'discount_value' => (float)($discountValue ?: 0),
                'final_amount' => (float)($res['data']['finalAmount'] ?? 0),
                'processing_date' => $res['data']['processingDate'] ?? '',
                'commission_rate' => (float)($res['data']['commissionRate'] ?? 0),
                'commission_amount' => (float)($res['data']['commissionAmount'] ?? 0)
            ];
        } else {
            $errors[] = 'Order conversion failed. Please try again.';
        }
    }
}

$items = $quote['items'] ?? [];

include_once 'header.php';
?>

<?php if ($confirmation): ?>
<!-- Purchase Confirmation -->
<div class="flash success">
    Quote #<?= (int)$confirmation['quote_id'] ?> converted to order successfully.
</div>

<div class="form-box" style="max-width:600px;">
    <h3 style="margin-top:0;">Order Confirmation</h3>
    <table style="box-shadow:none;">
        <tr><td><strong>Quote ID</strong></td>          <td>#<?= (int)$confirmation['quote_id'] ?></td></tr>
        <tr><td><strong>Customer</strong></td>          <td>#<?= (int)$confirmation['customer_id'] ?></td></tr>
        <tr><td><strong>Sales Associate</strong></td>   <td>#<?= (int)$confirmation['associate_id'] ?></td></tr>
        <tr><td><strong>Sanctioned Total</strong></td>  <td>$<?= number_format((float)($quote['subtotal'] ?? $confirmation['sanctioned_total']), 2) ?></td></tr>
        <?php if ($confirmation['discount_type']): ?>
        <tr>
            <td><strong>Final Discount</strong></td>
            <td>
                <?= $confirmation['discount_type'] === 'percentage'
                    ? number_format($confirmation['discount_value'], 2) . '%'
                    : '$' . number_format($confirmation['discount_value'], 2) ?>
            </td>
        </tr>
        <?php endif; ?>
        <tr><td><strong>Final Amount</strong></td>      <td>$<?= number_format($confirmation['final_amount'], 2) ?></td></tr>
        <tr><td><strong>Processing Date</strong></td>   <td><?= htmlspecialchars($confirmation['processing_date']) ?></td></tr>
        <tr><td><strong>Commission Rate</strong></td>   <td><?= number_format($confirmation['commission_rate'], 2) ?>%</td></tr>
        <tr><td><strong>Commission Earned</strong></td> <td>$<?= number_format($confirmation['commission_amount'], 2) ?></td></tr>
    </table>
    <br>
    <a href="process_orders.php" class="btn btn-blue">Back to Orders</a>
    <a href="review_quotes.php?status=ordered" class="btn btn-green" style="margin-left:8px;">View Ordered Quotes</a>
</div>

<?php else: ?>
<!-- Convert to Order Form -->
<h2>Convert Quote #<?= $quoteId ?> to Purchase Order</h2>

<?php foreach ($errors as $e): ?>
    <div class="flash error"><?= htmlspecialchars($e) ?></div>
<?php endforeach; ?>

<div class="info-box">
    <strong>Customer:</strong> #<?= (int)($quote['customerID'] ?? 0) ?><br>
    <strong>Sanctioned Total:</strong> $<?= number_format((float)($quote['subtotal'] ?? $quote['total']), 2) ?>
</div>

<!-- Line items (read-only at this stage) -->
<?php if (!empty($items)): ?>
<h3>Line Items</h3>
<table style="margin-bottom:20px;">
    <tr><th>Item</th><th>Price</th></tr>
    <?php foreach ($items as $i): ?>
    <tr>
        <td><?= htmlspecialchars($i['item'] ?? '-') ?></td>
        <td>$<?= number_format((float)$i['price'], 2) ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

<div class="form-box">
    <h3 style="margin-top:0;">Apply Final Discount & Convert</h3>
    <form method="POST">
        
        <label>Final Discount Type</label>
        <select name="final_discount_type" id="disc-type" onchange="document.getElementById('disc-val-row').style.display=this.value?'block':'none'">
            <option value="">None</option>
            <option value="percentage">Percentage (%)</option>
            <option value="amount">Fixed Amount ($)</option>
        </select>

        <div id="disc-val-row" style="display:none;">
            <label>Discount Value</label>
            <input type="number" step="0.01" min="0" name="final_discount_value" placeholder="0.00">
        </div>

        <p style="font-size:13px; color:#555;">
            Submitting will contact the external processing system, record the commission, and mark this quote as ordered.
        </p>

        <button type="submit">Convert to Purchase Order</button>
    </form>
</div>
<?php endif; ?>

<?php include_once 'footer.php'; ?>
