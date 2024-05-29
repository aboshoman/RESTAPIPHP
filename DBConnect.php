<?php
// Include your database configuration file
require_once 'configurations.php';

try {
    // Create a new PDO connection
    $conn = new PDO(
        "mysql:host=$DATABASE_SERVER_IP;dbname=$DATABASE_NAME", 
        $DATABASE_USER_NAME, 
        $DATABASE_USER_PASSWORD
    );
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //echo "Connected successfully";
} catch(PDOException $e) {
    // Output any connection errors
    echo "Connection failed: " . $e->getMessage();
}
?>
