const express = require('express');
const router = express.Router();
const db = require('./db');

router.get('/test', (req, res) => {
  res.send('API is working');
});

function computeTotal(items, discountType, discountAmt) {
  const subtotal = items.reduce((sum, i) => sum + Number(i.price || 0), 0);
  const discountValue = Number(discountAmt || 0);
  let total = subtotal;

  if (discountType === 'percentage') {
    total = subtotal - subtotal * (discountValue / 100);
  } else if (discountType === 'amount') {
    total = subtotal - discountValue;
  }

  return {
    subtotal: Number(subtotal.toFixed(2)),
    total: Number(Math.max(0, total).toFixed(2))
  };
}

function logFakeEmail(eventName, email, payload) {
  const safePayload = { ...payload };
  // Explicitly ensure no secret notes are ever logged in email content.
  delete safePayload.secretNotes;
  delete safePayload.notes;
  console.log(`EMAIL_EVENT:${eventName}`);
  console.log(`To: ${email}`);
  console.log('Payload:', safePayload);
}

async function getQuoteById(id) {
  const rows = await db.query(
    `SELECT id, associateID, customerID, email, status, discountType, discountAmt, date, commission
     FROM quotes
     WHERE id = ?`,
    [id]
  );
  return rows[0] || null;
}

async function getLineItemsForQuote(id) {
  return db.query(
    `SELECT id, quoteID, item, price
     FROM line_items
     WHERE quoteID = ?
     ORDER BY id ASC`,
    [id]
  );
}

router.get('/status/:status', async (req, res) => {
  const { status } = req.params;
  const allowed = ['draft', 'finalized', 'sanctioned', 'ordered'];
  if (!allowed.includes(status)) {
    return res.status(400).json({ error: 'Invalid status' });
  }

  try {
    const quotes = await db.query(
      `SELECT id, associateID, customerID, email, status, discountType, discountAmt, date, commission
       FROM quotes
       WHERE status = ?
       ORDER BY date DESC`,
      [status]
    );

    const enriched = await Promise.all(
      quotes.map(async (q) => {
        const items = await getLineItemsForQuote(q.id);
        const totals = computeTotal(items, q.discountType, q.discountAmt);
        return { ...q, ...totals };
      })
    );

    res.json(enriched);
  } catch (err) {
    res.status(500).json({ error: 'Failed to fetch quotes by status' });
  }
});

router.get('/:id', async (req, res) => {
  const { id } = req.params;
  try {
    const quote = await getQuoteById(id);
    if (!quote) {
      return res.status(404).json({ error: 'Quote not found' });
    }
    const items = await getLineItemsForQuote(id);
    const notes = await db.query(
      `SELECT id, quoteID, content, is_secret
       FROM notes
       WHERE quoteID = ?
       ORDER BY id DESC`,
      [id]
    );
    const totals = computeTotal(items, quote.discountType, quote.discountAmt);

    res.json({
      ...quote,
      ...totals,
      items,
      notes
    });
  } catch (err) {
    res.status(500).json({ error: 'Failed to fetch quote' });
  }
});

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
    res.status(500).json({ error: 'Error creating quote' });
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

    res.json({ message: 'Line item added' });

  } catch (err) {
    res.status(500).json({ error: 'Error adding item' });
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

    res.json({ message: 'Note added' });

  } catch (err) {
    res.status(500).json({ error: 'Error adding note' });
  }
});

router.put('/:id/line-items/:itemId', async (req, res) => {
  const { id, itemId } = req.params;
  const { item, price } = req.body;

  if (!item || typeof item !== 'string' || Number(price) < 0) {
    return res.status(400).json({ error: 'Invalid item payload' });
  }

  try {
    const existing = await db.query(
      `SELECT id
       FROM line_items
       WHERE id = ? AND quoteID = ?`,
      [itemId, id]
    );
    if (!existing.length) {
      return res.status(404).json({ error: 'Line item not found for this quote' });
    }

    await db.query(
      `UPDATE line_items
       SET item = ?, price = ?
       WHERE id = ?`,
      [item, price, itemId]
    );

    res.json({ message: 'Line item updated' });
  } catch (err) {
    res.status(500).json({ error: 'Error updating line item' });
  }
});


async function calculateTotal(quoteID) {
  const items = await getLineItemsForQuote(quoteID);
  const quoteRows = await db.query(
    `SELECT discountType, discountAmt FROM quotes WHERE id = ?`,
    [quoteID]
  );
  const quote = quoteRows[0];
  if (!quote) {
    return 0;
  }
  return computeTotal(items, quote.discountType, quote.discountAmt).total;
}

router.post('/:id/finalize', async (req, res) => {
  const { id } = req.params;

  try {
    const quote = await db.query(
      `SELECT status, email FROM quotes WHERE id = ?`,
      [id]
    );

    if (!quote.length || quote[0].status !== 'draft') {
      return res.status(400).json({ error: 'Only draft quotes can be finalized' });
    }

    const total = await calculateTotal(id);

    await db.query(
      `UPDATE quotes 
       SET status = 'finalized'
       WHERE id = ?`,
      [id]
    );

    const items = await db.query(
      `SELECT item, price FROM line_items WHERE quoteID = ?`,
      [id]
    );
    const notes = await db.query(
      `SELECT id, content, is_secret FROM notes WHERE quoteID = ?`,
      [id]
    );
    const publicNotes = notes
      .filter((n) => !n.is_secret)
      .map((n) => ({ id: n.id, content: n.content }));

    logFakeEmail('quote_finalized', quote[0].email, {
      quoteId: Number(id),
      status: 'finalized',
      items: items.map((i) => ({ item: i.item, price: Number(i.price) })),
      publicNotes,
      total: Number(total)
    });

    res.json({ message: 'Quote finalized', total });

  } catch (err) {
    res.status(500).json({ error: 'Finalize failed' });
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

    if (!transitions[current] || !transitions[current].includes(newStatus)) {
      return res.status(400).json({ error: 'Invalid transition' });
    }

    await db.query(
      `UPDATE quotes SET status = ? WHERE id = ?`,
      [newStatus, id]
    );

    res.json({ message: `Updated to ${newStatus}` });

  } catch (err) {
    res.status(500).json({ error: 'Status update failed' });
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

    res.json({ message: 'Discount applied' });

  } catch (err) {
    res.status(500).json({ error: 'Discount failed' });
  }
});

router.post('/:id/sanction', async (req, res) => {
  const { id } = req.params;
  const { discountType = '', discountAmt = 0 } = req.body;
  const normalizedType = discountType || null;

  if (![null, 'percentage', 'amount'].includes(normalizedType)) {
    return res.status(400).json({ error: 'Invalid discountType' });
  }

  try {
    const quote = await getQuoteById(id);
    if (!quote || quote.status !== 'finalized') {
      return res.status(400).json({ error: 'Quote must be finalized to sanction' });
    }

    await db.query(
      `UPDATE quotes
       SET status = 'sanctioned',
           discountType = ?,
           discountAmt = ?
       WHERE id = ?`,
      [normalizedType, Number(discountAmt || 0), id]
    );

    const items = await getLineItemsForQuote(id);
    const totals = computeTotal(items, normalizedType, Number(discountAmt || 0));
    const notes = await db.query(
      `SELECT id, content, is_secret FROM notes WHERE quoteID = ?`,
      [id]
    );
    const publicNotes = notes
      .filter((n) => !n.is_secret)
      .map((n) => ({ id: n.id, content: n.content }));

    logFakeEmail('quote_sanctioned', quote.email, {
      quoteId: Number(id),
      status: 'sanctioned',
      discountType: normalizedType,
      discountAmt: Number(discountAmt || 0),
      items: items.map((i) => ({ item: i.item, price: Number(i.price) })),
      publicNotes,
      subtotal: totals.subtotal,
      total: totals.total
    });

    res.json({ message: 'Quote sanctioned', ...totals });
  } catch (err) {
    res.status(500).json({ error: 'Sanction failed' });
  }
});

router.post('/:id/order', async (req, res) => {
  const { id } = req.params;
  const { finalDiscountType = '', finalDiscountAmt = 0, commissionRate = 10 } = req.body;
  const normalizedType = finalDiscountType || null;

  if (![null, 'percentage', 'amount'].includes(normalizedType)) {
    return res.status(400).json({ error: 'Invalid finalDiscountType' });
  }

  try {
    const quote = await getQuoteById(id);
    if (!quote || quote.status !== 'sanctioned') {
      return res.status(400).json({ error: 'Quote must be sanctioned to order' });
    }

    const items = await getLineItemsForQuote(id);
    const totals = computeTotal(items, normalizedType ?? quote.discountType, normalizedType ? finalDiscountAmt : quote.discountAmt);
    const commission = Number((totals.total * (Number(commissionRate) / 100)).toFixed(2));

    await db.query(
      `UPDATE quotes
       SET status = 'ordered',
           discountType = ?,
           discountAmt = ?,
           commission = ?
       WHERE id = ?`,
      [normalizedType ?? quote.discountType, normalizedType ? Number(finalDiscountAmt || 0) : Number(quote.discountAmt || 0), commission, id]
    );

    const notes = await db.query(
      `SELECT id, content, is_secret FROM notes WHERE quoteID = ?`,
      [id]
    );
    const publicNotes = notes
      .filter((n) => !n.is_secret)
      .map((n) => ({ id: n.id, content: n.content }));
    const processingDate = new Date().toISOString().slice(0, 10);

    logFakeEmail('purchase_order_created', quote.email, {
      quoteId: Number(id),
      status: 'ordered',
      items: items.map((i) => ({ item: i.item, price: Number(i.price) })),
      publicNotes,
      processingDate,
      commissionRate: Number(commissionRate),
      commissionAmount: commission,
      finalAmount: totals.total
    });

    res.json({
      message: 'Quote converted to order',
      processingDate,
      commissionRate: Number(commissionRate),
      commissionAmount: commission,
      finalAmount: totals.total
    });
  } catch (err) {
    res.status(500).json({ error: 'Order conversion failed' });
  }
});

module.exports = router;
