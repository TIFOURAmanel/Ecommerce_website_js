
-- Create sequence for auto-incrementing IDs
CREATE SEQUENCE user_id_seq START WITH 1 INCREMENT BY 1;
CREATE SEQUENCE category_id_seq START WITH 1 INCREMENT BY 1;
CREATE SEQUENCE product_id_seq START WITH 1 INCREMENT BY 1;
CREATE SEQUENCE order_id_seq START WITH 1 INCREMENT BY 1;
CREATE SEQUENCE order_items_id_seq START WITH 1 INCREMENT BY 1;
CREATE SEQUENCE shopping_carts_id_seq START WITH 1 INCREMENT BY 1;
CREATE SEQUENCE cart_items_id_seq START WITH 1 INCREMENT BY 1;
CREATE SEQUENCE cancelled_orders_id_seq START WITH 1 INCREMENT BY 1;


CREATE TABLE users (
    user_id NUMBER DEFAULT user_id_seq.NEXTVAL PRIMARY KEY,
    email VARCHAR2(100) NOT NULL UNIQUE,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    password_hash VARCHAR2(255) NOT NULL,
    address VARCHAR2(255),
    city VARCHAR2(50),
    country VARCHAR2(50),
    phone VARCHAR2(20),
    is_admin NUMBER(1) DEFAULT 0 NOT NULL CHECK (is_admin IN (0, 1))
);

CREATE TABLE categories (
    category_id NUMBER DEFAULT category_id_seq.NEXTVAL PRIMARY KEY,
    name VARCHAR2(100) NOT NULL
    );


CREATE TABLE products (
    product_id NUMBER DEFAULT product_id_seq.NEXTVAL PRIMARY KEY,
    category_id NUMBER REFERENCES categories(category_id),
    name VARCHAR2(100) NOT NULL,
    description VARCHAR2(500),
    price NUMBER(10, 4) NOT NULL CHECK (price >= 0),
    stock_quantity NUMBER NOT NULL CHECK (stock_quantity >= 0),
    image_url VARCHAR2(255)
);

CREATE TABLE orders (
    order_id NUMBER DEFAULT order_id_seq.NEXTVAL PRIMARY KEY,
    user_id NUMBER REFERENCES users(user_id),
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR2(20) DEFAULT 'pending' CHECK (status IN ('pending', 'processing', 'shipped', 'delivered', 'cancelled')),
    address VARCHAR2(255) NOT NULL,
    payment_method VARCHAR2(50) NOT NULL
);

CREATE TABLE order_items (
    order_item_id NUMBER DEFAULT order_items_id_seq.NEXTVAL PRIMARY KEY,
    order_id NUMBER,              
    product_id NUMBER,               
    quantity INT CHECK (quantity >= 0),             
    price NUMBER(10,4), 
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);


CREATE TABLE shopping_carts (
    cart_id NUMBER DEFAULT shopping_carts_id_seq.NEXTVAL PRIMARY KEY, 
    user_id NUMBER REFERENCES users(user_id)
);

CREATE TABLE cart_items (
    cart_item_id NUMBER DEFAULT cart_items_id_seq.NEXTVAL PRIMARY KEY,
    cart_id NUMBER,              
    product_id NUMBER,               
    quantity INT CHECK (quantity >= 0),             
    price NUMBER(10,4), 
    FOREIGN KEY (cart_id) REFERENCES shopping_carts(cart_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);


-- Cancelled orders history
CREATE TABLE cancelled_orders (
    cancellation_id NUMBER DEFAULT cancelled_orders_id_seq.NEXTVAL PRIMARY KEY,
    order_id NUMBER NOT NULL,
    user_id NUMBER NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);


-- ***************************************matensaych

--Écrire une procédure qui permet d'afficher l'historique des commandes d'un client.