<?php
session_start();

if (!isset($_SESSION['username'])) {
    // Redirect to login page if not logged in
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

// First, check if the property exists
$check_property = $conn->prepare("SELECT id FROM Properties WHERE id = ?");
$check_property->bind_param("i", $property_id);
$check_property->execute();
$check_property->store_result();

if ($check_property->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Property does not exist']);
    $check_property->close();
    $conn->close();
    exit();
}
$check_property->close();

// Then, check if already in wishlist
$check_wishlist = $conn->prepare("SELECT id FROM Wishlist WHERE user_id = ? AND property_id = ?");
$check_wishlist->bind_param("ii", $user_id, $property_id);
$check_wishlist->execute();
$check_wishlist->store_result();

if ($check_wishlist->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Property is already in your wishlist']);
    $check_wishlist->close();
    $conn->close();
    exit();
}
$check_wishlist->close();

// If not in wishlist, insert
$stmt = $conn->prepare("INSERT INTO Wishlist (user_id, property_id) VALUES (?, ?)");
$stmt->bind_param("ii", $user_id, $property_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Added to wishlist']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error adding to wishlist']);
}

$stmt->close();
$conn->close();
?>