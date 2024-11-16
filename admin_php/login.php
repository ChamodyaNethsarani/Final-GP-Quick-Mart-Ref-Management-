<?php
// Start the session
session_start();

include '../db_connection.php';



// Check if admin exists
$admin_email = 'admin@quickmart.com';
$admin_password = 'admin123'; // Default password
$hashed_password = password_hash($admin_password, PASSWORD_DEFAULT); // Hash the default password

$sql_check_admin = "SELECT * FROM employee WHERE email = ? AND position = 'Admin'";
$stmt = $conn->prepare($sql_check_admin);
$stmt->bind_param("s", $admin_email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    // If no admin exists, create the default admin
    $sql_insert_admin = "INSERT INTO employee (username, email, password, position) 
                         VALUES ('admin', ?, ?, 'Admin')";
    $stmt_insert = $conn->prepare($sql_insert_admin);
    $stmt_insert->bind_param("ss", $admin_email, $hashed_password);
    $stmt_insert->execute();
    echo "Default admin created with email: $admin_email and password: $admin_password";
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // SQL query to retrieve the user based on the email using a prepared statement
    $sql = "SELECT * FROM employee WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // User exists
        $row = $result->fetch_assoc();
        $stored_password = $row['password'];
        $position = $row['position'];
        $username = $row['username']; // Fetch the username from the database

        // Verify the password
        if (password_verify($password, $stored_password)) {
    // Store user details in session
    $_SESSION['user_id'] = $row['employee_id'];
    $_SESSION['user_email'] = $row['email'];
    $_SESSION['user_position'] = $row['position'];
    $_SESSION['user_name'] = $row['username']; // Store the username

    // Debugging: Print session variables to confirm they are set
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";

    // Redirect based on user position
    if ($position == 'Admin') {
        header("Location: admin_home.php");
    } else {
        header("Location: ../emp_php/emp_home.php");
    }
    exit();
}
 else {
            echo "<script>alert('Invalid password.');</script>";
        }
    } else {
        echo "<script>alert('No account found with that email.');</script>";
    }

    // Close the statement
    $stmt->close();
}

// Close connection
$conn->close();
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Form</title>
    <link rel="stylesheet" href="../admin_css/login.css">

</head>
<body>
    <div class="login-container">
        <h2>User Login</h2>
        <form action="login.php" method="post">
            <div class="form-group">
                <span class="icon">&#128100;</span>
                <input type="text" name="email" placeholder="Email" required>
            </div>
            <div class="form-group">
                <span class="icon">&#128274;</span>
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit">Login</button>
        </form>
    </div>
    <script>
         function validateForm() {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            let valid = true;

            // Email format validation
            const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            if (!emailPattern.test(email)) {
                document.getElementById('emailError').innerText = 'Please enter a valid email address.';
                valid = false;
            } else {
                document.getElementById('emailError').innerText = '';
            }

            // Password length validation
            if (password.length < 6) {
                document.getElementById('passwordError').innerText = 'Password must be at least 6 characters long.';
                valid = false;
            } else {
                document.getElementById('passwordError').innerText = '';
            }

            return valid;
        }
    </script>
</body>
</html>


