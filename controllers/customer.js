/*Database Eng (Role 3)
  Interface w/ legacy DB.
  Retrieves customer records, validates customers
  Used by quotes.js too
*/

var mysql = require('mysql')
var connection = mysql.createConnection({
  host: 'blitz.cs.niu.edu',
  user: 'student',
  password: 'student',
  database: 'csci467'
});

connection.connect();

module.exports = {
    getAll: async result => {
        connection.query('SELECT * FROM customers', function(err, rows){
            if (err) throw err;
            console.log('found ', rows.length, ' customers');
            result(rows);
        });
    }
}
