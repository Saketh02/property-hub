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
    die("Connection failed: " . $conn->connect_error);
}

// Fetch wishlist properties
$user_id = $_SESSION['user_id'];
$wishlist_query = "
    SELECT p.id, p.property_name, p.location, a.price, a.garden_available, 
           a.car_parking_slots, a.proximity, p.bedroom_count, p.bathroom_count, 
           p.age_years, p.floor_area, w.added_at,
           (SELECT image_path FROM PropertyImages pi WHERE pi.id_properties = p.id LIMIT 1) as image_path
    FROM Wishlist w
    JOIN Properties p ON w.property_id = p.id
    JOIN Amenities a ON p.id = a.id_properties
    WHERE w.user_id = ?
    ORDER BY w.added_at DESC
";

$stmt = $conn->prepare($wishlist_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$wishlist_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist</title>
    <link rel="stylesheet" href="buyer_styles.css">
    <script>
        function removeFromWishlist(propertyId) {
            fetch('remove_from_wishlist.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'property_id=' + propertyId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            });
        }

        function logout() {
            // Placeholder for logout functionality
            window.location.href = "logout.php";
        }

        function backToDashboard() {
            // Placeholder for logout functionality
            window.location.href = "buyer.php";
        }
    </script>
</head>
<body>
<header class="site-header">
        <h1>My Wishlist</h1>
        <div class="buttons">
            <button class="back-button" onclick="backToDashboard()">Back to Dashboard</button>
            <button class="logout-btn" onclick="logout()">Logout</button>
        </div>
        
    </header>

    <div class="dashboard-container">
        <?php if ($wishlist_result->num_rows > 0): ?>
            <?php while ($property = $wishlist_result->fetch_assoc()): ?>
                <div class="property-card">
                    <img src="<?php echo htmlspecialchars($property['image_path'] ?? 'default.jpg'); ?>" alt="Property Image">
                    <div class="property-details">
                        <h3><?php echo htmlspecialchars($property['property_name']); ?></h3>
                        <p>Location: <?php echo htmlspecialchars($property['location']); ?></p>
                        <p>Price: $<?php echo number_format($property['price']); ?></p>
                        <p>Added on: <?php echo date('M d, Y', strtotime($property['added_at'])); ?></p>
                        <button class="wishlist-btn" onclick="removeFromWishlist(<?php echo $property['id']; ?>)">Remove from Wishlist</button>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Your wishlist is empty.</p>
        <?php endif; ?>
    </div>
</body>
</html>