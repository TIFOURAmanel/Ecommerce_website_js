<?php
session_start();
require_once('connection.php');

// Debug initial
error_log("Début traitement panier - Method: ".$_SERVER['REQUEST_METHOD']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation des données
    if (empty($_POST['product_id'])) {
        die("ID produit manquant");
    }

    $productId = (int)$_POST['product_id'];
    $quantity = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    // Vérification panier
    if (empty($_SESSION['cart_id'])) {
        die("Panier non initialisé");
    }
    $cartId = $_SESSION['cart_id'];

    $pdo = null;
    try {
        $pdo = getConnection();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // 1. Vérifier que le produit existe
        $stmt = $pdo->prepare("SELECT product_id, price, name_prod FROM products WHERE product_id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();

        if (!$product) {
            throw new Exception("Produit introuvable: ".$productId);
        }

        // 2. Traitement selon l'action
        if ($action === 'add') {
            error_log("Tentative d'ajout produit $productId au panier $cartId");
            
            // Vérifier si existe déjà dans le panier
            $stmt = $pdo->prepare("SELECT quantity FROM cart_items 
                                 WHERE cart_id = ? AND product_id = ? FOR UPDATE"); // Verrouillage
            $stmt->execute([$cartId, $productId]);
            $existingItem = $stmt->fetch();

            if ($existingItem) {
                // Mise à jour
                $newQty = $existingItem['quantity'] + $quantity;
                $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? 
                                      WHERE cart_id = ? AND product_id = ?");
                $stmt->execute([$newQty, $cartId, $productId]);
                
                error_log("Produit mis à jour: $productId, nouvelle quantité: $newQty");
            } else {
                // Insertion
                $stmt = $pdo->prepare("INSERT INTO cart_items 
                                      (cart_id, product_id, quantity, price) 
                                      VALUES (?, ?, ?, ?)");
                $stmt->execute([$cartId, $productId, $quantity, $product['price']]);
                
                error_log("Nouveau produit ajouté: $productId");
            }

            // Mise à jour session
            $_SESSION['basket'][$productId] = [
                'quantity' => $existingItem ? $newQty : $quantity,
                'price' => $product['price'],
                'name' => $product['name_prod']
            ];

        } elseif ($action === 'remove') {
            // Suppression
            $stmt = $pdo->prepare("DELETE FROM cart_items 
                                  WHERE cart_id = ? AND product_id = ?");
            $stmt->execute([$cartId, $productId]);
            
            // Mise à jour session
            unset($_SESSION['basket'][$productId]);
            
            error_log("Produit supprimé: $productId");
        }

    } catch (PDOException $e) {
        error_log("ERREUR BDD: ".$e->getMessage());
        if ($pdo && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['error'] = "Erreur technique - veuillez réessayer";
    } catch (Exception $e) {
        error_log("ERREUR: ".$e->getMessage());
        $_SESSION['error'] = $e->getMessage();
    } finally {
        if ($pdo && $pdo->inTransaction()) {
            $pdo->commit();
        }
    }
}

// Redirection avec debug
error_log("Redirection vers: ".$_SERVER['HTTP_REFERER']);
header("Location: ".$_SERVER['HTTP_REFERER']);
exit();
?>