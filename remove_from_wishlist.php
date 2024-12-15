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

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Get property ID from POST request
$property_id = isset($_POST['property_id']) ? intval($_POST['property_id']) : 0;
$user_id = $_SESSION['user_id'];

// Prepare and execute the delete
$stmt = $conn->prepare("DELETE FROM Wishlist WHERE user_id = ? AND property_id = ?");
$stmt->bind_param("ii", $user_id, $property_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Removed from wishlist']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error removing from wishlist']);
}

$stmt->close();
$conn->close();
?>