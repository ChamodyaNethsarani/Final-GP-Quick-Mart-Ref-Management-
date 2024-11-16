<?php
// Database connection
include '../db_connection.php';


// Handle deletion of record
if (isset($_GET['delete'])) {
    $employee_id = $_GET['delete'];
    
    // Ensure the employee_id is not empty and is a valid integer
    if (!empty($employee_id) && is_numeric($employee_id)) {
        $employee_id = $conn->real_escape_string($employee_id); // Sanitize input
        
        // Use prepared statement for deletion
        $stmt = $conn->prepare("DELETE FROM employee WHERE employee_id=?");
        $stmt->bind_param("i", $employee_id);
        
        if ($stmt->execute()) {
            // Redirect to avoid re-execution on refresh
            header("Location: admin_empReg.php");
            exit();
        } else {
            echo "Error deleting record: " . $conn->error;
        }
        $stmt->close();
    } else {
        echo "Invalid ID for deletion.";
    }
}

// Handle fetching employee data by ID (for AJAX)
if (isset($_GET['id'])) {
    $employee_id = $_GET['id'];

    // Ensure ID is valid and sanitized
    if (is_numeric($employee_id)) {
        $stmt = $conn->prepare("SELECT * FROM employee WHERE employee_id = ?");
        $stmt->bind_param("i", $employee_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $employee = $result->fetch_assoc();

        // Return employee data as JSON
        echo json_encode($employee);
    } else {
        echo json_encode([]);
    }
    exit();
}

// Handle fetching of employee records for display
$employees = $conn->query("SELECT * FROM employee") or die($conn->error);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employee_id = isset($_POST['employee_id']) ? $_POST['employee_id'] : null;

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $contact_number = trim($_POST['contact_number']);
    $address = trim($_POST['address']);
    $birthday = $_POST['birthday'];
    $password = trim($_POST['password']);

    $errorMessage = '';

    // Server-side validation
    if (empty($username) || empty($email) || empty($contact_number) || empty($address) || empty($birthday)) {
        $errorMessage = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = "Invalid email format.";
    } elseif (!preg_match("/^[0-9]{10}$/", $contact_number)) {
        $errorMessage = "Contact number must be 10 digits.";
    }

    if (!empty($errorMessage)) {
        echo "<div style='color: red;'>$errorMessage</div>";
    } else {
        if (empty($employee_id)) {
    // Insert new employee
    if (strlen($password) < 2) {
        $errorMessage = "Password must be at least 2 characters long.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $position = $_POST['position']; // Get the position from the form

        $sql = "INSERT INTO employee (username, email, contact_number, address, birthday, password, position) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            die("Error preparing statement: " . $conn->error);
        }
        $stmt->bind_param("sssssss", $username, $email, $contact_number, $address, $birthday, $hashedPassword, $position);
        
        if ($stmt->execute()) {
            header("Location: admin_empReg.php");
            exit();
        } else {
            echo "Error executing statement: " . $stmt->error;
        }
        $stmt->close();
    }
} else {
    // Update existing employee
    $position = $_POST['position']; // Get the position from the form
    
    $sql = "UPDATE employee SET username=?, email=?, contact_number=?, address=?, birthday=?, position=?";
    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql .= ", password=?";
    }
    $sql .= " WHERE employee_id=?";
    
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
    
    if (!empty($password)) {
        $stmt->bind_param("sssssssi", $username, $email, $contact_number, $address, $birthday, $position, $hashedPassword, $employee_id);
    } else {
        $stmt->bind_param("ssssssi", $username, $email, $contact_number, $address, $birthday, $position, $employee_id);
    }

    if ($stmt->execute()) {
        header("Location: admin_empReg.php");
        exit();
    } else {
        echo "Error executing statement: " . $stmt->error;
    }

    $stmt->close();
}

    }
}

// Query to check if any product has 20 or fewer items in stock
$low_stock_sql = "SELECT category, product_name, qty FROM product WHERE qty <= 20";
$low_stock_result = $conn->query($low_stock_sql);

// Check if there are products with low stock
if ($low_stock_result->num_rows > 0) {
    // Notify the admin of low stock products
    $low_stock_message = "Warning: The following products are low in stock (20 or less):<br>";
    
    while ($row = $low_stock_result->fetch_assoc()) {
        $low_stock_message .= "Category: " . htmlspecialchars($row['category']) . " - Product: " . htmlspecialchars($row['product_name']) . " (Stock: " . htmlspecialchars($row['qty']) . ")<br>";
    }


    
}

$conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Registration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../admin_css/admin_nav.css?v=1">
    <link rel="stylesheet" href="../admin_css/admin_empReg.css">
    <link rel="stylesheet" href="../admin_css/admin_footer.css">
    

     <style>
        .low-stock-notification {
            background-color: #ffcccb;
            color: #d8000c;
            padding: 15px;
            border: 1px solid #d8000c;
            margin: 20px;
            font-weight: bold;
        }
    </style>
    
    <script>
        function openPopup() {
            document.getElementById("popupForm").style.display = "flex";
        }

        function closePopup() {
            document.getElementById("popupForm").style.display = "none";
        }

        function openEditPopup(employeeId) {
            // Make an AJAX request to fetch employee data
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "admin_empReg.php?id=" + employeeId, true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    var employeeData = JSON.parse(xhr.responseText);

                    // Pre-fill the form with employee data
                    document.getElementById("username").value = employeeData.username;
                    document.getElementById("email").value = employeeData.email;
                    document.getElementById("contact_number").value = employeeData.contact_number;
                    document.getElementById("address").value = employeeData.address;
                    document.getElementById("birthday").value = employeeData.birthday;
                    document.getElementById("password").value = ""; // Do not show password
                    document.getElementById("position").value = employeeData.position;
                    // Update the hidden employee_id field with the employee's ID
                    document.getElementById("employee_id").value = employeeId;

                    // Show the popup form
                    openPopup();
                }
            };
            xhr.send();
        }

        function validateForm() {
            const email = document.getElementById("email").value;
            const contactNumber = document.getElementById("contact_number").value;

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const phoneRegex = /^\d{10}$/;

            if (!emailRegex.test(email)) {
                alert("Please enter a valid email address.");
                return false;
            }

            if (!phoneRegex.test(contactNumber)) {
                alert("Please enter a valid 10-digit contact number.");
                return false;
            }

            return true; // Allow the form submission if all validations pass
        }
    </script>
</head>
<body>
     <br/> <br/> <br/> <br/>
    <!-- Low stock notification right below the navigation bar -->
<?php if (!empty($low_stock_message)): ?>
    <div class="low-stock-notification">
        <?= $low_stock_message ?>
    </div>
<?php endif; ?>

    <div class="container">
       
        <h2>Employee Registration</h2>
        <div class="top-right">
            <button class="btn-new" onclick="openPopup()">New Employee</button>
        </div>
        
        <!-- Table to display employees -->
        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Contact Number</th>
                    <th>Address</th>
                    <th>Birthday</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $employees->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['username']; ?></td>
                        <td><?php echo $row['email']; ?></td>
                        <td><?php echo $row['contact_number']; ?></td>
                        <td><?php echo $row['address']; ?></td>
                        <td><?php echo $row['birthday']; ?></td>
                        <td class="actions">
                            <a href="#" class="btn-edit" onclick="openEditPopup('<?php echo $row['employee_id']; ?>')">Edit</a>
                            <a href="?delete=<?php echo $row['employee_id']; ?>" class="btn-delete" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Popup form for adding a new employee -->
    <div id="popupForm" class="popup-form" onclick="closePopup()">
        <div class="popup-content" onclick="event.stopPropagation()">
            <h3>Add New Employee</h3>
<form method="POST" action="" onsubmit="return validateForm()">
    <label for="username">Username</label>
    <input type="text" id="username" name="username" required>
    
    <label for="email">Email</label>
    <input type="email" id="email" name="email" required>
    
    <label for="contact_number">Contact Number</label>
    <input type="text" id="contact_number" name="contact_number" required>
    
    <label for="address">Address</label>
    <input type="text" id="address" name="address" required>
    
    <label for="birthday">Birthday</label>
    <input type="date" id="birthday" name="birthday" required>
    
    <label for="password">Password</label>
    <input type="password" id="password" name="password" required>

    <!-- Add Position Dropdown -->
    <label for="position">Position</label>
    <select id="position" name="position" required>
        <option value="Employee">Employee</option>
        <option value="Admin">Admin</option>
    </select>
    
    <input type="submit" value="Register">
    <input type="hidden" id="employee_id" name="employee_id">
</form>

        </div>
    </div>

<!-- Include the navigation bar -->
    <?php include '../admin_php/admin_nav.php'; ?>
<br><br><br><br><br><br><br><br>
<!-- Include the footer file here -->
<?php include '../admin_php/admin_footer.php'; ?>

</body>    
</html>
