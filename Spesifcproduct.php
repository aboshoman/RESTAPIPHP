<?php
// Allow cross-origin requests from your React application
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Check if the request method is GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Check if the product ID is provided in the request
    if(isset($_GET['id'])) {
        $id = $_GET['id'];  
        
        // Include your database connection file
        require_once("DBConnect.php");

        // Check if the connection is established
        if($conn) {
            // Query to fetch the product from the database using its ID
            $query = "SELECT * FROM products WHERE id = :id";

            // Prepare the query
            $stmt = $conn->prepare($query);

            // Bind the parameter
            $stmt->bindParam(':id', $id);

            // Execute the query
            if ($stmt->execute()) {
                // Check if any rows were returned
                if($stmt->rowCount() > 0) {
                    // Fetch the product data
                    $product = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    // Return the product data as JSON
                    echo json_encode($product);
                } else {
                    // No product found with the provided ID
                    http_response_code(404);
                    echo json_encode(array("message" => "No product found with ID $id"));
                }
            } else {
                // Error executing the query
                http_response_code(500);
                echo json_encode(array("message" => "Error retrieving product"));
            }

            // Close the database connection (not necessary for PDO)
            // $conn = null;
        } else {
            // Connection failed
            http_response_code(500);
            echo json_encode(array("message" => "Database connection failed"));
        }
    } else {
        // Product ID not provided
        http_response_code(400);
        echo json_encode(array("message" => "Product ID not provided"));
    }
} else {
    // Return error response if request method is not GET
    http_response_code(405);
    echo json_encode(array("message" => "Method Not Allowed"));
}
?>
