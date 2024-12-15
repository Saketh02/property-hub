<?php
session_start();
if (!isset($_SESSION['username'])) {
    // Redirect to login page if not logged in
    header('Location: login.php?error=unauthorized');
    exit();
}

// Database credentials
$hostname = "localhost";
$username_db = "skalikota1";
$password_db = "skalikota1";
$dbname = "skalikota1";

// Establish the database connection
$conn = new mysqli($hostname, $username_db, $password_db, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    echo "<script>alert('You must log in first.'); window.location.href='login.php';</script>";
    exit();
}

// Fetch the username from the session
$username = $_SESSION['username'];

// Prepare and execute the query to fetch user details
$sql = "SELECT * FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();

// Check if the user record exists
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $role = $user['role']; // Assume the role field in your users table is named 'role'

    // Redirect based on the role
    if ($role === 'Buyer') {
        header("Location: buyer.php");
        exit();
    } elseif ($role === 'Seller') {
        header("Location: seller.php");
        exit();
    } else {
        echo "<script>alert('Invalid user role. Please contact support.'); window.location.href='logout.php';</script>";
        exit();
    }
} else {
    echo "<script>alert('User not found.'); window.location.href='logout.php';</script>";
    exit();
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>
