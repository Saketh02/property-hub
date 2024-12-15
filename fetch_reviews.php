<?php

session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php?error=unauthorized');
    exit();
}

$conn = new mysqli('localhost', 'skalikota1', 'skalikota1', 'skalikota1');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch reviews
$sql = "SELECT name, rating, review, created_at FROM reviews ORDER BY created_at DESC";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviews</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4a90e2;
            --accent-color: #3498db;
            --background-color: #f4f7f9;
            --card-color: #ffffff;
            --text-primary: #2c3e50;
            --text-secondary: #34495e;
            --star-color: #ffc107;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--background-color);
            color: var(--text-primary);
            line-height: 1.7;
            padding: 2rem;
        }

        .reviews-wrapper {
            max-width: 80%;
            margin: 0 auto;
            margin-top:100px;
        }

        .review-card {
            background-color: var(--card-color);
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .review-header h3 {
            font-size: 1.5rem;
            color: var(--primary-color);
        }

        .rating-stars {
            color: var(--star-color);
        }

        .review-content {
            font-size: 1rem;
            color: var(--text-secondary);
            margin-bottom: 1rem;
        }

        .review-footer {
            font-size: 0.9rem;
            color: var(--text-secondary);
            text-align: right;
        }

        h1, .no-reviews {
            margin:15px;
            text-align:center;
        }

        .site-header {
            background-color: #2c3e50;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 5%;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            position: fixed; 
            top: 0; 
            left: 0;
            right: 0;
            z-index: 1000;
            height: 60px;
        }

        .site-header h1 {
            margin: 0;
            font-size: 1.5em;
        }

        .logout-btn {
            background-color: var(--accent-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }

        .logout-btn:hover {
            background-color: #2980b9;
        }

        .buttons {
            display: flex;
            gap: 10px;
            position: relative;
            left: 85%;
        }

        .logout-btn {
            background-color: var(--accent-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }

        .logout-btn:hover {
            background-color: #2980b9;
        }

    </style>
</head>
<body>
    <header class="site-header">
        <div class="buttons">
            <button class="logout-btn" onclick="goToDashboard()">Dashboard</button>
            <button class="logout-btn" onclick="logout()">Logout</button>
        </div>
    </header>
    <div class="reviews-wrapper">
        <h1>User Reviews</h1>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="review-card">
                    <div class="review-header">
                        <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                        <div class="rating-stars">
                            <?php for ($i = 0; $i < $row['rating']; $i++): ?>
                                &#9733;
                            <?php endfor; ?>
                        </div>
                    </div>
                    <div class="review-content">
                        <?php 
                            $review = stripslashes($row['review']);
                            echo nl2br(htmlspecialchars($review));?>
                    </div>
                    <div class="review-footer">
                        <?php echo date('F j, Y, g:i a', strtotime($row['created_at'])); ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="no-reviews">No reviews yet. Be the first to leave one!</p>
        <?php endif; ?>
    </div>
    <script>
        function logout() {
            window.location.href = 'logout.php';
        }

        function goToDashboard() {
            window.location.href = 'dashboard.php';
        }
    </script>
</body>
</html>

<?php $conn->close(); ?>
