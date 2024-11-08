<?php
session_start();
include('config.php');

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['user_id']);

// Check if user is logged in and reviewID is provided
if (!isset($_SESSION['user_id']) || !isset($_GET['reviewID'])) {
    echo "<script>Swal.fire('Access Denied', 'Unauthorized access.', 'error');</script>";
    exit();
}

$userID = $_SESSION['user_id'];
$reviewID = intval($_GET['reviewID']);

// Fetch the existing review from the database
$stmt = $conn->prepare("SELECT rating, reviewText, itemID FROM reviews WHERE reviewID = ? AND user_id = ?");
$stmt->bind_param("ii", $reviewID, $userID);
$stmt->execute();
$result = $stmt->get_result();
$review = $result->fetch_assoc();
$stmt->close();

if (!$review) {
    echo "<script>Swal.fire('Not Found', 'Review not found or you do not have permission to edit it.', 'error');</script>";
    exit();
}

$itemID = $review['itemID']; // Fetch itemID from the review record

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = intval($_POST['rating']);
    $reviewText = trim($_POST['reviewText']);

    // Update the review in the database
    $stmt = $conn->prepare("UPDATE reviews SET rating = ?, reviewText = ? WHERE reviewID = ? AND user_id = ?");
    $stmt->bind_param("isii", $rating, $reviewText, $reviewID, $userID);

    if ($stmt->execute()) {
        header("Location: review.php?products_ItemID=" . $itemID . "&success=1");
        exit();
    } else {
        header("Location: edit_review.php?reviewID=$reviewID&error=execution_failed");
        exit();
    }
    $stmt->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Review</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            color: #333;
            margin: 0;
            padding-top: 0px; /* Prevent content from being hidden behind fixed header */
        }
        
        /*----------------- Header styling ---------------*/
        header {
            position: sticky;
            top: 0;
            left: 0;
            width: 100vw;
            background-color: #fff;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #ddd;
        }

        /* Logo styling */
        header .logo img {
            width: 200px;
            height: auto; /* Maintain aspect ratio */
        }

        /* Navigation styling for larger screens */
        header nav.nav-menu {
            flex-grow: 1;
            display: flex;
            justify-content: center; /* Center navigation */
        }

        header nav.nav-menu ul {
            list-style: none;
            display: flex;
            gap: 25px;
            margin: 0;
            padding: 0;

        }

        header nav.nav-menu ul li a {
            text-decoration: none; /* Remove underline for nav links */
            color: #333;
            font-size: 20px;
            padding: 8px 12px;
            transition: color 0.3s ease;
            
        }

        header nav.nav-menu ul li a:hover {
            color: #fbb01b; /* Change link color on hover */
        }

        /* Icons container */
        header .icons {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #333b;
            margin-right: 20px;
        }

        header .icons i {
            font-size: 24px;
            cursor: pointer;
            transition: color 0.3s ease;
            color: black;
            
        }

        header .icons i:hover {
            color: orange; /* Change icon color on hover */
        }

        .hover-effect:hover {
            transform: scale(1.1); /* Scale the image slightly */
            opacity: 0.9; /* Slightly reduce opacity on hover */
        }

        /* Auth links */
        .auth-links {
            display: flex;
            gap: 15px;
            align-items: center;
            margin-right: 15px; /* Adjusted margin */
            justify-content: center;
        }

        .auth-links ul {
            list-style: none;
            padding: 0;
            display: flex;
            margin: 0;
        }

        .auth-links a {
            text-decoration: none; /* Ensure no underline */
            color: inherit;
            padding: 5px 10px;
            font-size: 16px;
            transition: color 0.3s ease;
        }

        .auth-links a:hover {
            color: #fbb01b; /* Change color on hover */
            text-decoration: underline;
        }

        /* Media Queries for Responsiveness */
        @media (max-width: 768px) {
            header {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
                padding: 10px;
            }

            /* Hide nav-menu on smaller screens */
            header nav.nav-menu {
                display: none;
            }

            /* Show menu icon on smaller screens */
            .menu-icon {
                display: flex;
                cursor: pointer;
                font-size: 24px;
            }

            .search-container {
                flex-grow: 1;
                display: flex;
                align-items: center;
                justify-content: flex-start;
                margin-right: 5px;
            }

            #search-input {
                width: 100%; /* Full width on smaller screens */
            }

            header .logo img {
                width: 120px; /* Smaller logo on smaller screens */
            }

        /* Overlay Menu Styles for smaller screens */
        .overlay-menu {
            display: none; /* Hidden by default */
            position: fixed;
            top: 0;
            right: 0;
            width: 40%; /* Reduced overlay width */
            height: 100%;
            background-color: white; /* Background is white */
        /* background-color: rgba(255, 255, 255, 0.8); /* Semi-transparent white background */

            color: black; /* Font color is black */
            z-index: 1000;
            padding-top: 60px;
            padding-left: 20px; /* Add some padding on the left */
            transition: opacity 0.3s ease, visibility 0.3s ease; /* Smooth transition for opacity and visibility */
            opacity: 0; /* Start hidden */
            visibility: hidden; /* Avoid clicks when hidden */

        }

        /* Additional styling for links, if needed */
        .overlay-menu a {
            color: black; /* Change link color to black */
            text-decoration: none; /* Remove underline from links */
            transition: color 0.3s ease; /* Smooth color transition */
            margin-top: 50px;
        }

        .overlay-menu a:hover {
            color: #fbb01b; /* Change link color on hover */
        }

        .overlay-menu.active {
            display: block; /* Show when active */
            opacity: 1; /* Fully opaque when active */
            visibility: visible; /* Allow clicks */
        }

        .overlay-menu ul {
            list-style-type: none;
            padding: 0;
        }

        .overlay-menu ul li {
            padding: 15px 20px;
            font-size: 20px;
            text-align: center;
        }
        /* Opptional: Add a transition for smoother appearance */
        .overlay-menu {
            opacity: 0; /* Start hidden */
            visibility: hidden; /* Avoid clicks when hidden */
        }

        .overlay-menu.active {
            opacity: 1; /* Fade in effect */
            visibility: visible; /* Allow clicks */
        }


            .close-icon {
                position: absolute;
                top: 20px;
                right: 20px;
                font-size: 30px;
                cursor: pointer;
            }
        }

        @media (min-width: 769px) {
            /* Hide overlay menu on larger screens */
            .overlay-menu {
                display: none;
            }

            /* Show horizontal nav-menu on larger screens */
            header nav.nav-menu {
                display: flex;
            }

            .menu-icon {
                display: none; /* Hide menu icon on larger screens */
            }
        }

        .review-edit-container {
    
            margin-left: 35%;
            background-color: #fff;
            padding: 20px;
            width: 500px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            
            
        }
        .review-edit-container h2 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #444;
        }
        label {
            font-weight: bold;
            display: block;
            margin-top: 10px;
        }
        select, textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        textarea {
            resize: none;
        }
        button {
            background-color: orange;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            margin-top: 15px;
            font-size: 16px;
        }
        button:hover {
            background-color: #218838;
        }
        .cancel-button {
            background-color: orange;
            color: white;
        }
        .cancel-button:hover {
            background-color: #c82333;
        }
        .stars {
            display: inline-flex;
            cursor: pointer;
        }
        .star {
            font-size: 24px;
            color: gray;
        }
        .star.selected {
            color: gold;
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

    </style>
</head>
<main>
<body>
    <div>
    <header>
        <div class="logo">
            <img src="logo.jpg" alt="Logo">
        </div>

        <nav class="nav-menu" aria-label="Main Navigation">
            <ul>
                <li><a href="homepage.php">Home</a></li>
                <li><a href="homepage.php#products">Shop Now</a></li>
                <li><a href="orders.php">Orders</a></li>
                <li><a href="#footer">Contact Us</a></li>
            </ul>
        </nav>
    
        <div class="icons"> 
            <?php if ($isLoggedIn): ?>
                <div class="cart-icon">
                    <a href="cartpage.php"><i class="fa-solid fa-cart-shopping"></i></a>
                </div>
                <div class="profile-icon">
                    <a href="profile.php"><i class="fa-solid fa-user"></i></a>
                </div>
                <div class="logout-icon">
                    <a href="homepage.php?logout=true"><i class="fa-solid fa-right-from-bracket"></i> </a>
                </div>
            <?php else: ?>
                <div class="auth-links">
                    <ul>
                        <li><a href="loginpage.php"><i class="fa-solid fa-right-to-bracket"></i> Login</a></li>
                        <li><a href="registrationpage.php"><i class="fa-solid fa-user-plus"></i> Register</a></li>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <br><br>

    <div class="review-edit-container">
        <h2>Edit Your Review</h2>
        <form action="edit_review.php?reviewID=<?= htmlspecialchars($reviewID); ?>" method="POST">
            <label for="rating">Rating (1-5):</label><br>
            <div class="stars">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <span class="star <?= $i <= $review['rating'] ? 'selected' : '' ?>" onclick="selectStarRating(<?= $i ?>)">★</span>
                <?php endfor; ?>
            </div>
            <input type="hidden" name="rating" id="rating" value="<?= $review['rating']; ?>"><br><br>

            <label for="reviewText">Your Review:</label><br>
            <textarea name="reviewText" id="reviewText" rows="4" required><?= htmlspecialchars($review['reviewText']); ?></textarea><br><br>

            <input type="hidden" name="ItemID" value="<?= isset($_GET['ItemID']) ? htmlspecialchars($_GET['ItemID']) : ''; ?>">
            <!-- <button type="submit">Save Changes</button> -->
             <div class="button-group">
                <button type="submit" class="save-button">Save Changes</button>
                <button type="button" class="cancel-button" onclick="window.location.href='review.php?products_ItemID=<?= $juice['ItemID']; ?>'">Cancel</button>
            </div>
        </form>
    </div>

    <script>
        // JavaScript to handle star rating selection
        function selectStarRating(rating) {
            document.getElementById('rating').value = rating;
            const stars = document.querySelectorAll('.star');
            stars.forEach((star, index) => {
                if (index < rating) {
                    star.classList.add('selected');
                } else {
                    star.classList.remove('selected');
                }
            });
        }

        function getQueryParameter(name) {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(name);
        }

        document.addEventListener('DOMContentLoaded', function () {
        const success = getQueryParameter('success');
        const error = getQueryParameter('error');

        if (success) {
            Swal.fire({
                icon: 'success',
                title: 'Review Submitted!',
                text: 'Your review has been successfully edited.',
                confirmButtonText: 'Okay'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "review.php?ItemID=<?= isset($_GET['ItemID']) ? htmlspecialchars($_GET['ItemID']) : '' ?>&success=1";
                }
            });
        } else if (error) {
            let errorMessage;
            switch (error) {
                case 'invalid_rating':
                    errorMessage = 'Invalid rating. Please provide a rating between 1 and 5.';
                    break;
                case 'product_not_found':
                    errorMessage = 'Error: The specified product does not exist.';
                    break;
                case 'execution_failed':
                    errorMessage = 'Error saving review. Please try again.';
                    break;
                case 'prepare_failed':
                    errorMessage = 'Error preparing the submission. Please contact support.';
                    break;
                default:
                    errorMessage = 'An unknown error occurred.';
            }

            Swal.fire({
                icon: 'error',
                title: 'Submission Failed',
                text: errorMessage,
                confirmButtonText: 'Okay'
            });
        }
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
                                <li><a href="">Help</a></li>
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

    <!-- Lightbox for image popup -->
    <div id="lightbox" onclick="closeLightbox()">
    <img id="lightbox-img" src="">
    <a href="javascript:void(0)" class="close" onclick="closeLightbox()">×</a>
    </div>


</body>
</main>   
</html>
