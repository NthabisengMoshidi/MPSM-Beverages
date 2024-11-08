<?php     
include('blueprint.php');

// Check if this is a deletion request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['deleteItems'])) {
    $itemIds = $_POST['deleteItems'];

    // Open debug log and log received IDs
    $logFile = 'debug_log.txt';
    $logMessage = "Delete request received for Item IDs: " . implode(", ", $itemIds) . "\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);

    // Prepare delete statement
    $placeholders = implode(',', array_fill(0, count($itemIds), '?'));
    $sql = "DELETE FROM products WHERE ItemID IN ($placeholders)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param(str_repeat('i', count($itemIds)), ...$itemIds);
        if ($stmt->execute()) {
            $responseMessage = 'Selected products have been deleted.';
            $logMessage = "Successfully deleted Item IDs: " . implode(", ", $itemIds) . "\n";
        } else {
            $responseMessage = 'Error deleting products.';
            $logMessage = "Error deleting products: " . $stmt->error . "\n";
        }
        $stmt->close();
    } else {
        $responseMessage = 'Failed to prepare statement.';
        $logMessage = "Statement preparation failed: " . $conn->error . "\n";
    }

    file_put_contents($logFile, $logMessage, FILE_APPEND);
    //header("Location: products.php?status=message&message=" . urlencode($responseMessage));
    exit;
}

// If not a deletion request, load and display products as usual
$sql = "SELECT * FROM products";
$result = $conn->query($sql);

if ($result === false) {
    die("Error in query execution: " . $conn->error);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script> <!-- Include SweetAlert -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<link rel="stylesheet" href="path/to/sweetalert2.min.css">
    <script src="path/to/sweetalert2.all.min.js"></script>
 <style>
 body { 
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    background-attachment: fixed;
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    padding-bottom: 60px; /* Space for footer */
    overflow-x: hidden; /* Prevent horizontal scrolling */
}

/* Title styles */
h1 {
    text-align: center;
    color: #2980b9;
    margin-top: 80px; /* Adjusted for fixed nav */
    animation: fadeIn 1s;
}
h2{
    text-align: center;
    color: #2980b9;
    margin-top: 80px; /* Adjusted for fixed nav */
    animation: fadeIn 1s;
    font-size: 20px ;
    margin-top:10px;
}

/* Container for the table */
.table-container {
    max-width: 90%; /* Set a maximum width */
    margin: 50px auto; /* Center and provide margin */
    padding: 20px; /* White space around table */
    background-color: rgba(255, 255, 255, 0.8); /* Light background for contrast */
    border-radius: 10px; /* Rounded corners */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Subtle shadow */
    margin-left: 350px; /* Add margin to the left to accommodate sidebar */
    margin-right: 100px;
    margin-top: 20px; /* Added space above the table */
    overflow-x: auto; /* Enable horizontal scrolling for small screens */
}

/* Table styles */
table {
    width: 100%;
    border-collapse: collapse;
    animation: fadeIn 1s;
}

/* Table headers and cells */
th, td {
    padding: 5px; /* Reduced padding */
    text-align: left;
    border-bottom: 1px solid grey; /* Grey border for rows */
}

/* Header styles */
th {
    background-color: orange; /* Orange background for headers */
    color: white;
}

/* Row hover effect */
tr:hover {
    background-color: rgba(255, 165, 0, 0.1); /* Hint of orange on hover */
    transition: background-color 0.3s ease;
}

/* Image styles */
.product-image {
    width: 60px; /* Smaller image size */
    height: auto;
    border-radius: 5px;
}

/* Button styles */
.action-button {
    padding: 5px 10px;
    border: 1px solid orange;
    background-color: white;
    color: orange;
    cursor: pointer;
    border-radius: 5px;
    transition: background-color 0.3s ease, color 0.3s ease;
}

.action-button:hover {
    background-color: orange;
    color: white;
}

/* Stock icon styles */
.stock-icon {
    font-size: 20px; /* Adjust icon size */
    width: 50px; /* Decreased width for stock level */
    text-align: center; /* Center align icon */
}

/* Stock status colors */
.low-stock {
    color: red; /* Red for low stock */
}

.good-stock {
    color: green; /* Green for good stock */
}

/* Availability status */
.availability-status {
    width: 150px; /* Decreased width for availability status */
}

/* Animation for fade in effect */
@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

/* Footer styles */
footer {
    background-color: rgba(255, 165, 0, 0.8);
    color: white;
    text-align: center;
    padding: 10px 0;
    position: fixed;
    bottom: 0;
    width: calc(100% - 250px); /* Adjusted for sidebar */
    left: 250px;
    display: none; /* Initially hidden */
}

/* Show footer when scrolled to the bottom */
body:has(footer:visible) {
    padding-bottom: 80px; /* Space for footer */
}

/* Ensure footer is displayed when scrolled */
.footer-visible {
    display: block;
}

/*----------------------------------------Edit Product --------------------------------*/

/* Modal and Tooltip styles */
#editModal {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: #f8f9fa;
    padding: 30px 40px;
    z-index: 1000;
    width: 400px;
    max-height: 80%; /* Set a maximum height for the modal */
    overflow-y: auto; /* Enable vertical scrolling */
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    font-family: Arial, sans-serif;
    color: #333;
}

/* Modal header styles */
#editModal h2 {
    margin-top: 0;
    color: #333;
    font-size: 20px;
    font-weight: bold;
    text-align: center;
    padding-bottom: 10px;
    border-bottom: 1px solid #ddd;
}

/* Close button styles */
.close-button {
    position: absolute;
    top: 10px;
    right: 10px;
    cursor: pointer;
    font-size: 18px;
    color: #888;
}

/* Form label styles */
form label {
    display: block;
    margin-top: 15px;
    font-weight: 500;
    color: #555;
}

/* Form input styles */
form input[type="text"],
form input[type="number"],
form input[type="date"],
form select {
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    border: 1px solid #ddd;
    border-radius: 5px;
    box-sizing: border-box;
    font-size: 14px;
    color: #333;
    transition: border-color 0.2s;
}

/* Input focus effect */
form input[type="text"]:focus,
form input[type="number"]:focus,
form input[type="date"]:focus,
form select:focus {
    border-color: #007bff;
    outline: none;
}

/* Button styles for submit and cancel */
.submit-btn,
.cancel-btn {
    width: 100%;
    padding: 12px;
    font-size: 15px;
    font-weight: bold;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    margin-top: 20px;
    transition: background-color 0.2s;
}

/* Submit button styles */
.submit-btn {
    background-color: #28a745;
}

.submit-btn:hover {
    background-color: #218838;
}

/* Cancel button styles */
.cancel-btn {
    background-color: #dc3545;
}

.cancel-btn:hover {
    background-color: #c82333;
}

/* Tooltip styles */
.tooltip {
    position: relative;
    display: inline-block;
}

/* Tooltip text */
.tooltip .tooltiptext {
    visibility: hidden;
    width: 200px;
    color: black;
    text-align: center;
    border-radius: 5px;
    padding: 5px;
    position: absolute;
    z-index: 1;
    bottom: 125%; /* Position the tooltip above the icon */
    left: 50%;
    margin-left: -100px; /* Center the tooltip */
    opacity: 0;
    transition: opacity 0.3s;
}

/* Show the tooltip text when hovering over the icon */
.tooltip:hover .tooltiptext {
    visibility: visible;
    opacity: 1;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .table-container {
        margin-left: 20px; /* Adjust left margin for smaller screens */
        margin-right: 20px; /* Adjust right margin for smaller screens */
    }

    .action-button {
        padding: 8px; /* Adjust button padding for smaller screens */
    }

    th, td {
        padding: 10px; /* Adjust padding for smaller screens */
    }
}


/*----------------------------------------Add Product --------------------------------*/
.add-product-btn {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    background: linear-gradient(45deg, #ff8008, #ffc837);
    color: white;
    font-size: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    border: none;
    position: fixed;
    bottom: 40px;
    right: 40px;
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
}

.add-product-btn:hover {
    transform: scale(1.1);
}

/* Styling for the popup form */
.popup-form {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: #fff;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    z-index: 999;
    width: 400px;

    /* New scroll bar styles */
    max-height: 80vh; /* Limit height to 80% of viewport */
    overflow-y: auto; /* Enable vertical scrolling */
}

.popup-form h3 {
    text-align: center;
    margin-bottom: 20px;
}

.popup-form input, .popup-form select, .popup-form textarea {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
}

.popup-form button {
    width: 100%;
    padding: 10px;
    background-color: #28a745;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.popup-form button:hover {
    background-color: #218838;
}

.popup-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 998;


}/*----------------------------- View Form Styling-----------------*/
/* Modal container styling */
.modal {
    display: none; /* Hidden by default */
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.5); /* Black with opacity */
    padding-top: 60px;
}

/* Modal content */
.modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 400px; /* Smaller form */
    max-height: 80vh; /* Limit the height of the modal */
    overflow-y: auto; /* Add vertical scrollbar if content overflows */
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    position: relative;
    text-align: center; /* Center content */
}


/* Close button (X) in the top-right */
.close {
    color: #aaa;
    position: absolute;
    top: 10px;
    right: 15px;
    font-size: 28px;
    font-weight: bold;
}

.close:hover, .close:focus {
    color: #000;
    text-decoration: none;
    cursor: pointer;
}

/* Form label styling */
form label {
    display: block;
    margin: 10px 0 5px;
    font-weight: bold;
}

/* Form input styling */
form input[type="text"] {
    width: 100%;
    padding: 8px;
    margin-bottom: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box;
}

/* Container for the image */
.image-container {
    display: block;
    width: 100%;
    max-width: 200px; /* Set a fixed size for the image container */
    height: 200px; /* Square container */
    margin: 20px auto; /* Center the container */
    overflow: hidden;
    border-radius: 8px;
    border: 1px solid #ccc;
    background-color: #f4f4f4; /* Optional: light background for the image container */
}

/* Image styling */
.image-container img {
    width: 100%;
    height: 100%;
    object-fit: cover; /* Maintain aspect ratio and cover container */
}

/* Close button in the form */
.close-button {
    background-color: #f44336; /* Red background */
    color: white;
    padding: 10px 20px;
    border: none;
    cursor: pointer;
    border-radius: 4px;
    font-size: 14px;
    margin-top: 20px; /* Adds space between the button and form elements */
    display: inline-block;
    width: auto; /* Adjusted to be smaller */
    text-align: center;
}

/* Hover effect for the close button */
.close-button:hover {
    background-color: #d11a1a; /* Darker red when hovered */
}

/* Center content horizontally */
.modal-content h2, form, .close-button {
    text-align: center;
}

/*--------------- Filter Section ----------------------*/
.filter-container {
    display: flex;
    justify-content: flex-end; /* Aligns the entire filter section to the right */
    padding: 20px;
    border: 1px solid #ccc;
    width: 100%; /* Full width to contain all filters */
    background-color: #f9f9f9;
    width: 1050px;
    margin-left: 450px;
    margin-right: 100px;
}

.filter-group {
    margin-right: 15px; /* Space between filter groups */
}

label {
    display: none; /* Hide labels since we are aligning filters in one line */
}

select, input[type="text"], input[type="date"], input[type="number"] {
    padding: 5px;
    /* margin-right: 0; Remove margin to keep inputs aligned */
    min-width: 100px; /* Set a minimum width for inputs */
}

/*--------------------- CSS code for button select---------------------------*/
.button-container {
    text-align: center;
    margin-bottom: 20px; /* Adds some space between buttons and the table */
    margin-top: 40px;
}

.button-container button {
    background-color: #007bff; /* Primary blue color */
    color: white;
    padding: 10px 20px;
    margin: 0 10px; /* Adds space between buttons */
    border: none;
    border-radius: 5px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s;
}

.button-container button:hover {
    background-color: #0056b3; /* Darker blue on hover */
}

.button-container button:active {
    background-color: #003f7f; /* Even darker blue on click */
}

#deleteButton {
    background-color: #dc3545; /* Red color for Delete button */
}

#deleteButton:hover {
    background-color: #a71d2a; /* Darker red on hover */
}

#deleteButton:active {
    background-color: #7d131e; /* Even darker red on click */
}

</style>
    
</head>

<body>


<h1>Inventory Management</h1>

<!------------------------------Filter Section-------------------------------------->
<div class="filter-container">
    <div class="filter-group">
        <label for="stockLevel">Stock Level</label>
        <input type="number" id="stockLevel" placeholder="Min Stock">
    </div>

    <div class="filter-group">
        <label for="category">Category</label>
        <select id="category">
            <option value="">Select Category</option>
            <option value="Water">Water</option>
            <option value="Juices">Juices</option>
            <option value="Customised Beverages">Customised Beverages</option>
            <option value="Distillation Equipment">Distillation Equipment</option>
            <option value="Ice">Ice</option>
        </select>
    </div>

    <div class="filter-group">
        <label for="availability">Availability</label>
        <select id="availability">
            <option value="">Select Availability</option>
            <option value="Available">Available</option>
            <option value="Out of Stock">Out of Stock</option>
            <option value="Pre-Order">Pre-Order</option>
        </select>
    </div>

    <div class="filter-group">
        <label for="minPrice">Price (Min)</label>
        <input type="number" id="minPrice" placeholder="Min Price" step="0.01">
    </div>

    <div class="filter-group">
        <label for="expirationDate">Expiration Date</label>
        <input type="date" id="expirationDate">
    </div>

    <div class="filter-group">
        <label for="search">Search</label>
        <input type="text" id="search" placeholder="Product ID, Name, SKU">
    </div>

    <div id="feedbackMessage" style="color: red; display: none;"></div> <!-- Feedback message container -->
</div>

<h2>Product List</h2>
<div class="button-container">
    <button id="selectButton">Select</button>
    <button id="selectAllButton" style="display: none;">Select All</button>
    <button id="deleteButton" style="display: none;">Delete</button>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th class="checkbox-column" style="display: none;">Select</th>
                <th>Stock Level</th>
                <th>Product ID</th>
                <th>Image</th>
                <th>Product Name</th>
                <th>SKU</th>
                <th>Price</th>
                <th>Category</th>
                <th>Availability Status</th>
                <th>Volume</th>
                <th>Expiration Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <?php
                    $tooltipMessage = "";
                    $availabilityStatus = '';

                    if (intval($row["stockLevel"]) === 0) {
                        $availabilityStatus = 'Out of Stock';
                        $stockClass = 'low-stock';
                        $stockIcon = 'fas fa-exclamation-circle';
                        $tooltipMessage = "Stock level is 0, set availability status to 'Out of Stock'";
                    } else {
                        $stockClass = (intval($row["stockLevel"]) < $row["restockLevel"]) ? 'low-stock' : 'good-stock';
                        $stockIcon = (intval($row["stockLevel"]) < 10) ? 'fas fa-exclamation-circle' : 'fas fa-check-circle';
                        $availabilityStatus = (intval($row["stockLevel"]) > 0) ? 'Available' : 'Out of Stock';
                        if ($stockClass === 'low-stock') {
                            $tooltipMessage = "Low stock";
                        }
                    }
                    ?>
                    <tr>
                        <td class="checkbox-column" style="display: none;">
                            <input type="checkbox" name="deleteItems[]" value="<?php echo htmlspecialchars($row["ItemID"]); ?>" class="select-row">
                        </td>
                        <td>
                            <div class="tooltip">
                                <i class="<?php echo $stockIcon; ?> stock-icon <?php echo $stockClass; ?>"></i>
                                <span class="tooltiptext"><?php echo htmlspecialchars($tooltipMessage); ?></span>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($row["ItemID"]); ?></td>
                        <td><img src="<?php echo htmlspecialchars($row["imageURL"]); ?>" class="product-image" alt="<?php echo htmlspecialchars($row["productName"]); ?>"></td>
                        <td><?php echo htmlspecialchars($row["productName"]); ?></td>
                        <td><?php echo htmlspecialchars($row["SKU"]); ?></td>
                        <td>R<?php echo number_format($row["Price"], 2); ?></td>
                        <td><?php echo htmlspecialchars($row["Category"]); ?></td>
                        <td><?php echo htmlspecialchars($availabilityStatus); ?></td>
                        <td><?php echo htmlspecialchars($row["volume"]); ?></td>
                        <td><?php echo $row["expirationDate"] ? htmlspecialchars($row["expirationDate"]) : 'N/A'; ?></td>
                        <td>
                            <button class='action-button view-btn' data-id='<?php echo $row['ItemID']; ?>'>View</button>
                            <button class='action-button edit-btn' data-id='<?php echo $row['ItemID']; ?>'>Edit</button>
                            <button class='action-button delete-btn' data-id='<?php echo htmlspecialchars($row["ItemID"]); ?>'>Delete</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="12" style="text-align:center;">No products found</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
    const selectButton = document.getElementById("selectButton");
    const selectAllButton = document.getElementById("selectAllButton");
    const deleteButton = document.getElementById("deleteButton");
    const checkboxes = document.querySelectorAll(".select-row");
    const checkboxColumns = document.querySelectorAll(".checkbox-column");

    // Toggle display of Select All, Delete, and checkboxes
    selectButton.addEventListener("click", function() {
        const isSelecting = selectButton.innerText === "Select";
        selectAllButton.style.display = isSelecting ? "inline-block" : "none";
        deleteButton.style.display = isSelecting ? "inline-block" : "none";
        selectButton.innerText = isSelecting ? "Deselect" : "Select";
        checkboxColumns.forEach(cell => cell.style.display = isSelecting ? "table-cell" : "none");
        if (!isSelecting) checkboxes.forEach(checkbox => checkbox.checked = false); // Uncheck all on deselect
    });

    // Select All functionality
    selectAllButton.addEventListener("click", function() {
        const allChecked = Array.from(checkboxes).every(checkbox => checkbox.checked);
        checkboxes.forEach(checkbox => checkbox.checked = !allChecked);
    });

    // Delete button with SweetAlert confirmation and page reload
    deleteButton.addEventListener("click", function() {
        const selectedItems = Array.from(checkboxes).filter(checkbox => checkbox.checked);
        if (selectedItems.length === 0) {
            Swal.fire({
                title: "No items selected",
                text: "Please select items to delete.",
                icon: "warning",
                confirmButtonText: "OK"
            });
            return;
        }

        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#dc3545",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Yes, delete them!"
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement("form");
                form.method = "POST";
                form.action = "products.php";

                selectedItems.forEach(item => {
                    const input = document.createElement("input");
                    input.type = "hidden";
                    input.name = "deleteItems[]";
                    input.value = item.value;
                    form.appendChild(input);
                });

                Swal.fire({
                    title: "Deleted!",
                    text: "The selected items will be deleted.",
                    icon: "success",
                    confirmButtonText: "OK"
                }).then(() => {
                    document.body.appendChild(form);
                    form.submit();
                });
            }
        });
    });
</script>


<!-------------HTML Form Codes --------------->  

<!------------- Edit Product Form --------------->  
<div id="editModal" style="display: none;"> <!-- Hide by default -->
    <span class="close-button" onclick="closeModal()">&times;</span>
    <h2>Edit Product</h2>
    <form id="editForm" method="POST" action="update_products.php" enctype="multipart/form-data">
        <input type="hidden" name="ItemID" id="editItemID">
        
        <label for="productName">Product Name:</label>
        <input type="text" name="productName" id="editProductName" required>
        
        <label for="SKU">SKU:</label>
        <input type="text" name="SKU" id="editSKU" required>
        
        <label for="price">Price:</label>
        <input type="number" name="price" id="editPrice" step="0.01" required>
        
        <label for="Category">Category:</label>
        <select id="editCategory" name="Category" >
            <option value="Water">Water</option>
            <option value="Juices">Juices</option>
            <option value="Customised Beverages">Customised Beverages</option>
            <option value="Distillation Equipment">Distillation Equipment</option>
            <option value="Ice">Ice</option>
        </select>
        
        <label for="stockLevel">Stock Level:</label>
        <input type="number" name="stockLevel" id="editStockLevel" >
        
        <label for="restockLevel">Restock Level:</label>
        <input type="number" name="restockLevel" id="editRestockLevel" >

        <label for="availabilityStatus">Availability Status:</label>
        <select id="editAvailabilityStatus" name="availabilityStatus" required>
            <option value="Available">Available</option>
            <option value="Out of Stock">Out of Stock</option>
            <option value="Pre-Order">Pre-Order</option>
        </select>
        
        <label for="volume">Volume:</label>
        <input type="text" name="volume" id="editVolume" >
        
        <label for="expirationDate">Expiration Date:</label>
        <input type="date" name="expirationDate" id="editExpirationDate">

        <!-- New Image Upload Section -->
        <label for="imageURL">Product Image:</label>
        <input type="file" name="imageURL" id="editImageURL" accept="image/*" onchange="previewImage(event)">
        <img id="imagePreview" src="" alt="Current Image" style="display:none; max-width: 100%; margin-top: 10px;"/>

        <button type="submit" class="submit-btn" id="saveButton">Save Changes</button>
    </form>
</div>

<!---------------------- View Form------------------------------------>
<!-- Modal for Viewing Product -->
<div id="viewProductModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>View Product</h2>
        <form id="viewProductForm" novalidate>

            <!-- Image container for product image -->
            <div class="image-container">
                <img id="viewImageURL" alt="Product Image">
            </div>

            <label>Item ID:</label>
            <input type="text" id="viewItemID" readonly><br>

            <label>Product Name:</label>
            <input type="text" id="viewProductName" readonly><br>

            <label>SKU:</label>
            <input type="text" id="viewSKU" readonly><br>

            <label>Price:</label>
            <input type="text" id="viewPrice" readonly><br>

            <label>Category:</label>
            <input type="text" id="viewCategory" readonly><br>

            <label>Stock Level:</label>
            <input type="text" id="viewStockLevel" readonly><br>

            <label>Restock Level:</label>
            <input type="text" id="viewRestockLevel" readonly><br>

            <label>Availability Status:</label>
            <input type="text" id="viewAvailabilityStatus" readonly><br>

            <label>Volume:</label>
            <input type="text" id="viewVolume" readonly><br>

            <label>Expiration Date:</label>
            <input type="text" id="viewExpirationDate" readonly><br>

          
        </form>
    </div>
</div>



    <!--<button id="addProductBtn" class="add-product-button">Add Product</button>-->

    <!-- Add Product Button -->
    <button class="add-product-btn" id="addProductBtn">
    <i class="fas fa-plus"></i>
</button>

<!-- ------------------------Add Products Form --------------------------------->

<!-- Popup Overlay and Form --> 
<div class="popup-overlay" id="popupOverlay"></div>
<div class="popup-form" id="popupForm">
    <h3>Add New Product</h3>
    <form id="addProductForm" action="/ITPJA/Prototype%20Drafts/Admin%20Prototype/add_product.php" enctype="multipart/form-data" method="POST">
        <label for="productName">Product Name:</label>
        <input type="text" id="productName" name="productName" placeholder="Product Name" required>

        <label for="SKU">SKU:</label>
        <input type="text" id="SKU" name="SKU" placeholder="SKU" required>

        <label for="price">Price:</label>
        <input type="number" step="0.01" id="price" name="price" placeholder="Price" required>

        <label for="Category">Category:</label>
        <select id="Category" name="Category" required>
            <option value="Water">Water</option>
            <option value="Juices">Juices</option>
            <option value="Customised Beverages">Customised Beverages</option>
            <option value="Distillation Equipment">Distillation Equipment</option>
        </select>

        <label for="stockLevel">Stock Level:</label>
        <input type="number" id="stockLevel" name="stockLevel" placeholder="Stock Level" required>

        <label for="restockLevel">Restock Level:</label>
        <input type="number" id="restockLevel" name="restockLevel" placeholder="Restock Level" required>
        
        <label for="availabilityStatus">Availability Status:</label>
        <select id="availabilityStatus" name="availabilityStatus" required>
            <option value="Available">Available</option>
            <option value="Out of Stock">Out of Stock</option>
            <option value="Pre-Order">Pre-Order</option>
        </select>

        <label for="volume">Volume:</label>
        <input type="text" id="volume" name="volume" placeholder="Volume (e.g., 500ml, 1L)" required>

        <label for="imageURL">Image:</label>
        <input type="file" id="imageURL" name="imageURL">

        <label for="expirationDate">Expiration Date:</label>
        <input type="date" id="expirationDate" name="expirationDate">

        <input type="hidden" id="createdAt" name="createdAt" value="<?php echo date('Y-m-d'); ?>" /> <!-- Today's date -->
        
        <button type="submit">Add New Product</button>
    </form>
</div>



<script>
function closeModal() {
    document.getElementById('editModal').style.display = 'none';
}
</script>
<?php
if (isset($_POST['message'])) {
    $message = $_POST['message'];
    $logFile = fopen("debug_log.txt", "a");
    fwrite($logFile, date('Y-m-d H:i:s') . " - " . $message . "\n");
    fclose($logFile);
}
?>


<footer id="footer">
    &copy; 2024 Inventory Management System | All rights reserved
</footer>
</body>
<!------------------------Javascript----------------------------->
<script>
//-------Edit Form------------
//-------Edit Form------------
document.addEventListener('DOMContentLoaded', function () {
    // Handle edit button click event for each 'Edit' button
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function () {
            const itemID = this.getAttribute('data-id'); // Get the ItemID from data attribute

            // Fetch product details from server using the itemID
            fetch(`get_product_details.php?item_id=${itemID}`)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        console.error("Error from server:", data.error); // Log error from server
                        Swal.fire("Error", data.error, "error");
                    } else {
                        // Populate the edit form with fetched product data
                        populateEditForm(data);
                        openModal(); // Display the edit modal form
                    }
                })
                .catch(error => {
                    console.error("Error fetching product data:", error); // Log fetch errors
                    Swal.fire("Error", "Unable to load product details. Please try again.", "error");
                });
        });
    });

    // Handle form submission to update product details
    document.getElementById('editForm').addEventListener('submit', function (event) {
        event.preventDefault(); // Prevent default form submission

        const formData = new FormData(this); // Gather form data as FormData object
        const saveButton = document.getElementById('saveButton');
        saveButton.disabled = true; // Disable button to prevent duplicate submissions

        // Send form data to server via fetch API
        fetch(this.action, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.text(); // Adjust based on server's response type (text or JSON)
        })
        .then(data => {
            // Check for success in response and reload if successful
            if (data.includes('success')) {
                Swal.fire("Success", "Product updated successfully!", "success").then(() => {
                    closeModal(); // Close modal on success
                    location.reload(); // Reload page to reflect updated data
                });
            } else {
                throw new Error("Failed to update product.");
            }
        })
        .catch(error => {
            console.error("Error updating product:", error); // Log any errors for debugging
            Swal.fire("Error", "Something went wrong! Please try again.", "error");
        })
        .finally(() => {
            saveButton.disabled = false; // Re-enable the submit button
        });
    });

    // Display the edit modal
    function openModal() {
        document.getElementById('editModal').style.display = 'block';
    }

    // Close the edit modal and reset fields
    function closeModal() {
        document.getElementById('editModal').style.display = 'none';
        document.getElementById('editForm').reset(); // Clear all input fields
        document.getElementById('imagePreview').style.display = 'none'; // Hide image preview
    }

    // Populate edit form fields with provided product data
    function populateEditForm(data) {
        document.getElementById('editItemID').value = data.ItemID;
        document.getElementById('editProductName').value = data.productName;
        document.getElementById('editSKU').value = data.SKU;
        document.getElementById('editPrice').value = data.Price;
        document.getElementById('editCategory').value = data.Category;
        document.getElementById('editStockLevel').value = data.stockLevel;
        document.getElementById('editRestockLevel').value = data.restockLevel;
        document.getElementById('editAvailabilityStatus').value = data.availabilityStatus;
        document.getElementById('editVolume').value = data.volume;
        document.getElementById('editExpirationDate').value = data.expirationDate;

        // Display image preview if URL is available
        const imagePreview = document.getElementById('imagePreview');
        if (data.imageURL) {
            imagePreview.src = data.imageURL;
            imagePreview.style.display = 'block'; // Show image preview
        } else {
            imagePreview.style.display = 'none'; // Hide if no image
        }
    }

    // Preview image when a file is selected
    window.previewImage = function(event) {
        const imagePreview = document.getElementById('imagePreview');
        const file = event.target.files[0]; // Retrieve selected file

        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result; // Set preview source to file
                imagePreview.style.display = 'block'; // Show image preview
            };
            reader.readAsDataURL(file); // Convert file to data URL for preview
        }
    };

    // Attach the close modal function to handle outside clicks
    window.closeModal = closeModal;
});



 // ------------------------- delete button--------------------------

 document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('.delete-btn');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const itemId = this.getAttribute('data-id');

            // Show confirmation dialog
            Swal.fire({
                title: 'Are you sure?',
                text: "This is irreversible!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Proceed with the deletion
                    deleteItem(itemId);
                }
            });
        });
    });
});

// Function to handle deletion
function deleteItem(itemId) {
    // Send a POST request to delete the item
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'delete_product.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                Swal.fire(
                    'Deleted!',
                    'Your item has been deleted.',
                    'success'
                ).then(() => {
                    location.reload(); // Reload the page to see the changes
                });
            } else {
                Swal.fire(
                    'Error!',
                    response.message,
                    'error'
                );
            }
        }
    };
    xhr.send('itemId=' + encodeURIComponent(itemId));
}
//--------------Add Products ----------------
$(document).ready(function () {
    // Show popup when "Add Product" button is clicked
    $('#addProductBtn').click(function () {
        resetForm();
        $('#popupForm').fadeIn();
        $('#popupOverlay').fadeIn();
    });

    // Close popup when the overlay is clicked
    $('#popupOverlay').click(function () {
        $('#popupForm').fadeOut();
        $('#popupOverlay').fadeOut();
    });

    // Function to reset form fields
    function resetForm() {
        $('#productName').val('');
        $('#SKU').val('');
        $('#price').val('');
        $('#Category').val('Water');
        $('#stockLevel').val('');
        $('#restockLevel').val('');
        $('#availabilityStatus').val('Available');
        $('#volume').val('');
        $('#imageURL').val('');
        $('#expirationDate').val('');
        $('#createdAt').val(new Date().toISOString().split('T')[0]);
    }

    // Check URL parameters for success or error messages
    const urlParams = new URLSearchParams(window.location.search);
    const success = urlParams.get('success');
    const error = urlParams.get('error');

    if (success) {
        Swal.fire({
            icon: 'success',
            title: success,
            showConfirmButton: false,
            timer: 1500
        });
    } else if (error) {
        Swal.fire({
            icon: 'error',
            title: 'Failed to Add Product',
            text: error || 'Please try again.',
        });
    }
});

//----------Tool tip--------------
// JavaScript for tooltip functionality
document.addEventListener("DOMContentLoaded", function () {
    // Get all tooltip elements
    const tooltips = document.querySelectorAll('.tooltip');

    tooltips.forEach(tooltip => {
        tooltip.addEventListener('mouseenter', function () {
            const tooltipText = this.querySelector('.tooltiptext');
            tooltipText.style.visibility = 'visible'; // Show the tooltip
            tooltipText.style.opacity = '1'; // Fade in the tooltip
        });

        tooltip.addEventListener('mouseleave', function () {
            const tooltipText = this.querySelector('.tooltiptext');
            tooltipText.style.visibility = 'hidden'; // Hide the tooltip
            tooltipText.style.opacity = '0'; // Fade out the tooltip
        });
    });
});


//--------------------------------Filter Section---------------------
document.addEventListener('DOMContentLoaded', function() {
    const stockLevelInput = document.getElementById('stockLevel');
    const categorySelect = document.getElementById('category');
    const availabilitySelect = document.getElementById('availability');
    const minPriceInput = document.getElementById('minPrice'); // Min price input
    const expirationDateInput = document.getElementById('expirationDate');
    const searchInput = document.getElementById('search');
    const tableBody = document.querySelector('.table-container tbody');
    const feedbackMessage = document.getElementById('feedbackMessage'); // Feedback message element

    function filterProducts() {
        // Input validation
        const stockLevel = stockLevelInput.value ? parseInt(stockLevelInput.value) : 0;
        const minPrice = minPriceInput.value ? parseFloat(minPriceInput.value) : 0;

        // Validate inputs
        if (isNaN(stockLevel) || stockLevel < 0) {
            stockLevelInput.value = ''; // Reset invalid input
        }
        if (isNaN(minPrice) || minPrice < 0) {
            minPriceInput.value = ''; // Reset invalid input
        }

        const category = categorySelect.value;
        const availability = availabilitySelect.value;
        const expirationDate = new Date(expirationDateInput.value);
        const searchTerm = searchInput.value.toLowerCase();

        const rows = tableBody.querySelectorAll('tr');

        let found = false; // Track if any rows match

        rows.forEach(row => {
            const stockCell = row.cells[0].querySelector('.tooltip .stock-icon');
            const productIdCell = row.cells[1].textContent.toLowerCase();
            const productNameCell = row.cells[3].textContent.toLowerCase();
            const skuCell = row.cells[4].textContent.toLowerCase();
            const priceCell = parseFloat(row.cells[5].textContent.replace('R', '').replace(',', ''));
            const categoryCell = row.cells[6].textContent;
            const availabilityCell = row.cells[7].textContent;

            const matchesStock = stockCell ? (parseInt(stockCell.classList.contains('low-stock') ? 0 : 1) >= stockLevel) : true;
            const matchesCategory = category ? categoryCell === category : true;
            const matchesAvailability = availability ? availabilityCell === availability : true;
            const matchesPrice = priceCell >= minPrice; // Only check against min price
            const matchesExpiration = !expirationDateInput.value || new Date(row.cells[9].textContent) >= expirationDate;
            const matchesSearch = productIdCell.includes(searchTerm) || productNameCell.includes(searchTerm) || skuCell.includes(searchTerm);

            if (matchesStock && matchesCategory && matchesAvailability && matchesPrice && matchesExpiration && matchesSearch) {
                row.style.display = '';
                found = true;
            } else {
                row.style.display = 'none';
            }
        });

        // Update feedback message based on the found variable
        if (!found) {
            feedbackMessage.textContent = 'No results found.';
            feedbackMessage.style.display = 'block'; // Show message
        } else {
            feedbackMessage.style.display = 'none'; // Hide message if results found
        }
    }

    stockLevelInput.addEventListener('input', filterProducts);
    categorySelect.addEventListener('change', filterProducts);
    availabilitySelect.addEventListener('change', filterProducts);
    minPriceInput.addEventListener('input', filterProducts); // Event listener for min price
    expirationDateInput.addEventListener('input', filterProducts);
    searchInput.addEventListener('input', filterProducts);
});

//---------------------- View Form JS------------------------
// JavaScript to handle opening and closing the modal
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('viewProductModal');
    const closeBtn = document.querySelector('.close');
    const closeButton = document.querySelector('.close-button');

    // Function to open the modal and populate product data
    document.querySelectorAll('.view-btn').forEach(button => {
        button.addEventListener('click', function() {
            const productID = this.getAttribute('data-id');

            // Fetch product data using AJAX
            fetch(`get_product_details.php?item_id=${productID}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);  // Handle the error
                        return;
                    }

                    // Populate form fields with product data
                    document.getElementById('viewItemID').value = data.ItemID || '';
                    document.getElementById('viewProductName').value = data.productName || 'N/A';
                    document.getElementById('viewSKU').value = data.SKU || 'N/A';
                    document.getElementById('viewPrice').value = data.Price || 'N/A';
                    document.getElementById('viewCategory').value = data.Category || 'N/A';
                    document.getElementById('viewStockLevel').value = data.stockLevel || 'N/A';
                    document.getElementById('viewRestockLevel').value = data.restockLevel || 'N/A';
                    document.getElementById('viewAvailabilityStatus').value = data.availabilityStatus || 'N/A';
                    document.getElementById('viewVolume').value = data.volume || 'N/A';
                    document.getElementById('viewExpirationDate').value = data.expirationDate || 'N/A';

                    // Handle image, show placeholder if no image available
                    const imageURL = data.imageURL || 'placeholder.jpg'; // Replace with your placeholder image path
                    document.getElementById('viewImageURL').src = imageURL;

                    // Display the modal
                    modal.style.display = 'block';
                })
                .catch(error => {
                    console.error('Error fetching product data:', error);
                });
        });
    });

    // Close modal when clicking on the 'X' or 'Close' button
    closeBtn.addEventListener('click', function() {
        modal.style.display = 'none';
    });

  

    // Close modal when clicking outside of the modal
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
});

</script>


</html>