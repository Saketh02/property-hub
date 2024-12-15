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
    die(json_encode(['error' => 'Connection failed']));
}

// Get property ID from query string
$property_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch property details from both Properties and Amenities tables
$query = "SELECT p.*, a.price, a.garden_available, a.car_parking_slots, a.proximity 
          FROM Properties p 
          JOIN Amenities a ON p.id = a.id_properties 
          WHERE p.id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $property_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $property = $result->fetch_assoc();
    echo json_encode($property);
} else {
    echo json_encode(['error' => 'Property not found']);
}

$stmt->close();
$conn->close();
?>