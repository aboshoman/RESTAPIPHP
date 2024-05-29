    <?php
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');    // cache for 1 day
    }

    // Access-Control headers are received during OPTIONS requests
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
            header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
            header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

        exit(0);
    }

    // Check if the request method is GET
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
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

        // Prepare SQL statement to fetch products from the products table
        $sql = "SELECT * FROM products";

        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // Initialize an array to store the products
            $products = array();

            // Fetch products from the result set
            while($row = $result->fetch_assoc()) {
                // Add each product to the products array
                $products[] = $row;
            }

            // Close connection
            $conn->close();

            // Convert the products array to JSON format
            $response = json_encode($products);

            // Send the JSON response
            header('Content-Type: application/json');
            echo $response;
        } else {
            // Close connection
            $conn->close();

            // If no products found, send an empty JSON array
            echo json_encode(array());
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Define the upload directory
        $uploadDir = __DIR__ . '/uploads/';


        // Check if the directory exists, create it if it doesn't
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Check for errors
        if ($_FILES['product_img']['error'] !== UPLOAD_ERR_OK) {
            echo "File upload failed with error code: " . $_FILES['product_img']['error'];
            exit();
        }

        // Move the uploaded file to the upload directory
        $targetPath = $uploadDir . basename($_FILES['product_img']['name']);
        if (!move_uploaded_file($_FILES['product_img']['tmp_name'], $targetPath)) {
            echo "Failed to move uploaded file";
            exit();
        }

        // File upload successful, continue with other database operations
        // Your database insertion logic goes here
    } else {
        // Return error response if request method is not GET or POST
        http_response_code(405);
        echo "Method Not Allowed";
    }
    ?>
