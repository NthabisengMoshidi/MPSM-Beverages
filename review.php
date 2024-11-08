<?php
// Start the session to ensure user ID is available
session_start();
include('config.php');

// Sanitize incoming ItemID (product ID) to avoid security risks
$ItemID = isset($_GET['products_ItemID']) ? intval($_GET['products_ItemID']) : 0;

// Fetch product details (name and image) from the database
$stmt = $conn->prepare("SELECT productName, imageURL FROM products WHERE ItemID = ?");
$stmt->bind_param("i", $ItemID);
$stmt->execute();
$productResult = $stmt->get_result();
$product = $productResult->fetch_assoc();
$stmt->close();

$userReviews = []; // Array to store user's previous reviews
if (isset($_SESSION['user_id'])) {
    $userID = $_SESSION['user_id'];

    // Fetch all reviews for this product by the logged-in user
    $stmt = $conn->prepare("SELECT reviewID, rating, reviewText, created_at FROM reviews WHERE ItemID = ? AND user_id = ?");
    $stmt->bind_param("ii", $ItemID, $userID);
    $stmt->execute();
    $userReviewResult = $stmt->get_result();
    while ($row = $userReviewResult->fetch_assoc()) {
        $userReviews[] = $row;
    }
    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MPSM Beverages</title>
    <link rel="icon" type="image/x-icon" href="images/logo.jpg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* General styling */
        body { background-color: #f9f9f9; font-family: Arial, sans-serif; }
        header, footer { background-color: #fff; padding: 10px 20px; border: 1px solid #ddd; }
        .table { width: 80%; margin: 20px auto; border-collapse: collapse; }
        .table, .table th, .table td { border: 1px solid #ddd; padding: 10px; }
        button { padding: 10px 20px; background-color: orange; color: #fff; border: none; cursor: pointer; }
        button:hover { background-color: green; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: #fff; margin: 15% auto; padding: 20px; width: 50%; }
        .stars { display: flex; gap: 5px; }
        .star { font-size: 30px; cursor: pointer; color: gray; }
        .star.selected { color: gold; }
    </style>
</head>
<body>

<header>
    <div class="logo"><img src="logo.jpg" alt="MPSM Beverages" width="150"></div>
    <nav>
        <ul>
            <li><a href="homepage.php">Home</a></li>
            <li><a href="homepage.php#products">Shop Now</a></li>
            <li><a href="orders.html">Orders</a></li>
            <li><a href="#footer">Contact Us</a></li>
        </ul>
    </nav>
</header>

<main>
    <section ><center>
        <h1><?= htmlspecialchars($product['productName'] ?? 'Product') ?></h1>
        <img src="<?= htmlspecialchars($product['imageURL'] ?? 'default.jpg') ?>" alt="<?= htmlspecialchars($product['productName'] ?? 'Product') ?>" style="width: 300px; height: auto;">
    </center></section>

    <h2>Product Reviews</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Rating</th>
                <th>Reviewer</th>
                <th>Review</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $stmt = $conn->prepare("SELECT reviews.rating, reviews.reviewText, reviews.reviewerName, reviews.created_at FROM reviews WHERE reviews.ItemID = ?");
            $stmt->bind_param("i", $ItemID);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $ratingStars = str_repeat('★', $row['rating']) . str_repeat('☆', 5 - $row['rating']);
                    echo "<tr>
                            <td>$ratingStars</td>
                            <td>" . htmlspecialchars($row['reviewerName'] ?: 'Anonymous') . "</td>
                            <td>" . htmlspecialchars($row['reviewText']) . "</td>
                            <td>" . htmlspecialchars($row['created_at']) . "</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='4'>No reviews yet. Be the first to review!</td></tr>";
            }
            $stmt->close();
            ?>
        </tbody>
    </table>
    <br><br>
    <div><center>
        <?php if (isset($_SESSION['user_id'])): ?>
            <button onclick="document.getElementById('reviewModal').style.display='block'">Leave a Review</button>
        <?php else: ?>
            <p>Please <a href="registrationpage.php">register</a> / <a href="loginpage.php">login</a> first in order to leave a review.</p>
        <?php endif; ?>
    </center></div>
    <br>   
</main>

<footer id="footer">
    <!-- Footer Content -->
</footer>

<!-- Review Modal -->
<div id="reviewModal" class="modal">
    <div class="modal-content">
        <span onclick="document.getElementById('reviewModal').style.display='none'" style="float:right;cursor:pointer">&times;</span>
        
        <h3>Your Previous Reviews</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Rating</th>
                        <th>Review</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($userReviews)): ?>
                        <?php foreach ($userReviews as $review): ?>
                            <tr>
                                <td><?= str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']); ?></td>
                                <td><?= htmlspecialchars($review['reviewText']); ?></td>
                                <td><?= htmlspecialchars($review['created_at']); ?></td>
                                <td>
                                    <button onclick="editReview(<?= $review['reviewID']; ?>)">Edit</button>
                                    <button onclick="deleteReview(<?= $review['reviewID']; ?>)">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4">No reviews submitted yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        
        <h3>Leave a Review</h3>
        <form action="submit_review.php" method="POST">
            <input type="hidden" name="ItemID" value="<?= htmlspecialchars($ItemID); ?>">
            <label for="rating">Rating (1-5):</label><br>
            <div class="stars">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <span class="star" onclick="selectStarRating(<?= $i ?>)">★</span>
                <?php endfor; ?>
            </div>
            <input type="hidden" name="rating" id="rating" value="5"><br><br>
            <label for="reviewText">Your Review:</label><br>
            <textarea name="reviewText" id="reviewText" rows="4" required></textarea><br><br>
            <label for="reviewerName">Your Name (Optional):</label><br>
            <input type="text" name="reviewerName" id="reviewerName"><br><br>
            <button type="submit">Submit Review</button>
        </form>
    </div>
</div>

<script>
    function selectStarRating(rating) {
        document.getElementById('rating').value = rating;
        const stars = document.querySelectorAll('.star');
        stars.forEach((star, index) => star.classList.toggle('selected', index < rating));
    }
    // Function to parse query parameters
    function getQueryParameter(name) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(name);
    }

    function editReview(reviewID) {
        // Redirect to edit review page or open an edit form
        window.location.href = `edit_review.php?reviewID=${reviewID}`;
    }

    function deleteReview(reviewID) {
        // Use SweetAlert for confirmation before deletion
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Perform the delete action with fetch
                fetch(`delete_review.php?reviewID=${reviewID}`, { method: 'GET' })
                    .then(response => response.text())
                    .then(data => {
                        console.log("Response from server:", data); // Log the response for debugging
                        if (data.trim() === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'Review deleted successfully.',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Failed!',
                                text: 'Failed to delete review. Please try again.',
                                confirmButtonText: 'OK'
                            });
                            console.error("Error deleting review:", data); // Log any errors
                        }
                    })
                    .catch(error => {
                        console.error("Error:", error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while deleting the review. Please check your network or contact support.',
                            confirmButtonText: 'OK'
                        });
                    });
            }
        });
    }

 

    // Show SweetAlert based on success or error parameters
    document.addEventListener('DOMContentLoaded', function () {
        const success = getQueryParameter('success');
        const error = getQueryParameter('error');

        if (success) {
            Swal.fire({
                icon: 'success',
                title: 'Review Submitted!',
                text: 'Your review has been successfully submitted.',
                confirmButtonText: 'Okay'
            });
        } else if (error) {
            let errorMessage;
            switch(error) {
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


</body>
</html>
