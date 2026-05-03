/*Database Eng (Role 3)
  Interface w/ legacy DB.
  Retrieves customer records, validates customers
  Used by quotes.js too
*/

var mysql = require('mysql')
var legacy_db = mysql.createConnection({
  host: 'blitz.cs.niu.edu',
  user: 'student',
  password: 'student',
  database: 'csci467'
});

// Throw error if we cannot connect to legacy DB
legacy_db.connect(err => {
  if(err) throw err;
  console.log('Connected to legacy database');
});
                  
// same for our DB
system_db.connect(err => {
  if(err) throw err
  console.log('Connected to quote_system database');
});   

// Not sure if we need this here anymore but I added exports for our own tables too
module.exports = {
  getLegacyCustomers: async result => {
    connection.query('SELECT * FROM customers', function(err, rows){
      if (err) throw err;
      console.log('found ', rows.length, ' customers');
      result(rows);
  });
}}
