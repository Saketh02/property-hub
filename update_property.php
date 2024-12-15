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
    die(json_encode([
        'success' => false, 
        'message' => "Connection failed: " . $conn->connect_error
    ]));
}

// Start transaction
$conn->begin_transaction();

try {
    // Validate input
    if (!isset($_POST['property_id']) || empty($_POST['property_id'])) {
        throw new Exception("Invalid property ID");
    }

    $property_id = $conn->real_escape_string($_POST['property_id']);
    
    // Prepare property update data
    $property_name = $conn->real_escape_string($_POST['property_name']);
    $location = $conn->real_escape_string($_POST['location']);
    $age_years = $conn->real_escape_string($_POST['age_years']);
    $floor_area = $conn->real_escape_string($_POST['floor_area']);
    $bedroom_count = $conn->real_escape_string($_POST['bedroom_count']);
    $bathroom_count = $conn->real_escape_string($_POST['bathroom_count']);
    $price = $conn->real_escape_string($_POST['price']);
    $garden_available = $conn->real_escape_string($_POST['garden_available']);
    $car_parking_slots = $conn->real_escape_string($_POST['car_parking_slots']);
    $proximity = $conn->real_escape_string($_POST['proximity']);

    // Update Properties table
    $properties_update_query = "UPDATE Properties SET
        property_name = '$property_name', 
        location = '$location', 
        age_years = '$age_years', 
        floor_area = '$floor_area', 
        bedroom_count = '$bedroom_count', 
        bathroom_count = '$bathroom_count'
        WHERE id = '$property_id'";
    
    if (!$conn->query($properties_update_query)) {
        throw new Exception("Error updating properties: " . $conn->error);
    }

    // Update Amenities table
    $amenities_update_query = "UPDATE Amenities SET 
        price = '$price', 
        garden_available = '$garden_available', 
        car_parking_slots = '$car_parking_slots', 
        proximity = '$proximity'
        WHERE id_properties = '$property_id'";
    
    if (!$conn->query($amenities_update_query)) {
        throw new Exception("Error updating amenities: " . $conn->error);
    }

    // Handle image uploads
    if (!empty($_FILES['property_images']['name'][0])) {
        // Delete existing images
        $delete_images_query = "DELETE FROM PropertyImages WHERE id_properties = '$property_id'";
        if (!$conn->query($delete_images_query)) {
            throw new Exception("Error deleting existing images: " . $conn->error);
        }

        // Upload new images
        $upload_dir = 'uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Process multiple image uploads
        $image_count = count($_FILES['property_images']['name']);
        for ($i = 0; $i < $image_count; $i++) {
            $tmp_name = $_FILES['property_images']['tmp_name'][$i];
            $original_name = $_FILES['property_images']['name'][$i];
            
            // Generate unique filename
            $file_extension = pathinfo($original_name, PATHINFO_EXTENSION);
            $new_filename = uniqid('property_') . '.' . $file_extension;
            $destination = $upload_dir . $new_filename;

            // Move uploaded file
            if (move_uploaded_file($tmp_name, $destination)) {
                // Insert image path into database
                $image_insert_query = "INSERT INTO PropertyImages (id_properties, image_path) VALUES ('$property_id', '$destination')";
                if (!$conn->query($image_insert_query)) {
                    throw new Exception("Error inserting image path: " . $conn->error);
                }
            } else {
                throw new Exception("Failed to upload image: $original_name");
            }
        }
    }

    // Commit transaction
    $conn->commit();

    // Redirect back to dashboard with success message
    header('Location: seller.php?update=success');
    exit();

} catch (Exception $e) {
    // Rollback transaction
    $conn->rollback();

    // Log error (in a production environment, use proper logging)
    error_log($e->getMessage());

    // Redirect back to dashboard with error message
    header('Location: seller.php?update=failed&error=' . urlencode($e->getMessage()));
    exit();
} finally {
    // Close database connection
    $conn->close();
}
?>