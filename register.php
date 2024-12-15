<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Hub - Register</title>
    <link rel="stylesheet" href="styles.css">
</head>
<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];

    $errors = [];

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $conn = new mysqli('localhost', 'skalikota1', 'skalikota1', 'skalikota1');
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Check for existing username or email
        $stmt = $conn->prepare("SELECT username, email FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($existing_username, $existing_email);
            while ($stmt->fetch()) {
                if ($existing_username === $username) {
                    $errors[] = "Username already taken.";
                }
                if ($existing_email === $email) {
                    $errors[] = "User with given email ID exists.";
                }
            }
        }

        $stmt->close();

        // If no errors, insert into database
        if (empty($errors)) {
            $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $email, $hashed_password, $role);

            if ($stmt->execute()) {
                $stmt->close();
                $conn->close();
                header("Location: login.php?success=User Registration Successful");
                exit();
            } else {
                $errors[] = "Error during registration. Please try again.";
            }
            $stmt->close();
        }

        $conn->close();
    }

    foreach ($errors as $error) {
        echo "<p style='color:red;'>$error</p>";
    }
}
?>
<body>
    <div class="container">
        <h1>Register</h1>
        <form action="register.php" method="POST">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
            
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required >
            <span id="emailError" style="color: red; font-size: 12px;"></span>
            
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            
            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
            
            <label for="role">Role:</label>
            <select id="role" name="role" required>
                <option value="" disabled selected>Select Role</option>
                <option value="Buyer">Buyer</option>
                <option value="Seller">Seller</option>
            </select>
            
            <button type="submit" id="register-button">Register</button>
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </form>
    </div>
</body>
</html>
