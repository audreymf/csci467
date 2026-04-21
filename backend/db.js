const mysql = require('mysql');
const util = require('util');

const pool = mysql.createPool({
  host: process.env.DB_HOST || 'localhost',
  user: process.env.DB_USER || 'root',
  password: process.env.DB_PASSWORD || '',
  database: process.env.DB_NAME || 'quote_system',
  connectionLimit: 10
});

const query = util.promisify(pool.query).bind(pool);

module.exports = {
  query,
  pool
};
