<?php
function getConnection() {
    $conn = oci_connect(
        'your_username', 
        'your_password', 
        'your_connection_string'
    );
    
    if (!$conn) {
        $error = oci_error();
        error_log("Database connection failed: " . $error['message']);
        die("Database connection error. Please try again later.");
    }
    
    return $conn;
}
?>