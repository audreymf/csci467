<?php
require_once 'api_client.php';

$quoteId = $_GET['id'] ?? null;
if (!$quoteId || !ctype_digit((string)$quoteId)) {
    die('Invalid quote ID.');
}
$quoteId = (int)$quoteId;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_note') {
    $note = trim($_POST['note_content'] ?? '');
    if ($note !== '') {
        $res = apiRequest('POST', '/' . $quoteId . '/notes', [
            'content' => $note,
            'is_secret' => 1
        ]);
        if ($res['status'] < 200 || $res['status'] >= 300) {
            $errors[] = 'Failed to add note.';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'edit_item') {
    $itemId = filter_input(INPUT_POST, 'item_id', FILTER_VALIDATE_INT);
    $itemName = trim($_POST['item_name'] ?? '');
    $itemPrice = filter_input(INPUT_POST, 'item_price', FILTER_VALIDATE_FLOAT);

    if (!$itemId || $itemName === '' || $itemPrice === false || $itemPrice < 0) {
        $errors[] = 'Invalid item data. Check all fields.';
    } else {
        $res = apiRequest('PUT', '/' . $quoteId . '/line-items/' . $itemId, [
            'item' => $itemName,
            'price' => (float)$itemPrice
        ]);
        if ($res['status'] < 200 || $res['status'] >= 300) {
            $errors[] = 'Failed to update line item.';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'sanction') {
    $discountType = $_POST['discount_type'] ?? '';
    $discountValue = filter_input(INPUT_POST, 'discount_value', FILTER_VALIDATE_FLOAT);

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
        $res = apiRequest('POST', '/' . $quoteId . '/sanction', [
            'discountType' => $discountType,
            'discountAmt' => $discountValue ?: 0
        ]);
        if ($res['status'] >= 200 && $res['status'] < 300) {
            $_SESSION['flash'] = ['type' => 'success', 'message' => "Quote #$quoteId sanctioned successfully."];
            header("Location: review_quotes.php?status=sanctioned");
            exit;
        }
        $errors[] = 'Failed to sanction quote.';
    }
}

$quoteResponse = apiRequest('GET', '/' . $quoteId);
$quote = ($quoteResponse['status'] >= 200 && $quoteResponse['status'] < 300 && is_array($quoteResponse['data']))
    ? $quoteResponse['data']
    : null;

if (!$quote || ($quote['status'] ?? '') !== 'finalized') {
    die('Quote not found or not in finalized status.');
}

$items = $quote['items'] ?? [];
$secretNotes = array_values(array_filter($quote['notes'] ?? [], static function ($n) {
    return !empty($n['is_secret']);
}));

include_once 'header.php';
?>

<h2>Sanction Quote #<?= $quoteId ?></h2>

<?php foreach ($errors as $e): ?>
    <div class="flash error"><?= htmlspecialchars($e) ?></div>
<?php endforeach; ?>

<div class="info-box">
    <strong>Customer:</strong> #<?= (int)($quote['customerID'] ?? 0) ?><br>
    <strong>Current Total:</strong> $<?= number_format((float)$quote['total'], 2) ?>
</div>

<!-- Editable Line Items -->
<h3>Line Items <small style="font-weight:normal; color:#666;">(click Edit to modify)</small></h3>

<?php if (empty($items)): ?>
    <p>No line items on this quote.</p>
<?php else: ?>
<table style="margin-bottom: 25px;">
    <tr><th>#</th><th>Item</th><th>Price</th><th>Edit</th></tr>
    <?php foreach ($items as $i): ?>
    <tr id="row-<?= (int)$i['id'] ?>">
        <td><?= (int)$i['id'] ?></td>
        <td>
            <span class="view"><?= htmlspecialchars($i['item'] ?? '') ?></span>
            <input class="edit" type="text" value="<?= htmlspecialchars($i['item'] ?? '') ?>" style="display:none; width:100%;">
        </td>
        <td>
            <span class="view">$<?= number_format((float)$i['price'], 2) ?></span>
            <input class="edit" type="number" step="0.01" min="0" value="<?= (float)$i['price'] ?>" style="display:none; width:100px;">
        </td>
        <td>
            <button type="button" onclick="startEdit(<?= (int)$i['id'] ?>)" id="btn-edit-<?= (int)$i['id'] ?>">Edit</button>
            <button type="button" onclick="saveEdit(<?= (int)$i['id'] ?>)" id="btn-save-<?= (int)$i['id'] ?>" style="display:none;">Save</button>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

<!-- Hidden form for line item edits -->
<form id="edit-form" method="POST" style="display:none;">
    <input type="hidden" name="action" value="edit_item">
    <input type="hidden" name="item_id" id="f-item-id">
    <input type="hidden" name="item_name" id="f-item-name">
    <input type="hidden" name="item_price" id="f-item-price">
</form>

<!-- SECRET NOTES -->
<div class="form-box" style="margin-bottom:20px;">
    <h3 style="margin-top:0;">Secret Notes (Internal Only)</h3>

    <?php if (empty($secretNotes)): ?>
        <p>No secret notes yet.</p>
    <?php else: ?>
        <?php foreach ($secretNotes as $n): ?>
            <div style="padding:6px 0; border-bottom:1px solid #ddd;">
                <?= nl2br(htmlspecialchars($n['content'])) ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Add Note Form -->
    <form method="POST" style="margin-top:10px;">
        <input type="hidden" name="action" value="add_note">
        <textarea name="note_content" placeholder="Add a secret note..." style="width:100%; height:60px;"></textarea>
        <button type="submit" style="margin-top:5px;">Add Note</button>
    </form>
</div>

<!-- DISCOUNT + SANCTION -->
<div class="form-box">
    <h3 style="margin-top:0;">Apply Discount & Sanction</h3>
    <form method="POST">
        <input type="hidden" name="action" value="sanction">

        <label>Discount Type</label>
        <select name="discount_type" id="disc-type" onchange="document.getElementById('disc-val-row').style.display=this.value?'block':'none'">
            <option value="">None</option>
            <option value="percentage">Percentage (%)</option>
            <option value="amount">Fixed Amount ($)</option>
        </select>

        <div id="disc-val-row" style="display:none;">
            <label>Discount Value</label>
            <input type="number" step="0.01" min="0" name="discount_value" placeholder="0.00">
        </div>

        <button type="submit">Sanction Quote</button>
    </form>
</div>

<script>
function startEdit(id) {
    var row = document.getElementById('row-' + id);
    row.querySelectorAll('.view').forEach(el => el.style.display = 'none');
    row.querySelectorAll('.edit').forEach(el => el.style.display = 'inline-block');
    document.getElementById('btn-edit-' + id).style.display = 'none';
    document.getElementById('btn-save-' + id).style.display = 'inline-block';
}

function saveEdit(id) {
    var row = document.getElementById('row-' + id);
    var name = row.querySelector('.edit[type="text"]').value.trim();
    var price = row.querySelector('.edit[type="number"]').value;
    if (!name || price === '') { alert('Name and price are required.'); return; }
    document.getElementById('f-item-id').value = id;
    document.getElementById('f-item-name').value = name;
    document.getElementById('f-item-price').value = price;
    document.getElementById('edit-form').submit();
}
</script>

<?php include_once 'footer.php'; ?>
