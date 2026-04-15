<?php
require '../config/db.php';
include_once 'header.php';
 
// Show flash message if set
if (!empty($_SESSION['flash'])) {
    $f = $_SESSION['flash'];
    echo '<div class="flash ' . $f['type'] . '">' . htmlspecialchars($f['message']) . '</div>';
    unset($_SESSION['flash']);
}
 
// Get all sanctioned quotes ready for order processing
$stmt = $pdo->prepare("
    SELECT q.id, q.created_at, q.total,
           c.name AS customer_name
    FROM quotes q
    JOIN customers c ON q.customerID = c.id
    WHERE q.status = 'sanctioned'
    ORDER BY q.created_at DESC
");
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        <td><?= htmlspecialchars($r['created_at']) ?></td>
        <td><?= htmlspecialchars($r['customer_name']) ?></td>
        <td>$<?= number_format((float)$r['total'], 2) ?></td>
        <td><a href="convert_to_order.php?id=<?= (int)$r['id'] ?>" class="btn btn-green">Process Order</a></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>
 
<?php include_once 'footer.php'; ?>