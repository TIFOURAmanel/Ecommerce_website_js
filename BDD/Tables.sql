
-- ***************************************matensaych

--Écrire une procédure qui permet d'afficher l'historique des commandes d'un client.


CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    password_hash VARCHAR(255) NOT NULL,
    addresse VARCHAR(255),
    city VARCHAR(50),
    country VARCHAR(50),
    phone VARCHAR(20),
    role_user ENUM('client', 'admin') DEFAULT 'client'
);

CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    name_categ VARCHAR(100) NOT NULL ,
    description_categ VARCHAR(500)
);

CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name_prod VARCHAR(100) NOT NULL,
    description_prod VARCHAR(500),
    price DECIMAL(16, 4) NOT NULL CHECK (price >= 0),
    stock_quantity INT NOT NULL CHECK (stock_quantity >= 0),
    image_url VARCHAR(255),
    FOREIGN KEY (category_id) REFERENCES categories(category_id)
);

CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    address VARCHAR(255) NOT NULL,
    total_amount FLOAT ,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

CREATE TABLE order_items (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,              
    product_id INT,               
    quantity INT CHECK (quantity >= 0),             
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);

CREATE TABLE shopping_carts (
    cart_id INT AUTO_INCREMENT PRIMARY KEY, 
    user_id INT,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

CREATE TABLE cart_items (
    cart_item_id INT AUTO_INCREMENT PRIMARY KEY,
    cart_id INT,              
    product_id INT,               
    quantity INT CHECK (quantity >= 0),             
    price DECIMAL(10,4), 
    FOREIGN KEY (cart_id) REFERENCES shopping_carts(cart_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);


CREATE TABLE cancelled_orders (
    cancellation_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    user_id INT NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);