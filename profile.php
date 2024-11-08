

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MPSM Beverages</title>
    <link rel="icon" type="image/x-icon" href="images/logo.jpg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
    button {
            padding: 10px 20px;
            background-color: #333;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            align-self: center;
        }

        button:hover {
            background-color: #555;
        }
    .profile-section {
    width: 80%;
    margin: auto;
    padding: 20px;
    text-align: center;
}

.profile-options {
    display: flex;
    justify-content: space-around;
    flex-wrap: wrap;
}

.profile-options .option {
    width: 150px;
    text-align: center;
    margin: 20px;
}

.profile-options .option img {
    width: 100px;
    height: 100px;
}

.profile-options .option p {
    margin-top: 10px;
    font-weight: bold;
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

</style>
</head>
<body>

    <header>
        <div class="logo"><img src="images/logo.jpg"></img></div>
        <nav>
            <ul>
                <li><a href="homepage.php">Home</a></li>
                <li><a href="homepage.php#products">Shop Now</a></li>
                <li><a href="orders.html">Orders</a></li>
                <li><a href="#footer">Contact Us</a></li>
            </ul>
        </nav>
        <div class="icons">
            <div class="search-icon"><i class="fa-solid fa-magnifying-glass"></i></div>
            <div class="cart-icon"><a href="cartpage.php"><i class="fa-solid fa-cart-shopping"></i></a></div>
            <div class="profile-icon"><a href="profile.html"><i class="fa-solid fa-user"></i></a></div>
        </div>
    </header>


    <div class="profile-section">
        <h2>Welcome User</h2>
        <div class="profile-options">
            <div class="option">
                <a href="cartpage.php"><img src="images/cart-icon.jpg" alt="My Cart"></a>
                <p>My Cart</p>
            </div>
            <div class="option">
                <a href="orders.html"><img src="images/orders-icon.jpg" alt="My Orders"></a>
                <p>My Orders</p>
            </div>
            <div class="option">
                <a href="changepersonalinfo.php"><img src="images/personal-info-icon.jpg" alt="Personal Information"></a>
                <p>Personal Information</p>
            </div>
            <div class="option">
                <a href="homeaddress.php"><img src="images/address-icon.jpg" alt="Home Address"></a>
                <p>Home Address</p>
            </div>
            <div class="option">
                <a href="changepassword.php"><img src="images/change-password-icon.jpg" alt="Change Password"></a>
                <p>Change Password</p>
            </div>
        </div>
    </div>

    <center><button id="button">Logout</button></center>
    
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
<script>
    document.getElementById("button").addEventListener("click", function() {
            window.location.href = 'logout.php'; 
        });
</script>
</body>
</html>