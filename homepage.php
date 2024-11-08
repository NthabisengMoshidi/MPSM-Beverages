<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Logout logic
if (isset($_GET['logout']) && $_GET['logout'] === 'true') {
    session_unset();
    session_destroy();
    header('Location: homepage.php');
    exit();
}

// Database Connection
$host = getenv('DB_HOST') ?: 'localhost';  
$db   = getenv('DB_NAME') ?: 'mpsm_ecommerce';  
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: 'P@ssword'; 
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage(), 3, 'error_log.txt');
    echo "We're experiencing technical difficulties. Please try again later.";
    exit;
}

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['user_id']);

// Handle Add to Cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
    if ($isLoggedIn) {
        $user_id = $_SESSION['user_id'];
        $product_id = $_POST['product_id'];
        $quantity = $_POST['quantity'];

        // Step 1: Check or create an active cart for the user
        $stmt = $pdo->prepare("SELECT cart_id FROM cart WHERE user_id = ? AND status = 'active'");
        $stmt->execute([$user_id]);
        $cart = $stmt->fetch();

        if (!$cart) {
            // Create a new cart if one doesn't exist
            $stmt = $pdo->prepare("INSERT INTO cart (user_id, status) VALUES (?, 'active')");
            $stmt->execute([$user_id]);
            $cart_id = $pdo->lastInsertId();
        } else {
            $cart_id = $cart['cart_id'];
        }

        // Step 2: Insert or update the product in the cart_items table
        $stmt = $pdo->prepare("INSERT INTO cart_items (cart_id, product_id, quantity, price) 
                               VALUES (?, ?, ?, (SELECT Price FROM products WHERE ItemID = ?))
                               ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)");
        $stmt->execute([$cart_id, $product_id, $quantity, $product_id]);

        echo json_encode(['success' => true]);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'User not logged in.']);
        exit;
    }
}

// Juices Section
try {
    $query = "SELECT * FROM products WHERE Category = 'Juices'";
    $statement = $pdo->prepare($query);

    if ($statement->execute()) {
        $juices = $statement->fetchAll(PDO::FETCH_ASSOC);  
    } else {
        $juices = [];  
        echo "Query failed to execute.";
    }
} catch (PDOException $e) {
    die("Error retrieving data: " . $e->getMessage());
}

// Water Section
try {
    $query = "SELECT * FROM products WHERE Category = 'Water'";
    $statement = $pdo->prepare($query);

    if ($statement->execute()) {
        $water = $statement->fetchAll(PDO::FETCH_ASSOC);  
    } else {
        $water = [];  
        echo "Query failed to execute.";
    }
} catch (PDOException $e) {
    die("Error retrieving data: " . $e->getMessage());
}
//Ice Section
try {
    $query = "SELECT * FROM products WHERE Category = 'Ice'";
    $statement = $pdo->prepare($query);

    if ($statement->execute()) {
        $waters = $statement->fetchAll(PDO::FETCH_ASSOC);  
    } else {
        $waters = [];  
        echo "Query failed to execute.";
    }
} catch (PDOException $e) {
    die("Error retrieving data: " . $e->getMessage());
}
//Equipment section
try {
    $query = "SELECT * FROM products WHERE Category = 'Distillation Equipment'";
    $statement = $pdo->prepare($query);

    if ($statement->execute()) {
        $distillation_equipment = $statement->fetchAll(PDO::FETCH_ASSOC);  
    } else {
        $distillation_equipment = [];  
        echo "Query failed to execute.";
    }
} catch (PDOException $e) {
    die("Error retrieving data: " . $e->getMessage());
}
//Refills section
try {
    $query = "SELECT * FROM products WHERE Category = 'Refills'";
    $statement = $pdo->prepare($query);

    if ($statement->execute()) {
        $refills = $statement->fetchAll(PDO::FETCH_ASSOC);  
    } else {
        $refills = [];  
        echo "Query failed to execute.";
    }
} catch (PDOException $e) {
    die("Error retrieving data: " . $e->getMessage());
}
//Customisation
try {
    $query = "SELECT * FROM products WHERE Category = 'Customised Beverages'";
    $statement = $pdo->prepare($query);

    if ($statement->execute()) {
        $customised_bottles = $statement->fetchAll(PDO::FETCH_ASSOC);  
    } else {
        $customised_bottles= [];  
        echo "Query failed to execute.";
    }
} catch (PDOException $e) {
    die("Error retrieving data: " . $e->getMessage());
} 



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MPSM Beverages</title>
    <link rel="icon" type="image/x-icon" href="images/logo.jpg">
   
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

body {
    background-color: #f9f9f9;
}

/*----------------- Header styling ---------------*/
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

/*---------Hover effect for the navigation----------------------------*/
.hover-effect {
    transition: transform 0.3s ease, opacity 0.3s ease; /* Smooth transition */
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

/* Search bar styling */
.search-container {
    display: flex;
    align-items: center;
    gap: 5px;
    flex-grow: 1; /* Allow search to take up space */
    margin-right: 10px; /* Reduced spacing */
}

#search-input {
    padding: 5px;
    width: 180px;
}

#search-btn {
    background: none;
    border: none;
    cursor: pointer;
    padding: 5px;
}

#search-btn i {
    font-size: 18px;
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
/*-------------------------------------------------Welcome message --------------------------------------*/
/* Notification Banner Styling */
.notification-banner {
    display: flex;
    align-items: center;
    justify-content: flex-end; /* Align icon and text to the right */
    padding: 15px 20px;
    background-color: #f8f9fa;
    border-bottom: 1px solid #e0e0e0;
    border-top: 1px solid #e0e0e0;
    color: #333;
    font-size: 1rem;
    font-family: Arial, sans-serif;
    box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1);
}

/* Specific Text Styles */
.welcome-message, .notification-message {
    font-weight: bold;
    margin: 0px;
}

/* Icon Styling */
.notification-banner i {
    color: orange; /* Orange color for visibility */
    font-size: 1.2rem;
    margin-left: 10px; /* Space between icon and text */
}
.hero-banner {
        width: 100%;
        height: 300px;
        background-color: #e1e1e1;
        margin-top: 20px;
        display: flex;
        justify-content: center;
        align-items: center;
        position: relative;
    }

/*-------------------------------------------------Products Form --------------------------------------*/

.shop-by-category {
    text-align: center;
    margin: 20px 0;
    background-color: darkorange;
    padding: 15px; /* Added padding for better spacing */
    border-radius: 10px; /* Rounded corners */
    transition: background-color 0.3s ease; /* Smooth background transition */
}

.shop-by-category:hover {
    background-color: #ff8c00; /* Darker shade on hover */
}

.categories {
    display: flex;
    justify-content: center;
    gap: 30px;
    flex-wrap: wrap; /* Allows categories to wrap on smaller screens */
}

.categories div {
    width: 100px;
    height: 100px;  
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: #ddd; /* Background color for visibility */
    transition: transform 0.3s ease; /* Smooth scaling effect */
}

.categories div:hover {
    transform: scale(1.1); /* Scale effect on hover */
}

        .products-section {
            width: 90%;
            margin: 0 auto;
        }
        .products-section h2 {
            margin-top: 20px;
        }
        .product-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 40px;
            justify-content: flex-start;
        }
        .product-item {
            background-color: #fff;
            width: 250px;
            padding: 15px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 10px;
        }
        .product-item img {
            width: 100%;
            height: 100;
            background-color: #ccc;
             /*transition: transform 0.3s ease;  Smooth transition for scaling */

        }

        .product-item p {
            margin: 10px 0;
        }
        .product-item select,
        .product-item button {
            width: 100%;
            padding: 5px;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .product-bottom {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .product-quantity {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .quantity-control {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .quantity-control button {
        width: 25px;
        height: 25px;
        background-color: #ccc;
        border: none;
        cursor: pointer;
    }

    .quantity-control input {
        width: 50px;
        text-align: center;
    }

    .add-to-cart-btn {
        background-color: #fbb01b;
        color: white;
        border: none;
        padding: 5px 10px;
        cursor: pointer;
    }

    .product-review a {
        color:#fbb01b;
        /*text-decoration: underline;*/
        cursor: pointer;
    }
        button {
            padding: 10px 20px;
            background-color: #fbb01b;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #ecb74d;
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
        }   .search-container

        table {
            border-collapse: separate;
            border-spacing: 20px; 
        }
        td {
            padding: 10px;
        }
        table img {
            width: 150px;
            height: auto;
            border-radius: 50%;
        }
        .center{
            margin-left: auto;
            margin-right: auto;
        }

        /* Style for the search container */
.search-icon {
    position: relative;
    margin-left: -200px;
}

/* The search icon button */
#search-icon-btn {
    background: none;
    border: none;
    cursor: pointer;
   /* margin-left: -100px;*/

}

/* Hidden search popup by default */
.search-popup {
    display: none;
    position: absolute;
    top: 40px; 
    left: 0;
    background-color: white;
    padding: 10px;
    border: 1px solid #ccc;
    z-index: 1000;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

/* Input inside the popup */
#search-input {
    padding: 5px;
    width: 200px;
}

/* Transition effect (optional) */
.search-popup.show {
    display: block;
    animation: fadeIn 0.3s ease-in-out;
}

/* Optional fade-in effect */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
/* Product grid layout */
.product-grid {
    display: flex;
    flex-wrap: wrap;
}

.product-item {
    width: 200px;
    margin: 10px;
    border: 1px solid #ccc;
    padding: 10px;
    text-align: center;
}
/*Search icon*/
        /* Lightbox styles */
#lightbox {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.8);
    display: none;
    justify-content: center;
    align-items: center;
}

#lightbox img {
    max-width: 90%;
    max-height: 90%;
}

#lightbox:target {
    display: flex;
}

#lightbox .close {
    position: absolute;
    top: 20px;
    right: 40px;
    font-size: 40px;
    color: white;
    text-decoration: none;
    cursor: pointer;
}

    </style>
</head>


<body>

<!-- Header -->
<header>
    <div class="logo">
        <img src="juice-images/logo.png" alt="Logo">
    </div>

    <nav class="nav-menu" aria-label="Main Navigation">
        <ul>
            <li><a href="homepage.php">Home</a></li>
            <li><a href="homepage.php#products">Shop Now</a></li>
            <li><a href="orders.php">Orders</a></li>
            <li><a href="#footer">Contact Us</a></li>
        </ul>
    </nav>

    <div class="search-container" aria-label="Search Products"> 
        <input type="text" id="search-input" placeholder="Search for products..." aria-label="Search" oninput="filterProducts()">
    </div>
   
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

<div class="menu-icon" aria-label="Menu" role="button" tabindex="0">
    <i class="fa-solid fa-bars"></i>
</div>


    
</header>

<!-- Include Font Awesome in your head section -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<nav class="overlay-menu nav-menu" aria-label="Main Navigation"> 
    <span class="close-icon" aria-label="Close Menu">&times;</span>
    <ul>
        <li><a href="homepage.php"><i class="fas fa-home"></i> Home</a></li>
        <li><a href="homepage.php#products"><i class="fas fa-shopping-cart"></i> Shop Now</a></li>
        <li><a href="orders.php"><i class="fas fa-box"></i> Orders</a></li>
        <li><a href="#footer"><i class="fas fa-envelope"></i> Contact Us</a></li>
        <li><a href="profile.php"><i class="fa-solid fa-user"></i> Profile</a></li>
        <li><a href="loginpage.php"><i class="fa-solid fa-right-to-bracket"></i> Login</a></li>

    </ul>
</nav>

<!-- Display Notification or Welcome Message -->
<div class="notification-banner <?php echo $isLoggedIn ? 'logged-in' : 'guest'; ?>">
    <?php
    if ($isLoggedIn) {
        // Fetch the user's first name and last name
        $user_id = $_SESSION['user_id'];
        $stmt = $pdo->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        // Display welcome message if user data is found
        if ($user) {
            $first_name = $user['first_name'];
            $last_name = $user['last_name'];
            echo "<div class='welcome-message'><i class='fas fa-smile'></i> Welcome, $first_name $last_name!</div>";
        }
    } else {
        echo "<div class='notification-message'><i class='fas fa-bullhorn'></i> Please log in or register for a personalized experience.</div>";
    }
    ?>
</div>


    <!-- Hero Banner -->
    <div class="hero-banner"></div>

    <!-- Shop by Category -->
    <div class="shop-by-category" id="shopnow">
        <h2 style="font-family: cursive">Shop by Category</h2>
    </div>
    
    <div>
    <table class="center"> 
    <tr>
        <td>
            <a href="#juice">
                <img src="juice-images/juice.jpg" alt="Juices" class="hover-effect">
                <p style="font-family:cursive; text-align:center;">Juices</p>
            </a>
        </td>
        <td>
            <a href="#water">
                <img src="juice-images/Water Logo.jpg" alt="Water" class="hover-effect">
                <p style="font-family:cursive; text-align:center;">Water</p>
            </a>
        </td>
        <td>
            <a href="#ice">
                <img src="juice-images/Ice.jpg" alt="Ice" class="hover-effect">
                <p style="font-family:cursive; text-align:center;">Ice</p>
            </a>
        </td>
        <td>
            <a href="#distillationequipment">
                <img src="juice-images/distillation.jpg" alt="Equipment" class="hover-effect">
                <p style="font-family:cursive; text-align:center;">Equipment</p>
            </a>
        </td>
    </tr>
</table>

</div>


        <div class="container">
    <div class="products-section" id="search-popup">
        <h2 id="products"><center>Products</center></h2><br><br>
        <!-------------------Search Results------------------>
        <div id="search-results"></div>

<!-- Juices Section --> 
<h2 id="juice">Juices</h2>
<div class="product-grid">
    <?php if (!empty($juices)): ?>
        <?php foreach ($juices as $juice): ?>
            <div class="product-item" data-name="<?= strtolower(str_replace(' ', '-', $juice['productName'])); ?>" data-price="<?= $juice['Price']; ?>">
                <img src="<?= $juice['imageURL']; ?>" alt="<?= $juice['productName']; ?>">
                <div class="product-name"><?= $juice['productName']; ?> - <?= $juice['volume']; ?></div>
                <div class="product-price">R<?= number_format($juice['Price'], 2); ?></div>
                <div class="product-bottom">
                    <div class="product-cap-type">
                        <label for="cap-type-<?= strtolower($juice['SKU']); ?>">Type of cap</label>
                        <select id="cap-type-<?= strtolower($juice['SKU']); ?>">
                            <option><?= $juice['type_of_cap']; ?></option>
                        </select>
                    </div>
                    <div class="product-quantity">
                        <label for="qty-<?= strtolower($juice['SKU']); ?>">Quantity</label>
                        <div class="quantity-control">
                            <input type="number" id="qty-<?= strtolower($juice['SKU']); ?>" value="1" min="1" max="<?= $juice['stockLevel']; ?>">
                            <button class="increase-btn" onclick="increaseQuantity('qty-<?= strtolower($juice['SKU']); ?>')">+</button>
                            <button class="decrease-btn" onclick="decreaseQuantity('qty-<?= strtolower($juice['SKU']); ?>')">-</button>
                        </div>
                    </div>
                    <button class="add-to-cart-btn" 
                        onclick="handleAddToCart('<?= $juice['productName']; ?>', <?= $juice['Price']; ?>, 'qty-<?= strtolower($juice['SKU']); ?>', '<?= $juice['imageURL']; ?>')">
                        Add to Cart
                    </button>
                </div>
                <div class="product-rating">0.0 rating</div>
                <div class="product-review">
<div class="product-review">
    <a href="reviews.php?products_ItemID=<?= $juice['ItemID']; ?>">Reviews</a>
</div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No juices available at the moment.</p>
    <?php endif; ?>
</div>


<!-- Water Section -->
<h2 id="water">Water</h2>
<div class="product-grid">
    <?php if (!empty($water)): ?>
        <?php foreach ($water as $water): ?>
            <div class="product-item" data-name="<?= strtolower(str_replace(' ', '-', $water['productName'])); ?>">
                <img src="<?= $water['imageURL']; ?>" alt="<?= $water['productName']; ?>">
                <div class="product-name"><?= $water['productName']; ?> - <?= $water['volume']; ?></div>
                <div class="product-price">R<?= number_format($water['Price'], 2); ?></div>
                <div class="product-bottom">
                    <div class="product-cap-type">
                        <label for="cap-type-<?= strtolower($water['SKU']); ?>">Type of cap</label>
                        <select id="cap-type-<?= strtolower($water['SKU']); ?>">
                            <option><?= $water['type_of_cap']; ?></option>
                        </select>
                    </div>
                    <div class="product-quantity">
                        <label for="qty-<?= strtolower($water['SKU']); ?>">Quantity</label>
                        <div class="quantity-control">
                            <input type="number" id="qty-<?= strtolower($water['SKU']); ?>" value="1" min="1" max="<?= $water['stockLevel']; ?>">
                            <button class="increase-btn" onclick="increaseQuantity('qty-<?= strtolower($water['SKU']); ?>')">+</button>
                            <button class="decrease-btn" onclick="decreaseQuantity('qty-<?= strtolower($water['SKU']); ?>')">-</button>
                        </div>
                    </div>
                    <button class="add-to-cart-btn" 
                        onclick="handleAddToCart('<?= $water['productName']; ?>', <?= $water['Price']; ?>, 'qty-<?= strtolower($water['SKU']); ?>', '<?= $water['imageURL']; ?>')">
                        Add to Cart
                    </button>
                </div>
                <div class="product-rating">0.0 rating</div>
                <div class="product-review">
                    <a href="review.php">Reviews</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No water products available at the moment.</p>
    <?php endif; ?>
</div>

<!-- Distillation Equipment Section -->
<h2 id="distillation-equipment">Distillation Equipment</h2>
<div class="product-grid">
    <?php if (!empty($distillation_equipment)): ?>
        <?php foreach ($distillation_equipment as $equipment): ?>
            <div class="product-item" data-name="<?= strtolower(str_replace(' ', '-', $equipment['productName'])); ?>">
                <img src="<?= $equipment['imageURL']; ?>" alt="<?= $equipment['productName']; ?>">
                <div class="product-name"><?= $equipment['productName']; ?> - <?= $equipment['volume']; ?></div>
                <div class="product-price">R<?= number_format($equipment['Price'], 2); ?></div>
                <div class="product-bottom">
                    <div class="product-quantity">
                        <label for="qty-<?= strtolower($equipment['SKU']); ?>">Quantity</label>
                        <div class="quantity-control">
                            <input type="number" id="qty-<?= strtolower($equipment['SKU']); ?>" value="1" min="1" max="<?= $equipment['stockLevel']; ?>">
                            <button class="increase-btn" onclick="increaseQuantity('qty-<?= strtolower($equipment['SKU']); ?>')">+</button>
                            <button class="decrease-btn" onclick="decreaseQuantity('qty-<?= strtolower($equipment['SKU']); ?>')">-</button>
                        </div>
                    </div>
                    <button class="add-to-cart-btn" 
                        onclick="handleAddToCart('<?= $equipment['productName']; ?>', <?= $equipment['Price']; ?>, 'qty-<?= strtolower($equipment['SKU']); ?>', '<?= $equipment['imageURL']; ?>')">
                        Add to Cart
                    </button>
                </div>
                <div class="product-rating">0.0 rating</div>
                <div class="product-review">
                    <a href="review.php">Reviews</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No distillation equipment available at the moment.</p>
    <?php endif; ?>
</div>



<!-- Refills Section -->
<h2 id="refills">Refills</h2>
<div class="product-grid">
    <?php if (!empty($refills)): ?>
        <?php foreach ($refills as $refill): ?>
            <div class="product-item" data-name="<?= strtolower(str_replace(' ', '-', $refill['productName'])); ?>">
                <img src="<?= $refill['imageURL']; ?>" alt="<?= $refill['productName']; ?>">
                <div class="product-name"><?= $refill['productName']; ?> - <?= $refill['volume']; ?></div>
                <div class="product-price">R<?= number_format($refill['Price'], 2); ?></div>
                <div class="product-bottom">
                   <p>Refill in Store, Not available online</p>
                </div>
                <div class="product-rating">0.0 rating</div>
                <div class="product-review">
                    <a href="review.php">Reviews</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No refill products available at the moment.</p>
    <?php endif; ?>
</div>

<center><div><a href="https://wa.me/27649444905"><button id="button">Customise</button></a></div></center>

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
<!-- Include SweetAlert CSS and JS for alerts -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {

    // ----------------------------------------
    // Quantity Management Functions
    // ----------------------------------------

    /**
     * Increase the quantity for a given input.
     * @param {string} inputId - The ID of the quantity input field.
     */
    function increaseQuantity(inputId) {
        const input = document.getElementById(inputId);
        let currentValue = parseInt(input.value);
        if (currentValue < 100) { // Limit to 100 max
            input.value = currentValue + 1;
        }
    }

    /**
     * Decrease the quantity for a given input.
     * @param {string} inputId - The ID of the quantity input field.
     */
    function decreaseQuantity(inputId) {
        const input = document.getElementById(inputId);
        let currentValue = parseInt(input.value);
        if (currentValue > 1) { // Minimum quantity is 1
            input.value = currentValue - 1;
        }
    }

    // ----------------------------------------
    // Lightbox Functionality
    // ----------------------------------------

    /**
     * Open the lightbox to display a larger version of an image.
     * @param {HTMLImageElement} image - The image element clicked to open the lightbox.
     */
    function openLightbox(image) {
        const lightbox = document.getElementById('lightbox');
        const lightboxImg = document.getElementById('lightbox-img');
        lightboxImg.src = image.src;
        lightbox.style.display = 'flex';
    }

    /**
     * Close the lightbox view.
     */
    function closeLightbox() {
        const lightbox = document.getElementById('lightbox');
        lightbox.style.display = 'none';
    }

    // ----------------------------------------
    // Add to Cart Functionality
    // ----------------------------------------

    /**
     * Adds a product to the cart via AJAX request.
     * @param {string} productName - Name of the product.
     * @param {number} price - Price of the product.
     * @param {string} quantityId - The ID of the quantity input field.
     * @param {string} imageURL - Image URL of the product.
     */
    function addToCart(productName, price, quantityId, imageURL) {
        const quantity = document.getElementById(quantityId).value;

        // Construct form data for the POST request
        const formData = new FormData();
        formData.append('productName', productName);
        formData.append('price', price);
        formData.append('quantity', quantity);
        formData.append('imageURL', imageURL);

        // Send POST request to server to add product to cart
        fetch('cart.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success alert using SweetAlert
                swal({
                    title: "Success!",
                    text: "Item added to cart successfully!",
                    icon: "success",
                    button: "Continue Shopping"
                });
            } else if (data.message === 'User not logged in.') {
                // Prompt user to log in if not authenticated
                promptLogin();
            } else {
                // Show error alert if something goes wrong
                swal("Oops!", "Something went wrong. Please try again.", "error");
            }
        })
        .catch(error => {
            console.error('Error:', error);
            swal("Oops!", "An error occurred. Please try again.", "error");
        });
    }

    // ----------------------------------------
    // Helper Functions
    // ----------------------------------------

    /**
     * Displays a SweetAlert to prompt user to log in or register.
     */
    function promptLogin() {
        swal({
            title: "Please Log In!",
            text: "You need to log in or register first!",
            icon: "info",
            buttons: {
                cancel: "Cancel",
                confirm: {
                    text: "Log In/Register",
                    value: true,
                },
            },
        }).then((willLogin) => {
            if (willLogin) {
                window.location.href = 'login.php'; // Redirect to login page
            }
        });
    }

    // ----------------------------------------
    // Attach Event Listeners to "Add to Cart" Buttons
    // ----------------------------------------

    /**
     * Attaches click event listeners to each "Add to Cart" button to handle adding items to the cart.
     */
    document.querySelectorAll('.add-to-cart-btn').forEach(button => {
        button.addEventListener('click', function() {
            const productName = this.getAttribute('data-product-name');
            const price = this.getAttribute('data-price');
            const quantityId = `qty-${this.getAttribute('data-product-id')}`;
            const imageURL = this.getAttribute('data-image-url');

            // If logged in, add item to cart; otherwise, prompt to log in
            if (<?= json_encode($isLoggedIn); ?>) {
                addToCart(productName, price, quantityId, imageURL);
            } else {
                promptLogin();
            }
        });
    });

    // ----------------------------------------
    // Overlay Menu for Mobile Navigation
    // ----------------------------------------

    const menuIcon = document.querySelector(".menu-icon");
    const overlayMenu = document.querySelector(".overlay-menu");
    const closeIcon = document.querySelector(".close-icon");

    // Toggle menu on mobile
    menuIcon.addEventListener("click", function() {
        overlayMenu.classList.toggle("active");
    });

    closeIcon.addEventListener("click", function() {
        overlayMenu.classList.remove("active");
    });

});
</script>














   
<!-- ---------------------------------Search Bar------------------------>
<script>
function filterProducts() {
    // Get the input value and convert it to lowercase for case-insensitive comparison
    const searchValue = document.getElementById('search-input').value.toLowerCase();
    // Get all product items
    const productItems = document.querySelectorAll('.product-item');

    // Loop through each product item
    productItems.forEach(item => {
        // Get the product name from the data-name attribute
        const productName = item.dataset.name;
        // Get the product price from the data-price attribute
        const productPrice = item.dataset.price.toString(); // Convert price to string for comparison

        // If the product name or price includes the search value, show it; otherwise, hide it
        if (productName.includes(searchValue) || productPrice.includes(searchValue)) {
            item.style.display = ''; // Show the item
        } else {
            item.style.display = 'none'; // Hide the item
        }
    });
}
</script>

</html>