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
    die(json_encode(['success' => false, 'message' => "Connection failed"]));
}

// Get property ID
$property_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($property_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid property ID']);
    exit;
}

// Prepare and execute delete
$stmt = $conn->prepare("DELETE FROM Properties WHERE id = ?");
$stmt->bind_param("i", $property_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete property']);
}

$stmt->close();
$conn->close();
?>