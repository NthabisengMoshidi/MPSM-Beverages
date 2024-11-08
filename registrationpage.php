<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MPSM Beverages - Registration</title>
    <link rel="icon" type="image/x-icon" href="images/logo.jpg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /*styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        body {
            background-color: #f9f9f9;
        }
        header {
            position: sticky;
            top: 0;
            background-color: #fff;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #ddd;
        }
        header .logo img {
            width: 150px;
            height: 50px;
        
        }
        header nav ul {
            list-style: none;
            display: flex;
            gap: 20px;
        }
        header nav ul li {
            display: inline;
        }
        header nav ul li a {
            text-decoration: none;
            color: #333;
            font-size: 18px;
        }
        header .icons {
            display: flex;
            gap: 15px;
        }
        header .icons div {
            width: 24px;
            height: 24px;
            border-radius: 50%;
        }

        /*--------------Form---------------*/
        .form-container {
        width: 40%;
        margin: 50px auto;
        background-color: #f8f9fa;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
    }

    .form-container h1 {
        text-align: center;
        margin-bottom: 20px;
        font-size: 1.8rem;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: bold;
        color: #333;
    }

    .form-group input {
        width: 100%;
        padding: 10px;
        font-size: 16px;
        border: 1px solid #ccc;
        border-radius: 5px;
    }

    .button-group {
        display: flex;
        gap: 10px;
    }

    .nav-btn {
        background-color: #007bff;
        color: #fff;
        border: none;
        padding: 10px 15px;
        font-size: 16px;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s ease;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    #next-btn {
        float: right;
        animation: bounce 1s infinite;
    }

    .nav-btn:hover {
        background-color: #0056b3;
    }

    .animated-icon {
        transition: transform 0.3s ease;
    }

    .nav-btn:hover .animated-icon {
        transform: translateX(5px);
    }

    @keyframes bounce {
        0%, 20%, 50%, 80%, 100% {
            transform: translateY(0);
        }
        40% {
            transform: translateY(-5px);
        }
        60% {
            transform: translateY(-3px);
        }
    }

    .login-link {
        text-align: center;
        margin-top: 20px;
    }

    .login-link a {
        text-decoration: none;
        color: #007bff;
        transition: color 0.3s ease;
    }

    .login-link a:hover {
        color: #0056b3;
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

    <!-- Header -->
    <header>
       <center><h2>MPSM Beverages</h2></center>
    </header>
  
    <?php
ob_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include('db_connection.php');
    
    // Collect and sanitize form data for the users table
    $first_name = $conn->real_escape_string($_POST['first-name']);
    $last_name = $conn->real_escape_string($_POST['last-name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Collect address details
    $street_address = $conn->real_escape_string($_POST['street_address']);
    $suburb = $conn->real_escape_string($_POST['suburb_address']);
    $city = $conn->real_escape_string($_POST['city']);
    $province = $conn->real_escape_string($_POST['province']);
    $postal_code = $conn->real_escape_string($_POST['postal-code']);
    
    // Default values
    $role = 'customer';
    $is_active = 0; // Assuming inactive user by default
    $last_login = NULL; // Last login is NULL by default
    
    // Insert into the users table
    $user_sql = "INSERT INTO users (first_name, last_name, email, phone, password, street_address, suburb, city, province, postal_code, role, is_active, last_login)
                 VALUES ('$first_name', '$last_name', '$email', '$phone', '$password', '$street_address', '$suburb', '$city', '$province', '$postal_code', '$role', '$is_active', '$last_login')";
    
    if ($conn->query($user_sql) === TRUE) {
        // Get the last inserted user ID
        $user_id = $conn->insert_id;
        
        // SweetAlert success message for full registration with redirect to homepage
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Registration Successful!',
                    text: 'Welcome to the MPSM family!',
                    confirmButtonText: 'Go to Homepage'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'homepage.php';
                    }
                });
              </script>";
    } else {
        // SweetAlert error message
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Registration Failed',
                    text: 'Error: " . $conn->error . "',
                });
              </script>";
    }
    
    // Close the connection
    $conn->close();
}
ob_end_flush();
?>


<!-- Registration Form with JavaScript Validation and Pagination -->  
<div class="form-container">
    <h1>Registration</h1>
    <form id="registration-form" onsubmit="return validateForm()" method="post">
        <!-- Step 1: Personal Information -->
        <div class="form-step active">
            <div class="form-group">
                <label for="first-name">First Name:</label>
                <input type="text" id="first-name" name="first-name" required>
            </div>
            <div class="form-group">
                <label for="last-name">Last Name:</label>
                <input type="text" id="last-name" name="last-name" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
                <div id="email-error" style="color: red; font-size: 14px;"></div> <!-- Error message for email validation -->
            </div>
            <div class="form-group">
                <label for="phone">Phone Number:</label>
                <input type="tel" id="phone" name="phone" placeholder="+27" max="10" required>
                <div id="phone-error" style="color: red; font-size: 14px;"></div> <!-- Error message for phone validation -->
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
                <div id="password-error" style="color: red; font-size: 14px;"></div>
            </div>
            <div class="form-group">
                <label for="confirm-password">Confirm Password:</label>
                <input type="password" id="confirm-password" name="confirm-password" required>
                <div id="confirm-password-error" style="color: red; font-size: 14px;"></div>
            </div>
            <div class="form-group">
                <button type="button" id="next-btn" class="nav-btn">Next <i class="fa-solid fa-arrow-right"></i></button>
            </div>
        </div>

        <!-- Step 2: Address Information -->
        <div class="form-step">
            <div class="form-group">
                <label for="street_address">Street Address:</label>
                <input type="text" id="street_address" name="street_address" placeholder="Street Address" required>
            </div>
            <div class="form-group">
                <label for="suburb_address">Suburb Address:</label>
                <input type="text" id="suburb_address" name="suburb_address" placeholder="Suburb Address" required>
            </div>
            <div class="form-group">
                <label for="city">City:</label>
                <input type="text" id="city" name="city" required>
            </div>
            <div class="form-group">
                <label for="province">Province:</label>
                <input type="text" id="province" name="province" required>
            </div>
            <div class="form-group">
                <label for="postal-code">Postal Code:</label>
                <input type="text" id="postal-code" name="postal-code" required>
            </div>
            <div class="form-group">
                <label for="notes">Additional Notes:</label>
                <textarea id="notes" name="notes" placeholder="Additional Notes"></textarea>
            </div>

            <!-- Navigation buttons -->
            <div class="form-group button-group">
                <button type="button" id="prev-btn" class="nav-btn"><i class="fa-solid fa-arrow-left"></i> Previous</button>
                <button type="submit" id="submit-btn" class="nav-btn">Submit <i class="fa-solid fa-paper-plane"></i></button>
            </div>
        </div>

        <div class="login-link">
            Already have an account? <a href="loginpage.php">Login Here</a>
        </div>
    </form>
</div>

<!-- JavaScript for Form Validation and Pagination -->
<script>
   document.addEventListener("DOMContentLoaded", function() {
    const nextBtn = document.getElementById('next-btn');
    const submitBtn = document.getElementById('submit-btn');
    let showErrorMessages = false; // Flag to control error message visibility

    // Function to validate the form and enable/disable buttons
    function validateForm() {
        const password = document.getElementById("password").value;
        const confirmPassword = document.getElementById("confirm-password").value;
        const passwordError = document.getElementById("password-error");
        const confirmPasswordError = document.getElementById("confirm-password-error");
        const email = document.getElementById("email").value;
        const phone = document.getElementById("phone").value;
        const emailError = document.getElementById("email-error");
        const phoneError = document.getElementById("phone-error");

        const minLength = 8;
        const numberPattern = /[0-9]/;
        const specialCharPattern = /[!@#$%^&*(),.?":{}|<>]/;
        const emailPattern = /^[^\s@]+@[^\s@]+\.(com|co\.za|net)$/;
        const phonePattern = /^0\d{9}$/;

        let isValid = true;

        // Conditionally show or hide error messages based on the showErrorMessages flag
        passwordError.innerHTML = showErrorMessages && password.length < minLength 
            ? "Password must be at least 8 characters long." : "";

        passwordError.innerHTML += showErrorMessages && !numberPattern.test(password)
            ? "<br>Password must contain at least one number." : "";

        passwordError.innerHTML += showErrorMessages && !specialCharPattern.test(password)
            ? "<br>Password must contain at least one special character." : "";

        confirmPasswordError.innerHTML = showErrorMessages && password !== confirmPassword 
            ? "Passwords do not match." : "";

        emailError.innerHTML = showErrorMessages && !emailPattern.test(email)
            ? "Email must be a valid address ending with '.com', '.co.za', or '.net'." : "";

        phoneError.innerHTML = showErrorMessages && !phonePattern.test(phone)
            ? "Phone number must start with '0' and be exactly 10 digits long." : "";

        // Overall form validity status for enabling/disabling buttons
        isValid = passwordError.innerHTML === "" && confirmPasswordError.innerHTML === "" &&
                  emailError.innerHTML === "" && phoneError.innerHTML === "";

        nextBtn.disabled = !isValid;
        submitBtn.disabled = !isValid;

        return isValid;
    }

    // Listen to input changes for dynamic button state control
    const formInputs = document.querySelectorAll('#registration-form input, #registration-form select, #registration-form textarea');
    formInputs.forEach(input => {
        input.addEventListener('input', validateForm);
    });

    // Step management and display control
    const steps = document.querySelectorAll('.form-step');
    let currentStep = 0;

    function showStep(step) {
        steps.forEach((s, index) => {
            s.style.display = index === step ? 'block' : 'none';
        });
        document.getElementById('prev-btn').style.display = step === 0 ? 'none' : 'inline-block';
        nextBtn.style.display = step === steps.length - 1 ? 'none' : 'inline-block';
        submitBtn.style.display = step === steps.length - 1 ? 'inline-block' : 'none';
    }

    // Next button handler
    nextBtn.addEventListener('click', function() {
        showErrorMessages = true; // Set flag to show errors on button click
        if (validateForm()) {
            currentStep++;
            showStep(currentStep);
        }
    });

    // Previous button handler
    document.getElementById('prev-btn').addEventListener('click', function() {
        currentStep--;
        showStep(currentStep);
    });

    // Submit button handler
    submitBtn.addEventListener('click', function() {
        showErrorMessages = true; // Show errors on final submission
        if (validateForm()) {
            document.getElementById('registration-form').submit();
        }
    });

    // Initialize form display and button states
    showStep(currentStep);
    validateForm();
});
</script>

    <!-- Footer -->
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