<?php
session_start(); // Start the session

// Include the database configuration file
include 'db_connection.php';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Capture form data
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Protect against SQL injection
    $email = mysqli_real_escape_string($conn, $email);
    $password = mysqli_real_escape_string($conn, $password);

    // Query to check if the email exists and fetch the stored password and role
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        // Verify the password using password_hash() and password_verify()
        if (password_verify($password, $user['password'])) {
            // Update last_login and set is_active to true
            $update_sql = "UPDATE users SET last_login = NOW(), is_active = TRUE WHERE id = " . $user['id'];
            mysqli_query($conn, $update_sql);

            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['role'] = $user['role']; // Store the user's role in the session

            // Redirect to the dashboard or homepage based on the user's role
            if ($user['role'] == 'admin') {
                header("Location: dashboard.php"); // Admin login redirect
            } else {
                header("Location: homepage.php"); // Customer login redirect
            }
            exit();
        } else {
            // Incorrect password
            echo "<script>alert('Incorrect password. Please try again.');</script>";
        }
    } else {
        // Email not found
        echo "<script>alert('Email not found. Please register.');</script>";
    }
}
?>

<!DOCTYPE html> 
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MPSM Beverages</title>
    
    <style>
        /* Basic Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            justify-content: space-between;
            background-color: #f4f4f4;
        }

        header {
            background-color: #333;
            color: #fff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
        }

        header nav ul {
            list-style: none;
            display: flex;
        }

        header nav ul li {
            margin-left: 20px;
        }

        header nav ul li a {
            color: #fff;
            text-decoration: none;
        }

        .login-section {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .login-box {
            background-color: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 400px;
            text-align: center;
        }

        .login-box h2 {
            margin-bottom: 20px;
            font-size: 24px;
        }

        .input-box {
            margin-bottom: 15px;
            text-align: left;
        }

        .input-box label {
            display: block;
            margin-bottom: 5px;
        }

        .input-box input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        button {
            padding: 10px 20px;
            background-color: orange;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
        }

        button:hover {
            background-color: green;
        }

        footer {
            background-color: #fff;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #ddd;
            margin-top: 20px;
        }

        footer .footer-container {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
        }

        footer .footer-container div {
            width: 30%;
        }

        footer .footer-container .quick-links ul {
            list-style: none;
            padding: 0;
        }

        footer .footer-container .quick-links ul li {
            margin: 5px 0;
        }

        footer .footer-container .quick-links ul li a {
            text-decoration: none;
            color: #333;
        }

        footer .social-icons {
            margin-top: 15px;
            display: flex;
            justify-content: center;
            gap: 20px;
        }

        footer .social-icons img {
            width: 40px;
            height: 40px;
        }

        table {
            border-collapse: separate;
            border-spacing: 50px; /* Space between images */
        }

        td {
            padding: 10px; /* Padding inside each cell */
        }

        .center{
            margin-left: auto;
            margin-right: auto;
        }

    </style>
</head>
<body>

    <!-- Header section -->
    <header>
       <center><h2>MPSM Beverages</h2></center>
    </header>

<!-- Login section -->
<section class="login-section">
    <div class="login-box">
        <h2>Login</h2>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
            <div class="input-box">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="input-box">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">LOGIN</button>
        </form>
        <p>Don’t have an account? <a href="registrationpage.php">Register Here</a></p>
    </div>
</section>
    <!-- Footer section -->
    <footer>
        <class="footer-container" id="footer">
        
        <div><u><center><h3>Contact Us</h3></center></u></div><br><br>

            <table class="center" border-spacing="100px">
                <tr>
                    <td>
                        
                            <p><strong>Physical Address:</strong></p>
                            <p>Innovation Worx Unit b15, Cnr 16th Road, Scale End,<br>
                            Halfway House Estate, Johannesburg, 1688</p>
                        
                    </td>
                    <td>
                        
                            <p><strong>Open Hours:</strong><br>
                                Mon-Fri: 9am-5pm<br>
                                    Sat: 9am-3pm<br>
                                    Sun: 9am-1pm</p>
                        
                    </td>
                    <td>
                        
                            <p><strong>Contact Information:</strong></p>
                            <p>Phone: 064 944 4905</p>
                        
                    </td>
                    <td>
                        
                            <p><strong>Quick Links:</strong></p>
                            <ul>
                                <li><a href="homepage.php">Home</a></li>
                                <li><a href="homepage.php#products">Shop Now</a></li>
                                <li><a href="#">Help</a></li>
                                <li><a href="cartpage.php">Cart</a></li>
                                <li><a href="profile.html">Profile</a></li>
                            </ul>
                        
                    </td>
                </tr>
            </table>
        <div class="social-icons">
            <a href="https://www.instagram.com/mpsm_water?igsh=MXcwcGliNXhtNG5uMA=="><img src="instagram.avif" alt="Instagram"></a>
            <a href="https://wa.me/27649444905"><img src="whatsapp.avif" alt="WhatsApp"></a>
            <a href=https://www.facebook.com/profile.php?id=100075529239031&mibextid=LQQJ4d><img src="facebook.webp" alt="Facebook"></a>
        </div>
        <p>&copy; 2024 MPSM Beverages. All Rights Reserved.</p>
    </footer>

</body>
</html>