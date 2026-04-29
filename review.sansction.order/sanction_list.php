<?php
require_once 'api_client.php';
include_once 'header.php';

// Show flash message if set
if (!empty($_SESSION['flash'])) {
    $f = $_SESSION['flash'];
    echo '<div class="flash ' . $f['type'] . '">' . htmlspecialchars($f['message']) . '</div>';
    unset($_SESSION['flash']);
}

// Get all finalized quotes waiting to be sanctioned
$response = apiRequest('GET', '/status/finalized');
$rows = ($response['status'] >= 200 && $response['status'] < 300 && is_array($response['data']))
    ? $response['data']
    : [];
?>

<h2>Finalized Quotes - Pending Sanction</h2>

<?php if (empty($rows)): ?>
    <p>No finalized quotes waiting for sanction.</p>
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
        <td><a href="sanction_quote.php?id=<?= (int)$r['id'] ?>" class="btn btn-blue">Sanction</a></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

<?php include_once 'footer.php'; ?>