<?php

session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php?error=unauthorized');
    exit();
}

// Database connection
$servername = "localhost";
$username = "skalikota1";
$password = "skalikota1";
$dbname = "skalikota1";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start a transaction
$conn->begin_transaction();

try {
    // Assuming you have user authentication and can get the current user's ID
    // This might come from a session or login system
    $user_id = $_SESSION['user_id']; // Make sure to have user authentication in place

    // Insert into Properties
    $stmt_properties = $conn->prepare("INSERT INTO Properties (user_id, property_name, location, age_years, floor_area, bedroom_count, bathroom_count) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt_properties->bind_param("isssdii", $user_id, $property_name, $location, $age_years, $floor_area, $bedroom_count, $bathroom_count);
    
    $property_name = $_POST['property_name'];
    $location = $_POST['location'];
    $age_years = $_POST['age_years'];
    $floor_area = $_POST['floor_area'];
    $bedroom_count = $_POST['bedroom_count'];
    $bathroom_count = $_POST['bathroom_count'];
    
    $stmt_properties->execute();
    $property_id = $conn->insert_id;

    // Rest of the code remains the same as your original script
    // Insert into Amenities
    $stmt_amenities = $conn->prepare("INSERT INTO Amenities (id_properties, garden_available, car_parking_slots, proximity, price) VALUES (?, ?, ?, ?, ?)");
    $stmt_amenities->bind_param("iissd", $property_id, $garden_available, $car_parking_slots, $proximity, $price);
    
    $garden_available = $_POST['garden_available'];
    $car_parking_slots = $_POST['car_parking_slots'];
    $proximity = $_POST['proximity'];
    $price = $_POST['price'];
    
    $stmt_amenities->execute();

    // Handle image uploads
    if (!empty($_FILES['property_images']['name'][0])) {
        $stmt_images = $conn->prepare("INSERT INTO PropertyImages (id_properties, image_path, image_name) VALUES (?, ?, ?)");
        $stmt_images->bind_param("iss", $property_id, $image_path, $image_name);

        foreach ($_FILES['property_images']['tmp_name'] as $key => $tmp_name) {
            $image_name = $_FILES['property_images']['name'][$key];
            $upload_dir = 'uploads/properties/' . $property_id . '/';
            
            // Create directory if not exists
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $image_path = $upload_dir . $image_name;
            
            // Move uploaded file
            if (move_uploaded_file($tmp_name, $image_path)) {
                $stmt_images->execute();
            }
        }
        $stmt_images->close();
    }

    // Commit transaction
    $conn->commit();

    // Redirect back to the properties page
    header("Location: seller.php");
    exit();

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo "Error: " . $e->getMessage();
}

// Close statements and connection
$stmt_properties->close();
$stmt_amenities->close();
$conn->close();
?>