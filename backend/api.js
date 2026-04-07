const express = require('express');
const router = express.Router();

// example route
router.get('/test', (req, res) => {
  res.send('API is working');
});

module.exports = router;


