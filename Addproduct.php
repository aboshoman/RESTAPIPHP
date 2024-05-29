<?php
// Allow cross-origin requests
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}

// Handle OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    exit(0);
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the POST data
    $price = isset($_POST['price']) ? $_POST['price'] : "";
    $name = isset($_POST['name']) ? $_POST['name'] : "";
    $rate = isset($_POST['rate']) ? $_POST['rate'] : "";
    $sales = isset($_POST['sales']) ? $_POST['sales'] : "";

    // Check if an image file was uploaded
    if (isset($_FILES['product_img'])) {
        $product_img = $_FILES['product_img']['name']; // Get the name of the uploaded file
        $temp_name = $_FILES['product_img']['tmp_name']; // Get the temporary file name

        // Specify the target directory where the file should be saved
        $target_dir = __DIR__ . '/uploads/';
        $target_file = $target_dir . basename($product_img);

        // Move the uploaded file to the target directory
        if (!move_uploaded_file($temp_name, $target_file)) {
            echo "Failed to move uploaded file";
            exit();
        }
    } else {
        $product_img = ""; // Set default value if no image was uploaded
    }

    // Your MySQL connection logic here
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "appwebrestapi";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare SQL statement to insert data into the products table
    $sql = "INSERT INTO products (product_img, price, name, rate, sales)
            VALUES ('$product_img', '$price', '$name', '$rate', '$sales')";

    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    // Close connection
    $conn->close();
} else {
    // Return error response if request method is not POST
    http_response_code(405);
    echo "Method Not Allowed";
}
?>
