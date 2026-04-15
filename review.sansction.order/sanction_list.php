<?php
require '../config/db.php';
include_once 'header.php';

// Show flash message if set
if (!empty($_SESSION['flash'])) {
    $f = $_SESSION['flash'];
    echo '<div class="flash ' . $f['type'] . '">' . htmlspecialchars($f['message']) . '</div>';
    unset($_SESSION['flash']);
}

// Get all finalized quotes waiting to be sanctioned
$stmt = $pdo->prepare("
    SELECT q.id, q.created_at, q.total,
           c.name AS customer_name
    FROM quotes q
    JOIN customers c ON q.customerID = c.id
    WHERE q.status = 'finalized'
    ORDER BY q.created_at DESC
");
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        <td><?= htmlspecialchars($r['created_at']) ?></td>
        <td><?= htmlspecialchars($r['customer_name']) ?></td>
        <td>$<?= number_format((float)$r['total'], 2) ?></td>
        <td><a href="sanction_quote.php?id=<?= (int)$r['id'] ?>" class="btn btn-blue">Sanction</a></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

<?php include_once 'footer.php'; ?>