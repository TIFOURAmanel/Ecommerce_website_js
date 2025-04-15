-- Insert sample categories
INSERT INTO categories (name) VALUES ('');
INSERT INTO categories (name) VALUES ('');
INSERT INTO categories (name) VALUES ('');

-- 

-- Insert sample products
INSERT INTO products (name, description, price, stock_quantity) 
VALUES (' ', '', 699.99, 100);

INSERT INTO products (category_id, name, description, price, stock_quantity) 
VALUES ('', '', 19.99, 200);

-- Create an admin user
INSERT INTO users (username, email, password_hash, is_admin) 
VALUES ('admin', 'admin@example.com', 'hashed_password_here', 1);