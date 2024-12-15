<?php

session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php?error=unauthorized');
    exit();
}


$conn = new mysqli('localhost', 'skalikota1', 'skalikota1', 'skalikota1');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $rating = intval($_POST['rating']);
    $review = $conn->real_escape_string($_POST['review']);

    // Validate input
    if (!empty($name) && $rating > 0 && $rating <= 5 && !empty($review)) {
        // Insert review into database
        $sql = "INSERT INTO reviews (name, rating, review, created_at) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sis', $name, $rating, $review);

        if ($stmt->execute()) {
            echo "<script>alert('Thank you for your review!'); window.location.href='reviews.html';</script>";
        } else {
            echo "<script>alert('Error submitting your review. Please try again.'); window.history.back();</script>";
        }

        $stmt->close();
    } else {
        echo "<script>alert('Invalid input. Please fill out the form correctly.'); window.history.back();</script>";
    }
}

$conn->close();
?>
