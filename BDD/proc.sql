

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

