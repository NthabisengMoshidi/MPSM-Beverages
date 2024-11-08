<?php 
session_start();

// Database connection
$host = 'localhost';  
$db   = 'mpsm_ecommerce';  
$user = 'root';
$pass = 'P@ssword'; 
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    // Remove item from cart if requested
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'remove_from_cart') {
        $product_id = $_POST['product_id'];
        $stmt = $pdo->prepare("DELETE FROM cart_items WHERE cart_id = (SELECT cart_id FROM cart WHERE user_id = ?) AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);

        // Recalculate cart items and subtotal after removal
        echo json_encode(['success' => true]);
        exit;
    }

    // Fetch cart items for the user
    $stmt = $pdo->prepare("SELECT p.productName, p.Price, c.quantity, (p.Price * c.quantity) AS total, c.product_id
                            FROM cart_items c
                            JOIN products p ON c.product_id = p.ItemID
                            WHERE c.cart_id = (SELECT cart_id FROM cart WHERE user_id = ?)");
    $stmt->execute([$user_id]);
    $cartItems = $stmt->fetchAll();

    // Calculate subtotal
    $subtotal = array_sum(array_column($cartItems, 'total'));
} else {
    $cartItems = [];
    $subtotal = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="styles.css">

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

        nav a {
        margin: 0 15px;
        text-decoration: none;
        color: #333;
        }

        .cart-icon {
            font-size: 18px;
            position: relative;
        }

        .cart-page {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .cart-container {
            background-color: #fff;
            padding: 40px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        .cart-container h1 {
            font-size: 28px;
            margin-bottom: 20px;
        }

        .continue-browsing {
            display: inline-block;
            margin-bottom: 20px;
            color: #555;
            text-decoration: none;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        table th, table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        table th {
            background-color: #f4f4f4;
            font-weight: bold;
        }

        .subtotal-section {
            font-size: 24px;
            margin-bottom: 20px;
        }
        .subtotal-section h2 {
            font-weight: normal;
        }

        .checkout-section {
            margin-top: 30px;
        }

        .checkout-section button {
            background-color: #333;
            color: #fff;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            font-size: 18px;
            transition: background-color 0.3s;
        }

        .checkout-section button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        footer {
            background-color: #fff;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #ddd;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    
 
<div class="cart-page">
    <!-- Main Cart Section -->
    <section class="cart-container">
        <h1>Your Cart</h1>
        <a href="homepage.html#products" class="continue-browsing">← Continue browsing</a>

        <table id="cart-content">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Price (R)</th>
                    <th>Quantity</th>
                    <th>Total (R)</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($cartItems)): ?>
                    <?php foreach ($cartItems as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['productName']); ?></td>
                            <td>R<?= number_format($item['Price'], 2); ?></td>
                            <td><?= htmlspecialchars($item['quantity']); ?></td>
                            <td>R<?= number_format($item['total'], 2); ?></td>
                            <td>
                                <button class="remove-btn" onclick="removeFromCart(<?= $item['product_id']; ?>)">Remove</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">Your cart is empty.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Subtotal Section -->
        <div class="subtotal-section">
            <h2>Subtotal: R<span id="subtotal"><?= number_format($subtotal, 2); ?></span></h2>
            <p>The above total is an estimated amount, calculation before delivery costs.</p>
        </div>

        <!-- Terms and Checkout Button -->
        <div class="checkout-section">
            <input type="checkbox" id="terms">
            <label for="terms">I acknowledge and agree that my order processing will commence upon receipt of my payment or arrival.</label>
            <br>
            <a href="deliverypage.html"><button id="checkout-btn" disabled>Checkout</button></a>
        </div>
    </section>
</div>

<!-- Include SweetAlert -->
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

<script>
    // Enable Checkout button if terms checkbox is selected
    document.getElementById('terms').addEventListener('change', function() {
        document.getElementById('checkout-btn').disabled = !this.checked;
    });

    // Function to remove item from cart
    function removeFromCart(itemId) {
        fetch('remove_from_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `item_id=${itemId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove item row from table
                document.querySelector(`tr[data-item-id="${itemId}"]`).remove();

                // Update subtotal
                document.getElementById('subtotal').textContent = data.newSubtotal.toFixed(2);

                // Show success message
                swal("Removed", "Item removed from cart", "success");
            } else {
                swal("Error", "Failed to remove item from cart", "error");
            }
        })
        .catch(error => {
            console.error('Error:', error);
            swal("Error", "An error occurred. Please try again.", "error");
        });
    }

    //--------------------------Remove item-------------------

function removeFromCart(productId) {
    fetch('cartpage.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ 'action': 'remove_from_cart', 'product_id': productId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Refresh the page to show updated cart items
            location.reload();
        } else {
            alert('Failed to remove item from cart.');
        }
    })
    .catch(error => console.error('Error:', error));
}
</script>


    <footer id="footer">
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
                    <li><a href="homepage.php">Shop Now</a></li>
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

    <script>
        // Fetch cart data from localStorage
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        
        function renderCart() {
            const cartContent = document.querySelector('#cart-content tbody');
            const subtotalElement = document.getElementById('subtotal');
            cartContent.innerHTML = ''; // Clear previous content
            let subtotal = 0; // Initialize subtotal

            // Check if the cart is empty
            if (cart.length === 0) {
                cartContent.innerHTML = `<tr><td colspan="6">Your cart is empty.</td></tr>`;
                subtotalElement.textContent = "0.00"; // Set subtotal to 0 if cart is empty
                return; // Exit the function
            }

            // Loop through each item in the cart
            cart.forEach((item, index) => {
                const itemTotal = item.price * item.quantity; // Calculate total for the item
                subtotal += itemTotal; // Add to subtotal

                // Create cart row
                cartContent.innerHTML += `
                    <tr>
                        <td>${item.name}</td>
                        <td>R${item.price.toFixed(2)}</td>
                        <td>
                            <input type="number" value="${item.quantity}" min="1" onchange="updateQuantity(${index}, this.value)">
                        </td>
                        <td>R${itemTotal.toFixed(2)}</td>
                        <td>
                            <button onclick="removeFromCart(${index})">Delete</button>
                        </td>         
                    </tr>
                `;
            });

            subtotalElement.textContent = subtotal.toFixed(2); // Update subtotal display
        }

        // Update quantity in cart
        function updateQuantity(index, newQuantity) {
            if (newQuantity > 0) {
                cart[index].quantity = parseInt(newQuantity); // Update item quantity
                localStorage.setItem('cart', JSON.stringify(cart)); // Save updated cart
                renderCart(); // Re-render the cart
            }
        }

        // Remove item from cart
        function removeFromCart(index) {
            cart.splice(index, 1); // Remove item at index
            localStorage.setItem('cart', JSON.stringify(cart)); // Save updated cart
            renderCart(); // Re-render the cart
        }

        // Terms checkbox and checkout button logic
        const termsCheckbox = document.getElementById('terms');
        const checkoutBtn = document.getElementById('checkout-btn');

        termsCheckbox.addEventListener('change', () => {
            checkoutBtn.disabled = !termsCheckbox.checked; // Enable/disable button based on checkbox
        });

        // Initialize cart on page load
        window.onload = () => {
            renderCart(); // Call renderCart to display cart items
        };
    </script>

</body>
</html>
