const express = require('express');
const router = express.Router();
const db = require('views/db'); //need to update this for db permissions

// example route
router.get('/test', (req, res) => {
  res.send('API is working');
});

module.exports = router;

router.post('/', async (req, res) => {
  const { associateID, customerID, email } = req.body;

  try {
    const result = await db.query(
      `INSERT INTO quotes 
      (associateID, customerID, email, status, discountType, discountAmt, date, commission)
      VALUES (?, ?, ?, 'draft', 'amount', 0, NOW(), 0)`,
      [associateID, customerID, email]
    );

    res.json({ quoteID: result.insertId });

  } catch (err) {
    res.status(500).send('Error creating quote');
  }
});


router.post('/:id/line-items', async (req, res) => {
  const { id } = req.params;
  const { item, price } = req.body;

  try {
    await db.query(
      `INSERT INTO line_items (quoteID, item, price)
       VALUES (?, ?, ?)`,
      [id, item, price]
    );

    res.send('Line item added');

  } catch (err) {
    res.status(500).send('Error adding item');
  }
});

router.post('/:id/notes', async (req, res) => {
  const { id } = req.params;
  const { content, is_secret } = req.body;

  try {
    await db.query(
      `INSERT INTO notes (quoteID, content, is_secret)
       VALUES (?, ?, ?)`,
      [id, content, is_secret]
    );

    res.send('Note added');

  } catch (err) {
    res.status(500).send('Error adding note');
  }
});


async function calculateTotal(quoteID) {
  const items = await db.query(
    `SELECT price FROM line_items WHERE quoteID = ?`,
    [quoteID]
  );

  let subtotal = 0;
  items.forEach(i => subtotal += i.price);

  const quote = await db.query(
    `SELECT discountType, discountAmt FROM quotes WHERE id = ?`,
    [quoteID]
  );

  const { discountType, discountAmt } = quote[0];

  let total = subtotal;

  if (discountType === 'percentage') {
    total = subtotal - (subtotal * (discountAmt / 100));
  } else {
    total = subtotal - discountAmt;
  }

  return total;
}


router.post('/:id/finalize', async (req, res) => {
  const { id } = req.params;

  try {
    const quote = await db.query(
      `SELECT status, email FROM quotes WHERE id = ?`,
      [id]
    );

    if (quote[0].status !== 'draft') {
      return res.status(400).send('Only draft quotes can be finalized');
    }

    const total = await calculateTotal(id);

    await db.query(
      `UPDATE quotes 
       SET status = 'finalized'
       WHERE id = ?`,
      [id]
    );

    // Email (exclude secret notes)
    const items = await db.query(
      `SELECT item, price FROM line_items WHERE quoteID = ?`,
      [id]
    );

    console.log(`📧 Email sent to ${quote[0].email}`);
    console.log('Items:', items);
    console.log('Total:', total);

    res.json({ message: 'Quote finalized', total });

  } catch (err) {
    res.status(500).send('Finalize failed');
  }
});

router.post('/:id/status', async (req, res) => {
  const { id } = req.params;
  const { newStatus } = req.body;

  const transitions = {
    draft: ['finalized'],
    finalized: ['sanctioned'],
    sanctioned: ['ordered']
  };

  try {
    const quote = await db.query(
      `SELECT status FROM quotes WHERE id = ?`,
      [id]
    );

    const current = quote[0].status;

    if (!transitions[current].includes(newStatus)) {
      return res.status(400).send('Invalid transition');
    }

    await db.query(
      `UPDATE quotes SET status = ? WHERE id = ?`,
      [newStatus, id]
    );

    res.send(`Updated to ${newStatus}`);

  } catch (err) {
    res.status(500).send('Status update failed');
  }
});

router.post('/:id/discount', async (req, res) => {
  const { id } = req.params;
  const { discountType, discountAmt } = req.body;

  try {
    await db.query(
      `UPDATE quotes 
       SET discountType = ?, discountAmt = ?
       WHERE id = ?`,
      [discountType, discountAmt, id]
    );

    res.send('Discount applied');

  } catch (err) {
    res.status(500).send('Discount failed');
  }
});
