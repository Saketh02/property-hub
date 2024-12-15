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

// Handle search query
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';

// Fetch properties with their details
$search_query = "
    SELECT p.id, p.property_name, p.location, a.price, a.garden_available, a.car_parking_slots, a.proximity, 
           p.bedroom_count, p.bathroom_count, p.age_years, p.floor_area 
    FROM Properties p 
    JOIN Amenities a ON p.id = a.id_properties
";
if ($search_term) {
    $search_query .= " 
        WHERE p.property_name LIKE ? 
           OR p.location LIKE ? 
           OR a.price LIKE ?";
}

$stmt = $conn->prepare($search_query);
if ($search_term) {
    $like_term = '%' . $search_term . '%';
    $stmt->bind_param('sss', $like_term, $like_term, $like_term);
}
$stmt->execute();
$properties_result = $stmt->get_result();

// Fetch property images
$images_query = "SELECT id_properties, image_path FROM PropertyImages";
$images_result = $conn->query($images_query);

$property_images = [];
while ($img = $images_result->fetch_assoc()) {
    $property_images[$img['id_properties']][] = $img['image_path'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buyer Dashboard</title>
    <link rel="stylesheet" href="buyer_styles.css">

    <script>
        function openPropertyModal(propertyId) {
            document.getElementById(`propertyModal${propertyId}`).style.display = 'block';
        }
        function closePropertyModal(propertyId) {
            document.getElementById(`propertyModal${propertyId}`).style.display = 'none';
        }

        function addToWishlist(propertyId) {
            console.log(propertyId)
            fetch('add_to_wishlist.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'property_id=' + propertyId
            })
            .then(response => response.json())
            .then(data => {
                console.log('Server response:', data);
                if (data.success) {
                    const button = document.querySelector(`.wishlist-btn[onclick*="${propertyId}"]`);
                    if (button) {
                        // Update button appearance and text
                        button.textContent = 'Wishlisted';
                        button.style.backgroundColor = '#ccc'; // Gray out
                        button.style.color = '#000'; // Set text color to black (optional)
                        button.style.cursor = 'not-allowed'; // Change cursor to indicate non-interactivity
                        button.disabled = true; // Prevent further clicks
                    }
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while adding to wishlist');
            });
        }

        function viewWishlist() {
            window.location.href = 'wishlist.php';
        }

        function writeReview() {
            window.location.href = 'reviews.html';
        }

        function viewReviews() {
            window.location.href = 'fetch_reviews.php';
        }

        function logout() {
            // Placeholder for logout functionality
            window.location.href = "logout.php";
        }
    </script>
</head>
<body>
<header class="site-header">
    <h1>Property Buyer Dashboard</h1>
    <div class="buttons">
        <button class="view-wishlist-btn" onclick="viewWishlist()">View Wishlist</button>
        <button class="logout-btn" onclick="writeReview()">Write a Review</button>
        <button class="logout-btn" onclick="viewReviews()">View Reviews</button>
        <button class="logout-btn" onclick="logout()">Logout</button>
    </div>
</header>

    <div class="welcome-note">Welcome to Your Dream Home Hunt!</div>
    <div class="thank-you">Thank you for choosing us. We’re here to help you find the perfect property!</div>

    <div class="search-container">
        <form action="buyer.php" method="GET">
            <input type="text" name="search" placeholder="Search for properties...">
            <button type="submit">Search</button>
        </form>
    </div>

    <div class="dashboard-container">
        <?php
        if ($properties_result->num_rows > 0) {
            while ($property = $properties_result->fetch_assoc()) {
                $property_id = $property['id'];
                $images = $property_images[$property_id] ?? [];
                $image_path = $images[0] ?? 'default.jpg'; // Default image if none exists
        ?>
            <div class="property-card" onclick="openPropertyModal(<?php echo $property_id; ?>)">
                <img src="<?php echo htmlspecialchars($image_path); ?>" alt="Property Image">
                <div class="property-details">
                    <h3><?php echo htmlspecialchars($property['property_name']); ?></h3>
                    <p>Location: <?php echo htmlspecialchars($property['location']); ?></p>
                    <p>Price: $<?php echo number_format($property['price']); ?></p>
                    <button class="wishlist-btn" onclick="addToWishlist(<?php echo  $property_id; ?>); event.stopPropagation();">Add to Wishlist</button>
                </div>
            </div>

            <!-- Property Modal -->
            <div id="propertyModal<?php echo $property_id; ?>" class="property-modal">
                <div class="modal-content">
                    <span class="close-modal" onclick="closePropertyModal(<?php echo $property_id; ?>)">&times;</span>
                    <h2><?php echo htmlspecialchars($property['property_name']); ?></h2>
                    <div class="property-image-gallery">
                        <?php foreach ($images as $image): ?>
                            <img src="<?php echo htmlspecialchars($image); ?>" alt="Property Image">
                        <?php endforeach; ?>
                    </div>
                    <div class="modal-details">
                        <p><strong>Location:</strong> <?php echo htmlspecialchars($property['location']); ?></p>
                        <p><strong>Age:</strong> <?php echo htmlspecialchars($property['age_years']); ?> years</p>
                        <p><strong>Floor Plan:</strong> <?php echo htmlspecialchars($property['floor_area']); ?> sq. ft.</p>
                        <p><strong>Bedrooms:</strong> <?php echo htmlspecialchars($property['bedroom_count']); ?></p>
                        <p><strong>Bathrooms:</strong> <?php echo htmlspecialchars($property['bathroom_count']); ?></p>
                        <p><strong>Garden:</strong> <?php echo htmlspecialchars($property['garden_available'] ? 'Yes' : 'No'); ?></p>
                        <p><strong>Parking:</strong> <?php echo htmlspecialchars($property['car_parking_slots']); ?> slots</p>
                        <p><strong>Proximity:</strong> <?php echo htmlspecialchars($property['proximity']); ?></p>
                        <p><strong>Property Price:</strong> $<?php echo number_format($property['price']); ?></p>
                    </div>
                    <div class="modal-actions">
                        <button class="wishlist-btn" onclick="addToWishlist(<?php echo  $property_id; ?>); event.stopPropagation();">Add to Wishlist</button>
                    </div>
                </div>
            </div>
        <?php
            }
        } else {
            echo "<p>No properties available.</p>";
        }
        ?>
    </div>
</body>
</html>
