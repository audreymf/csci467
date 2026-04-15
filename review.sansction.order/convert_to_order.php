<?php
require '../config/db.php';
require '../backend/order_logic.php';

// Validate the quote ID from URL
$quoteId = $_GET['id'] ?? null;
if (!$quoteId || !ctype_digit((string)$quoteId)) {
    die('Invalid quote ID.');
}
$quoteId = (int)$quoteId;

// Make sure the quote exists and is sanctioned
$stmt = $pdo->prepare("
    SELECT q.*, c.name AS customer_name, c.email AS customer_email,
           sa.id AS associate_id, sa.name AS associate_name
    FROM quotes q
    JOIN customers c ON q.customerID = c.id
    JOIN sales_associates sa ON q.associateID = sa.id
    WHERE q.id = ? AND q.status = 'sanctioned'
");
$stmt->execute([$quoteId]);
$quote = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$quote) {
    die('Quote not found or not in sanctioned status.');
}

$errors = [];
$confirmation = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $discountType  = $_POST['final_discount_type'] ?? '';
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
        $discountValue   = $discountValue ?: 0.0;
        $sanctionedTotal = (float)$quote['total'];

        // Calculate final amount after any additional discount
        if ($discountType === 'percentage') {
            $finalAmount = round($sanctionedTotal * (1 - $discountValue / 100), 2);
        } elseif ($discountType === 'amount') {
            $finalAmount = round(max(0, $sanctionedTotal - $discountValue), 2);
        } else {
            $finalAmount = $sanctionedTotal;
        }

        // Call the external processing system to get processingDate and commissionRate
        $external = callExternalProcessingSystem($quoteId, $finalAmount, $quote);

        if ($external === null) {
            $errors[] = 'Could not reach the external processing system. Please try again.';
        } else {
            $processingDate   = $external['processingDate'];
            $commissionRate   = (float)$external['commissionRate'];
            $commissionAmount = round($finalAmount * $commissionRate / 100, 2);

            // Save everything in a transaction
            $pdo->beginTransaction();
            try {
                // Insert purchase order
                $pdo->prepare("
                    INSERT INTO purchase_orders
                        (quoteID, finalDiscountType, finalDiscountValue, finalAmount,
                         processingDate, commissionRate, commissionAmount, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                ")->execute([
                    $quoteId,
                    $discountType ?: null,
                    $discountType ? $discountValue : null,
                    $finalAmount,
                    $processingDate,
                    $commissionRate,
                    $commissionAmount
                ]);
                $purchaseOrderId = (int)$pdo->lastInsertId();

                // Add commission to the associate's total
                $pdo->prepare("UPDATE sales_associates SET commission = commission + ? WHERE id = ?")
                    ->execute([$commissionAmount, $quote['associate_id']]);

                // Mark quote as ordered
                $pdo->prepare("UPDATE quotes SET status = 'ordered', ordered_at = NOW() WHERE id = ?")
                    ->execute([$quoteId]);

                $pdo->commit();

                // Store confirmation details for display
                $confirmation = [
                    'purchase_order_id' => $purchaseOrderId,
                    'quote_id'          => $quoteId,
                    'customer_name'     => $quote['customer_name'],
                    'associate_name'    => $quote['associate_name'],
                    'sanctioned_total'  => $sanctionedTotal,
                    'discount_type'     => $discountType,
                    'discount_value'    => $discountValue,
                    'final_amount'      => $finalAmount,
                    'processing_date'   => $processingDate,
                    'commission_rate'   => $commissionRate,
                    'commission_amount' => $commissionAmount,
                ];

            } catch (Exception $e) {
                $pdo->rollBack();
                $errors[] = 'A database error occurred. Please try again.';
            }
        }
    }
}

// Load line items for display
$itemsStmt = $pdo->prepare("SELECT * FROM line_items WHERE quoteID = ? ORDER BY id");
$itemsStmt->execute([$quoteId]);
$items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

include_once 'header.php';
?>

<?php if ($confirmation): ?>
<!-- Purchase Confirmation -->
<div class="flash success">
    Purchase Order #<?= (int)$confirmation['purchase_order_id'] ?> created successfully.
</div>

<div class="form-box" style="max-width:600px;">
    <h3 style="margin-top:0;">Order Confirmation</h3>
    <table style="box-shadow:none;">
        <tr><td><strong>Purchase Order ID</strong></td><td>#<?= (int)$confirmation['purchase_order_id'] ?></td></tr>
        <tr><td><strong>Quote ID</strong></td>          <td>#<?= (int)$confirmation['quote_id'] ?></td></tr>
        <tr><td><strong>Customer</strong></td>          <td><?= htmlspecialchars($confirmation['customer_name']) ?></td></tr>
        <tr><td><strong>Sales Associate</strong></td>   <td><?= htmlspecialchars($confirmation['associate_name']) ?></td></tr>
        <tr><td><strong>Sanctioned Total</strong></td>  <td>$<?= number_format($confirmation['sanctioned_total'], 2) ?></td></tr>
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
    <strong>Customer:</strong> <?= htmlspecialchars($quote['customer_name']) ?><br>
    <strong>Sanctioned Total:</strong> $<?= number_format((float)$quote['total'], 2) ?>
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