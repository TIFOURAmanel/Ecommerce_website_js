

--Procédure pour afficher l'historique des commandes d'un client
DELIMITER //

CREATE PROCEDURE AfficherHistoriqueCommandes(IN p_user_id INT)
BEGIN
    -- Vérifier d'abord si le client existe
    IF NOT EXISTS (SELECT 1 FROM users WHERE user_id = p_user_id) THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Client non trouvé';
    ELSE
        -- Afficher les commandes avec leur statut et les produits associés
        SELECT 
            o.order_id,
            o.order_date,
            o.total_amount,
            o.state AS status,
            GROUP_CONCAT(
                CONCAT(p.name_prod, ' (', oi.quantity, ' × ', p.price, 'da)')
                SEPARATOR ', '
            ) AS produits,
            COUNT(oi.order_item_id) AS nombre_produits
            
        FROM 
            orders o
        LEFT JOIN 
            order_items oi ON o.order_id = oi.order_id
        LEFT JOIN 
            products p ON oi.product_id = p.product_id
        WHERE 
            o.user_id = p_user_id
        GROUP BY 
            o.order_id, o.order_date, o.total_amount, o.state
        ORDER BY 
            o.order_date DESC;
    END IF;
END //

DELIMITER ;



--procédure stockée qui affiche les détails d’une commande pour un client ainsi que le total à payer
DELIMITER //

CREATE PROCEDURE GetCustomerCart(IN p_user_id INT)
BEGIN
    -- Déclarer les variables
    DECLARE v_cart_id INT;
    DECLARE v_cart_exists INT;
    DECLARE customer_exists INT;

    -- Vérifier si le client existe
    SELECT COUNT(*) INTO customer_exists FROM users WHERE user_id = p_user_id;
    
    IF customer_exists = 0 THEN
        SELECT 'Client non trouvé' AS Message;
    ELSE
        -- Vérifier si le panier existe
        SELECT COUNT(*) INTO v_cart_exists FROM shopping_carts WHERE user_id = p_user_id;
        
        IF v_cart_exists = 0 THEN
            SELECT 'Aucun panier trouvé pour ce client' AS Message;
        ELSE
            -- Trouver l'ID du panier du client
            SELECT cart_id INTO v_cart_id FROM shopping_carts WHERE user_id = p_user_id;
        
        
            -- Afficher les informations du panier
            SELECT 
                sc.cart_id,
                CONCAT(u.first_name, ' ', u.last_name) AS client,
                u.email,
                COUNT(ci.cart_item_id) AS nombre_articles,
                SUM(ci.quantity * ci.price) AS total_panier
            FROM 
                shopping_carts sc
            JOIN 
                users u ON sc.user_id = u.user_id
            LEFT JOIN 
                cart_items ci ON sc.cart_id = ci.cart_id
            WHERE 
                sc.user_id = p_user_id;
            
            -- Afficher le détail des articles du panier
            SELECT 
                ci.cart_item_id,
                p.product_id,
                p.name_prod AS nom_produit,
                ci.quantity,
                ci.price AS prix_unitaire,
                (ci.quantity * ci.price) AS prix_total,
                p.description_prod AS description,
                p.image_url
            FROM 
                cart_items ci
            JOIN 
                products p ON ci.product_id = p.product_id
            WHERE 
                ci.cart_id = v_cart_id;
        END IF;
    END IF;
END //

DELIMITER ;


--procédure stockée qui permet de finaliser une commande et de vider le panier une fois la commande validée
DELIMITER //

CREATE PROCEDURE ProcessCustomerOrder(
    IN p_user_id INT,
    IN p_delivery_address VARCHAR(255),
    IN p_total_amount DECIMAL(16,4)
)
BEGIN
    DECLARE v_cart_id INT;
    DECLARE v_order_id INT;
    DECLARE v_done INT DEFAULT FALSE;
    DECLARE v_product_id INT;
    DECLARE v_quantity INT;
    DECLARE v_price DECIMAL(10,4);
    DECLARE v_item_count INT DEFAULT 0;
    
    -- Start transaction
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    -- Get the user's cart ID with validation
    SELECT cart_id INTO v_cart_id 
    FROM shopping_carts 
    WHERE user_id = p_user_id;
    
    IF v_cart_id IS NULL THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'No shopping cart found for this user';
    END IF;
    
    -- Check if cart has items
    SELECT COUNT(*) INTO v_item_count FROM cart_items WHERE cart_id = v_cart_id;
    IF v_item_count = 0 THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Shopping cart is empty';
    END IF;
    
    -- Create the order
    INSERT INTO orders (user_id, address, total_amount, order_date)
    VALUES (p_user_id, p_delivery_address, p_total_amount, NOW());
    
    SET v_order_id = LAST_INSERT_ID();
    
    -- Process cart items directly without cursor (more efficient)
    INSERT INTO order_items (order_id, product_id, quantity)
    SELECT v_order_id, product_id, quantity
    FROM cart_items 
    WHERE cart_id = v_cart_id;
    
    -- Clear the cart
    DELETE FROM cart_items WHERE cart_id = v_cart_id;
    
    -- Commit transaction
    COMMIT;
    
    -- Return the order ID
    SELECT v_order_id AS order_id;
END //

DELIMITER ;