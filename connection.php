
<?php
function getConnection() {
    $host = 'localhost';
    $dbname = 'meubles_ecommerce';
    $username = 'root'; 
    $password = '';

    try {
        $pdo = new PDO(
            "mysql:host=$host;dbname=$dbname",
            $username,
            $password,
            [
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false 
            ]
        );
        echo "<script>console.log('✅ Connexion à la base de données réussie');</script>";

        return $pdo;
        
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        throw new Exception("Database connection error"); 
    }
}
?>