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

// Fetch all properties
$properties_query = "SELECT p.id, p.property_name, p.location, a.price, p.bedroom_count, p.bathroom_count, p.age_years, p.floor_area, 
                     a.garden_available, a.car_parking_slots, a.proximity 
                     FROM Properties p 
                     JOIN Amenities a ON p.id = a.id_properties where p.user_id = {$_SESSION['user_id']}";
$properties_result = $conn->query($properties_query);

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
    <title>Seller Property Dashboard</title>
    <link rel="stylesheet" href="seller_styles.css">
</head>
<body>
    <header class="site-header">
        <h1>Property Seller Dashboard</h1>
        <div>
            <button class="logout-btn" onclick="writeReview()">Write a Review</button>
            <button class="logout-btn" onclick="viewReviews()">View Reviews</button>
            <button class="logout-btn" onclick="logout()">Logout</button>
        </div>
    </header>
    
    <div class="container">
        <div class="property-grid">
            <!-- Add Property Card -->
            <div class="add-property-card" onclick="showAddPropertyModal()">
                + Add New Property
            </div>

            <!-- Dynamically Generated Property Cards -->
            <?php 
            if ($properties_result->num_rows > 0) {
                while($property = $properties_result->fetch_assoc()) { 
                    $property_id = $property['id'];
                    $image_path = $property_images[$property_id][0] ?? 'property.jpg';
            ?>
                <div class="property-card">
                    <img src="<?php echo htmlspecialchars($image_path); ?>" alt="Property">
                    <div class="property-details">
                        <h2><?php echo htmlspecialchars($property['property_name']); ?></h2>
                        <p>Location: <?php echo htmlspecialchars($property['location']); ?></p>
                        <p>Price: $<?php echo number_format($property['price']); ?></p>
                        <button onclick="openPropertyModal(<?php echo $property_id; ?>)" class="view-details-btn">
                            View More Details
                        </button>
                    </div>
                </div>
            <?php 
                } 
            }
            ?>
        </div>
    </div>

    <!-- Add New Property Modal -->
    <div id="addPropertyModal" class="property-modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeAddPropertyModal()">&times;</span>
            <h2>Add New Property</h2>
            <form id="addPropertyForm" method="POST" action="add_property.php" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Property Name</label>
                    <input type="text" name="property_name" required>
                </div>
                <div class="form-group">
                    <label>Location</label>
                    <input type="text" name="location" required>
                </div>
                <div class="form-group">
                    <label>Age (Years)</label>
                    <input type="number" class="no-scroll" class="no-scroll" name="age_years" required>
                </div>
                <div class="form-group">
                    <label>Floor Area (sq. ft)</label>
                    <input type="number" class="no-scroll" class="no-scroll" name="floor_area" required>
                </div>
                <div class="form-group">
                    <label>Bedrooms</label>
                    <input type="number" class="no-scroll" class="no-scroll" name="bedroom_count" required>
                </div>
                <div class="form-group">
                    <label>Bathrooms</label>
                    <input type="number" class="no-scroll" class="no-scroll" name="bathroom_count" required>
                </div>
                <div class="form-group">
                    <label>Price</label>
                    <input type="number" class="no-scroll" class="no-scroll" name="price" required>
                </div>
                <div class="form-group">
                    <label>Garden Available</label>
                    <select name="garden_available">
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Parking Slots</label>
                    <input type="number" class="no-scroll" name="car_parking_slots">
                </div>
                <div class="form-group">
                    <label>Proximity</label>
                    <textarea name="proximity"></textarea>
                </div>
                <div class="form-group">
                    <label>Property Images</label>
                    <input type="file" name="property_images[]" multiple accept="image/*">
                </div>
                <button type="submit" class="submit-btn">Add Property</button>
            </form>
        </div>
    </div>

    <!-- Property Detail Modals (Dynamically Generated) -->
    <?php 
    // Reset the properties result pointer
    $properties_result->data_seek(0);
    while($property = $properties_result->fetch_assoc()) { 
        $property_id = $property['id'];
        $images = $property_images[$property_id] ?? [];
    ?>
    <div id="propertyModal<?php echo $property_id; ?>" class="property-modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closePropertyModal(<?php echo $property_id; ?>)">&times;</span>
            <h2><?php echo htmlspecialchars($property['property_name']); ?></h2>
            
            <!-- Image Gallery -->
            <div class="property-image-gallery">
                <?php foreach($images as $image): ?>
                    <div class="gallery-image-container">
                        <img src="<?php echo htmlspecialchars($image); ?>" alt="Property Image" class="gallery-image">
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="modal-details">
                <div>
                    <p><strong>Location:</strong> <?php echo htmlspecialchars($property['location']); ?></p>
                    <p><strong>Age:</strong> <?php echo htmlspecialchars($property['age_years']); ?> years</p>
                    <p><strong>Floor Plan:</strong> <?php echo number_format($property['floor_area']); ?> sq. ft.</p>
                    <p><strong>Bedrooms:</strong> <?php echo $property['bedroom_count']; ?></p>
                </div>
                <div>
                    <p><strong>Bathrooms:</strong> <?php echo $property['bathroom_count']; ?></p>
                    <p><strong>Garden:</strong> <?php echo $property['garden_available'] ? 'Yes' : 'No'; ?></p>
                    <p><strong>Parking:</strong> <?php echo $property['car_parking_slots']; ?>-car garage</p>
                    <p><strong>Proximity:</strong> <?php echo htmlspecialchars($property['proximity']); ?></p>
                </div>
            </div>
            <div>
                <p><strong>Property Price:</strong> $<?php echo number_format($property['price']); ?></p>
                <div class="modal-actions">
                    <button class="update-btn" onclick="openEditPropertyModal(<?php echo $property_id; ?>)">Update</button>
                    <button class="delete-btn" onclick="deleteProperty(<?php echo $property_id; ?>)">Delete</button>
                </div>
            </div>
        </div>
    </div>
    <?php } ?>

    <!-- Edit Property Modal -->
    <div id="editPropertyModal" class="property-modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeEditPropertyModal()">&times;</span>
            <h2>Edit Property</h2>
            <form id="editPropertyForm" method="POST" action="update_property.php" enctype="multipart/form-data">
                <input type="hidden" name="property_id" id="editPropertyId">
                <div class="form-group">
                    <label>Property Name</label>
                    <input type="text" name="property_name" id="editPropertyName" required>
                </div>
                <div class="form-group">
                    <label>Location</label>
                    <input type="text" name="location" id="editLocation" required>
                </div>
                <div class="form-group">
                    <label>Age (Years)</label>
                    <input type="number" class="no-scroll" name="age_years" id="editAgeYears" required>
                </div>
                <div class="form-group">
                    <label>Floor Area (sq. ft)</label>
                    <input type="number" class="no-scroll" name="floor_area" id="editFloorArea" required>
                </div>
                <div class="form-group">
                    <label>Bedrooms</label>
                    <input type="number" class="no-scroll" name="bedroom_count" id="editBedroomCount" required>
                </div>
                <div class="form-group">
                    <label>Bathrooms</label>
                    <input type="number" class="no-scroll" name="bathroom_count" id="editBathroomCount" required>
                </div>
                <div class="form-group">
                    <label>Price</label>
                    <input type="number" class="no-scroll" name="price" id="editPrice" required>
                </div>
                <div class="form-group">
                    <label>Garden Available</label>
                    <select name="garden_available" id="editGardenAvailable">
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Parking Slots</label>
                    <input type="number" class="no-scroll" name="car_parking_slots" id="editParkingSlots">
                </div>
                <div class="form-group">
                    <label>Proximity</label>
                    <textarea name="proximity" id="editProximity"></textarea>
                </div>
                <div class="form-group">
                    <label>Property Images</label>
                    <input type="file" name="property_images[]" multiple accept="image/*">
                </div>
                <button type="submit" class="submit-btn">Update Property</button>
            </form>
        </div>
    </div>

    <script>
        function showAddPropertyModal() {
            document.getElementById('addPropertyModal').style.display = 'block';
        }

        function closeAddPropertyModal() {
            document.getElementById('addPropertyModal').style.display = 'none';
        }

        function openPropertyModal(id) {
            document.getElementById(`propertyModal${id}`).style.display = 'block';
        }

        function closePropertyModal(id) {
            document.getElementById(`propertyModal${id}`).style.display = 'none';
        }

        function openEditPropertyModal(id) {
            // Fetch property details via AJAX
            fetch(`get_property_details.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    // Populate edit form with fetched data
                    document.getElementById('editPropertyName').value = data.property_name;
                    document.getElementById('editPropertyId').value = id;
                    document.getElementById('editLocation').value = data.location;
                    document.getElementById('editAgeYears').value = data.age_years;
                    document.getElementById('editFloorArea').value = data.floor_area;
                    document.getElementById('editBedroomCount').value = data.bedroom_count;
                    document.getElementById('editBathroomCount').value = data.bathroom_count;
                    document.getElementById('editPrice').value = data.price;
                    document.getElementById('editGardenAvailable').value = data.garden_available;
                    document.getElementById('editParkingSlots').value = data.car_parking_slots;
                    document.getElementById('editProximity').value = data.proximity;

                    // Show edit modal
                    document.getElementById('editPropertyModal').style.display = 'block';
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to fetch property details');
                });
        }

        function closeEditPropertyModal() {
            document.getElementById('editPropertyModal').style.display = 'none';
        }

        function logout() {
            window.location.href = 'logout.php';
        }

        function writeReview() {
            window.location.href = 'reviews.html';
        }

        function viewReviews() {
            window.location.href = 'fetch_reviews.php';
        }

        const numberFields = document.querySelectorAll('.no-scroll');
  
        // Attach the 'wheel' event listener to each field
        numberFields.forEach(field => {
            field.addEventListener('wheel', function(event) {
            event.preventDefault();
            });
        });

        function deleteProperty(id) {
            if(confirm('Are you sure you want to delete this property?')) {
                // Implement delete functionality via AJAX or form submission
                fetch(`delete_property.php?id=${id}`)
                    .then(response => response.json())
                    .then(data => {
                        if(data.success) {
                            location.reload();
                        } else {
                            alert('Failed to delete property');
                        }
                    });
            }
        }

        // Close modal if clicked outside
        window.onclick = function(event) {
            const modals = document.getElementsByClassName('property-modal');
            for (let modal of modals) {
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            }
        }
    </script>

    <?php 
    // Close database connection
    $conn->close(); 
    ?>
</body>
</html>