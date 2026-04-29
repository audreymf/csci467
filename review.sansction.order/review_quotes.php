<?php
require_once 'api_client.php';
include_once 'header.php';

// Only allow known statuses
$allowed = ['finalized', 'sanctioned', 'ordered'];
$status = $_GET['status'] ?? 'finalized';
if (!in_array($status, $allowed, true)) {
    $status = 'finalized';
}

// Show flash message if set
if (!empty($_SESSION['flash'])) {
    $f = $_SESSION['flash'];
    echo '<div class="flash ' . $f['type'] . '">' . htmlspecialchars($f['message']) . '</div>';
    unset($_SESSION['flash']);
}

// Get quotes for the selected status from API
$response = apiRequest('GET', '/status/' . urlencode($status));
$quotes = ($response['status'] >= 200 && $response['status'] < 300 && is_array($response['data']))
    ? $response['data']
    : [];
?>

<h2>HQ Quote Review</h2>

<div class="tabs">
    <a href="review_quotes.php?status=finalized"  <?= $status === 'finalized'  ? 'class="active"' : '' ?>>Finalized</a>
    <a href="review_quotes.php?status=sanctioned" <?= $status === 'sanctioned' ? 'class="active"' : '' ?>>Sanctioned</a>
    <a href="review_quotes.php?status=ordered"    <?= $status === 'ordered'    ? 'class="active"' : '' ?>>Ordered</a>
</div>

<?php if (empty($quotes)): ?>
    <p>No quotes found with status: <strong><?= htmlspecialchars($status) ?></strong></p>
<?php else: ?>
<table>
    <tr>
        <th>ID</th><th>Date</th><th>Customer</th><th>Total</th><th>Action</th>
    </tr>
    <?php foreach ($quotes as $q): ?>
    <tr>
        <td>#<?= (int)$q['id'] ?></td>
        <td><?= htmlspecialchars($q['date'] ?? '-') ?></td>
        <td>Customer #<?= (int)($q['customerID'] ?? 0) ?></td>
        <td>$<?= number_format((float)$q['total'], 2) ?></td>
        <td>
            <?php if ($q['status'] === 'finalized'): ?>
                <a href="sanction_quote.php?id=<?= (int)$q['id'] ?>" class="btn btn-blue">Sanction</a>
            <?php elseif ($q['status'] === 'sanctioned'): ?>
                <a href="convert_to_order.php?id=<?= (int)$q['id'] ?>" class="btn btn-green">Process Order</a>
            <?php else: ?>
                Completed
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

<?php include_once 'footer.php'; ?>