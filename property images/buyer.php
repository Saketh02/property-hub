<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buyer Dashboard</title>
    <link rel="stylesheet" href="buyer_styles.css">
    <script>
        function logout() {
            window.location.href = 'logout.php';
        }

        function redirectToDetails() {
            alert('Redirecting to property details page...');
            // Replace with actual redirection logic.
        }

        function addToWishlist(button) {
            alert('Property added to your Wishlist!');
            button.disabled = true;
            button.innerText = "Wish-listed";
        }
    </script>
</head>
<body>
    <header class="site-header">
        <h1>Property Buyer Dashboard</h1>
        <button class="logout-btn" onclick="logout()">Logout</button>
    </header>

    <div class="welcome-note">Welcome to Your Dream Home Hunt!</div>
    <div class="thank-you">Thank you for choosing us. We’re here to help you find the perfect property!</div>

    <div class="search-container">
        <input type="text" placeholder="Search for properties...">
        <button onclick="alert('Searching properties...')">Search</button>
    </div>

    <div class="dashboard-container">
        <!-- Property Card 1 -->
        <div class="property-card" onclick="redirectToDetails()">
            <img src="property.jpg" alt="Property Image">
            <div class="property-details">
                <h3>Urban Apartment</h3>
                <p>Price: $300,000</p>
                <button class="wishlist-btn" onclick="addToWishlist(this); event.stopPropagation();">Add to Wishlist</button>
            </div>
        </div>

        <!-- Property Card 2 -->
        <div class="property-card" onclick="redirectToDetails()">
            <img src="property.jpg" alt="Property Image">
            <div class="property-details">
                <h3>Luxury Condo</h3>
                <p>Price: $500,000</p>
                <button class="wishlist-btn" onclick="addToWishlist(this); event.stopPropagation();">Add to Wishlist</button>
            </div>
        </div>

        <!-- Property Card 3 -->
        <div class="property-card" onclick="redirectToDetails()">
            <img src="property.jpg" alt="Property Image">
            <div class="property-details">
                <h3>Suburban House</h3>
                <p>Price: $450,000</p>
                <button class="wishlist-btn" onclick="addToWishlist(this); event.stopPropagation();">Add to Wishlist</button>
            </div>
        </div>
    </div>
</body>
</html>
