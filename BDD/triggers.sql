


--Trigger pour mettre à jour le stock après validation d'une commande
DELIMITER //

CREATE TRIGGER after_order_insert
AFTER INSERT ON order_items
FOR EACH ROW
BEGIN
    UPDATE products
    SET stock_quantity = stock_quantity - NEW.quantity
    WHERE product_id = NEW.product_id;
END //

DELIMITER ;

--Trigger pour empêcher une commande si quantité insuffisante
DELIMITER //

CREATE TRIGGER before_order_item_insert
BEFORE INSERT ON order_items
FOR EACH ROW
BEGIN
    DECLARE available_stock INT;
    DECLARE order_exists INT;
    
    -- Vérifier si la commande existe
    SELECT COUNT(*) INTO order_exists
    FROM orders
    WHERE order_id = NEW.order_id;
    
    -- Vérifier le stock
    SELECT stock_quantity INTO available_stock
    FROM products
    WHERE product_id = NEW.product_id;
    
    IF NEW.quantity > available_stock THEN
        -- Supprimer la commande si elle existe
        IF order_exists > 0 THEN
            DELETE FROM orders WHERE order_id = NEW.order_id;
        END IF;
        
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Quantité demandée supérieure au stock disponible. La commande a été annulée.';
    END IF;
END //

DELIMITER ;


--Trigger pour restaurer le stock après annulation d'une commande
DELIMITER //

CREATE TRIGGER after_order_cancellation
AFTER INSERT ON cancelled_orders
FOR EACH ROW
BEGIN
    -- 1. Restaurer le stock pour chaque produit
    UPDATE products p
    JOIN order_items oi ON p.product_id = oi.product_id
    SET p.stock_quantity = p.stock_quantity + oi.quantity
    WHERE oi.order_id = NEW.order_id;
    
END //

DELIMITER ;


--Trigger pour tracer les commandes annulées
DELIMITER //

CREATE TRIGGER log_cancelled_order
AFTER UPDATE ON orders
FOR EACH ROW
BEGIN
    -- Vérifie si la commande vient d'être marquée comme annulée
    IF NEW.state = 'cancelled' AND OLD.state != 'cancelled' THEN
        -- Insère dans la table historique
        INSERT INTO cancelled_orders (order_id, user_id)
        VALUES (NEW.order_id, NEW.user_id);
        
    END IF;
END//

DELIMITER ;