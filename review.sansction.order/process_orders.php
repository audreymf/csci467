<?php
require_once 'api_client.php';
include_once 'header.php';
 
// Show flash message if set
if (!empty($_SESSION['flash'])) {
    $f = $_SESSION['flash'];
    echo '<div class="flash ' . $f['type'] . '">' . htmlspecialchars($f['message']) . '</div>';
    unset($_SESSION['flash']);
}
 
// Get all sanctioned quotes ready for order processing
$response = apiRequest('GET', '/status/sanctioned');
$rows = ($response['status'] >= 200 && $response['status'] < 300 && is_array($response['data']))
    ? $response['data']
    : [];
?>
 
<h2>Sanctioned Quotes - Ready for Order Processing</h2>
 
<?php if (empty($rows)): ?>
    <p>No sanctioned quotes pending order conversion.</p>
<?php else: ?>
<table>
    <tr>
        <th>ID</th><th>Date</th><th>Customer</th><th>Total</th><th>Action</th>
    </tr>
    <?php foreach ($rows as $r): ?>
    <tr>
        <td>#<?= (int)$r['id'] ?></td>
        <td><?= htmlspecialchars($r['date'] ?? '-') ?></td>
        <td>Customer #<?= (int)($r['customerID'] ?? 0) ?></td>
        <td>$<?= number_format((float)$r['total'], 2) ?></td>
        <td><a href="convert_to_order.php?id=<?= (int)$r['id'] ?>" class="btn btn-green">Process Order</a></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>
 
<?php include_once 'footer.php'; ?>