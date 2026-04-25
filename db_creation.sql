-- Sales Associate table
CREATE TABLE sales_associates (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  userID VARCHAR(50) UNIQUE NOT NULL,
  password VARCHAR(100) NOT NULL,
  address VARCHAR(200),
  commission DECIMAL(10,2) DEFAULT 0.0 NOT NULL,
  access ENUM('hq', 'sales', 'admin'),
);

-- Quotes Table
CREATE TABLE quotes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  associateID INT NOT NULL,
  customerID INT NOT NULL,
  email VARCHAR(100) NOT NULL,
  status ENUM('draft', 'finalized', 'sanctioned', 'ordered'),
  discountType ENUM('percentage', 'amount'),
  discountAmt DECIMAL(10,2) DEFAULT 0.0,
  date DATETIME DEFAULT current_timestamp(),
  commission DECIMAL(10,2) default 0.00 NOT NULL,
  subtotal DECIMAL(10,2),
  FOREIGN KEY (associateID) REFERENCES sales_associates(id)
);

-- Line Items Table
CREATE TABLE line_items(
  id INT AUTO_INCREMENT PRIMARY KEY,
  quoteID INT NOT NULL,
  item VARCHAR(200),
  price DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (quoteID) REFERENCES quotes(id) ON DELETE CASCADE
);

-- Notes Table
CREATE TABLE notes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  quoteID INT NOT NULL,
  content TEXT,
  is_secret BOOLEAN DEFAULT FALSE,
  FOREIGN KEY (quoteID) REFERENCES quotes(id) ON DELETE CASCADE
);
