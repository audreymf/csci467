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

// Connection to our created DB
// I did not open this to public networks so it should work
// with the specific login info included here
/*var system_db = mysql.createConnection({
  host: '10.168.191.95',
  port: 3306,
  user: 'team',
  password: 'ege467',
  database: 'quote_system'
});*/

// Throw error if we cannot connect to legacy DB
legacy_db.connect(err => {
  if(err) throw err;
  console.log('Connected to legacy database');
});
                  
// sane for our DB
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
},
  
getSalesAssociates: async result => {
    connection.query('SELECT * FROM sales_associates', function(err, rows){
      if (err) throw err;
      console.log('found ', rows.length, ' sales_associates');
      result(rows);
  });
},

getQuotes: async result => {
  connection.query('SELECT * FROM quotes', function(err, rows){
    if (err) throw err;
    console.log('found ', rows.length, ' quotes');
    result(rows);
  });
},

getLineItems: async result => {
  connection.query('SELECT * FROM line_items', function(err, rows){
    if (err) throw err;
    console.log('found ', rows.length, ' line_items');
    result(rows);
});
},

getNotes: async result => {
  connection.query('SELECT * FROM notes', function(err, rows){
    if (err) throw err;
    console.log('found ', rows.length, ' notes');
    result(rows);
  });
}
}
