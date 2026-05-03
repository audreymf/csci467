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

-- Insertions into sales_associates
INSERT INTO sales_associates (name, userID, password, address, access)
VALUES 
('Audrey Fields', 'audreyf', 'password1', '1234 Audrey St', 'sales'),
('Javon Cherry', 'javonc', 'password4', '1243 Javon St', 'hq'),
('Damian Mendoza', 'damianm', 'password5', '1234 Damian St', 'admin');

-- Insertions into quotes
INSERT INTO quotes (associateID, customerID, email, status, discountType, discountAmt, commission)
VALUES
(1, 1, 'ibm@ibm.com', 'draft', 'percentage', 0.50, 30.00),
(2, 50, 'euro@euro.com', 'ordered', 'amount', 50.00, 1615.00);

-- Insertions into line_items
INSERT INTO line_items (quoteID, item, price)
VALUES
(1, 'inspection', 50.00),
(2, 'hydraulic repair', 10000.00),
(4, 'system calibration', 200.00);

-- Insertions into notes
INSERT INTO notes (quoteID, content, is_secret)
VALUES
(1, 'add discount', 0),
(2, 'determine service fee', 1);
