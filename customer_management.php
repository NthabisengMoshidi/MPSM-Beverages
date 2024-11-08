<?php 
session_start(); // Start the session

include 'blueprint.php';

// Fetch customers from the users table
$query = "SELECT id, first_name, last_name, email, phone, home_address, created_at, role FROM users WHERE role = 'customer'";
$result = $conn->query($query);

// Fetch analytics data
$total_customers_query = "SELECT COUNT(*) as total FROM users WHERE role = 'customer'";
$total_customers_result = $conn->query($total_customers_query);
$total_customers = $total_customers_result->fetch_assoc()['total'];

$active_customers_query = "SELECT COUNT(*) as active FROM users WHERE role = 'customer' AND is_active = TRUE";
$active_customers_result = $conn->query($active_customers_query);
$active_customers = $active_customers_result->fetch_assoc()['active'];
?> 

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Management</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* General Styling */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        /* Navbar Styling */
        .navbar {
            background-color: #333;
            color: white;
            padding: 15px;
            text-align: center;
        }
        .navbar a {
            color: white;
            text-decoration: none;
            padding: 14px 20px;
            display: inline-block;
        }
        .navbar a:hover {
            background-color: #575757;
        }
        /* Container Styling */
        .container {
            padding: 20px;
            max-width: 1000px;
            margin: auto;
            background-color: white;
           box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            margin-top: 60px;
            margin-left: 350px;
        }
        /* Table Styling */
        table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        /* Action Icon Styling */
        .action-icon {
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        .action-icon:hover {
            transform: scale(1.2);
            color: #007bff;
        }
        /* Modal Styling */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            width: 80%;
            max-width: 500px;
        }
        .close-btn {
            float: right;
            font-size: 20px;
            cursor: pointer;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }
        .modal-actions {
            text-align: right;
        }
        .btn {
            padding: 8px 12px;
            cursor: pointer;
            margin-left: 5px;
        }
        .btn-close {
            background-color: #f44336;
            color: white;
            border: none;
        }
        .btn-save {
            background-color: #4CAF50;
            color: white;
            border: none;
        }

        .search-bar {
    width: 100%;
    display: flex;
    justify-content: flex-end; /* Aligns the search bar to the right */
    margin-bottom: 20px; /* Space between search bar and table */
    padding: 0; /* Ensure no padding around the search bar */
    border: none; /* Ensure no border on the search bar */
}

#searchInput {
    padding: 10px;
    width: 250px; /* Width of the search bar */
    border: none; /* Remove borders */
    border-radius: 5px; /* Rounded corners */
    font-size: 16px; /* Font size */
    background-color: #f8f9fa; /* Light background color */
    transition: box-shadow 0.3s; /* Smooth transition for shadow */
}

#searchInput:focus {
    outline: none; /* Remove default outline */
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.5); /* Add shadow on focus */
}

.search-bar::after {
    content: "🔍"; /* Search icon */
    margin-left: -30px; /* Position icon inside the input field */
    font-size: 18px; /* Icon size */
    cursor: pointer; /* Pointer cursor on hover */
    pointer-events: none; /* Prevents clicking on the icon */
}


  /* Analytics Section */
  .analytics {
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;
        padding: 10px;
        background-color: #e9ecef;
        border-radius: 5px;
    }
    .analytics div {
        text-align: center;
        flex: 1;
        padding: 10px;
    }
    .analytics div h3 {
        margin: 0;
        font-size: 24px;
    }
    .analytics div p {
        margin: 5px 0;
    }
    .action-icon {
        cursor: pointer;
        transition: transform 0.2s ease;
    }
    .action-icon:hover {
        transform: scale(1.2);
        color: #007bff;
    }
    </style>
</head>

<body>



    <div class="container">
        <h2>Customer Management</h2>

  <!-- Customer Analytics Section -->
  <div class="analytics">
            <div>
                <h3><?php echo $total_customers; ?></h3>
                <p>Total Customers</p>
                <i class="fas fa-users action-icon" title="Total Customers"></i>
            </div>
            <div>
                <h3><?php echo $active_customers; ?></h3>
                <p>Active Customers</p>
                <i class="fas fa-user-check action-icon" title="Active Customers"></i>
            </div>
            <div>
                <h3>75%</h3>
                <p>Customer Satisfaction</p>
                <i class="fas fa-smile action-icon" title="Customer Satisfaction"></i>
            </div>
        </div>     

<div class="search-bar">
    <input type="text" id="searchInput" placeholder="Search for customers..." onkeyup="searchCustomers()">
</div>
        <table>
            
            <thead>
                <tr>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Home Address</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr data-customer='<?php echo json_encode($row); ?>'>
                            <td data-label="First Name"><?php echo htmlspecialchars($row['first_name']); ?></td>
                            <td data-label="Last Name"><?php echo htmlspecialchars($row['last_name']); ?></td>
                            <td data-label="Email"><?php echo htmlspecialchars($row['email']); ?></td>
                            <td data-label="Phone"><?php echo htmlspecialchars($row['phone']); ?></td>
                            <td data-label="Home Address"><?php echo htmlspecialchars($row['home_address']); ?></td>
                            <td data-label="Created At"><?php echo htmlspecialchars($row['created_at']); ?></td>
                            <td data-label="Actions">
                                <i class="fas fa-eye action-icon" title="View Customer" onclick="viewCustomer(this)"></i>
                                <i class="fas fa-edit action-icon" title="Edit Customer" onclick="editCustomer(this)"></i>
                                <i class="fas fa-trash action-icon" title="Delete Customer" onclick="deleteCustomer(<?php echo $row['id']; ?>)"></i>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">No customers found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal for View/Edit Customer -->
    <div id="customerModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal()">&times;</span>
            <h3 id="modalTitle"></h3>
            <form id="customerForm" onsubmit="handleSubmit(event)">
                <input type="hidden" name="id" id="customerId">
                <div class="form-group">
                    <label for="firstName">First Name</label>
                    <input type="text" name="first_name" id="firstName" required pattern="[A-Za-z\s]+">
                </div>
                <div class="form-group">
                    <label for="lastName">Last Name</label>
                    <input type="text" name="last_name" id="lastName" required pattern="[A-Za-z\s]+">
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="text" name="phone" id="phone" required pattern="\d{10,15}">
                </div>
                <div class="form-group">
                    <label for="homeAddress">Home Address</label>
                    <input type="text" name="home_address" id="homeAddress" required>
                </div>
                <div class="form-group">
                <!-- Add the City field here -->
<label for="city">City:</label>
<select id="city" name="city" required>
    <option value="">Select a city</option>
    <option value="Johannesburg">Johannesburg</option>
    <option value="Pretoria">Pretoria</option>
    <option value="Ekurhuleni">Ekurhuleni</option>
    <option value="Tshwane">Tshwane</option>
    <option value="Midrand">Midrand</option>
    <option value="Soweto">Soweto</option>
    <option value="Centurion">Centurion</option>
    <option value="Benoni">Benoni</option>
    <option value="Brakpan">Brakpan</option>
    <option value="Randburg">Randburg</option>
    <option value="Roodepoort">Roodepoort</option>
    <option value="Other">Other</option> <!-- Fixed duplicate value -->
    <!-- Add more cities as needed -->
</select>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-close" onclick="closeModal()">Close</button>
                    <button type="submit" class="btn btn-save">Save</button>
                </div>
            </form>
        </div>
    </div>

<script>

//Searching
function searchCustomers() {
    // Get the search input and convert it to lower case
    let input = document.getElementById('searchInput');
    let filter = input.value.toLowerCase();
    
    // Get the table and rows
    let table = document.querySelector('table tbody');
    let rows = table.getElementsByTagName('tr');

    // Loop through all rows, and hide those that don't match the search query
    for (let i = 0; i < rows.length; i++) {
        let row = rows[i];
        let cells = row.getElementsByTagName('td');
        let found = false;

        // Check each cell in the row for a match
        for (let j = 0; j < cells.length; j++) {
            let cell = cells[j];
            if (cell.textContent.toLowerCase().indexOf(filter) > -1) {
                found = true;
                break; // Stop checking after the first match
            }
        }

        // Show or hide the row based on the search
        row.style.display = found ? "" : "none";
    }
}




        function viewCustomer(element) {
            const row = element.closest('tr');
            const customerData = JSON.parse(row.dataset.customer);
            Swal.fire({
                title: `${customerData.first_name} ${customerData.last_name}`,
                html: `<p>Email: ${customerData.email}</p>
                       <p>Phone: ${customerData.phone}</p>
                       <p>Home Address: ${customerData.home_address}</p>
                       <p>Created At: ${customerData.created_at}</p>`,
                confirmButtonText: 'Close'
            });
        }

        function editCustomer(element) {
            const row = element.closest('tr');
            const customerData = JSON.parse(row.dataset.customer);
            document.getElementById('customerId').value = customerData.id;
            document.getElementById('firstName').value = customerData.first_name;
            document.getElementById('lastName').value = customerData.last_name;
            document.getElementById('email').value = customerData.email;
            document.getElementById('phone').value = customerData.phone;
            document.getElementById('homeAddress').value = customerData.home_address;
            document.getElementById('city').value = customerData.city || ''; // Use the correct ID here
            document.getElementById('modalTitle').textContent = 'Edit Customer';
            document.getElementById('customerModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('customerModal').style.display = 'none';
            document.getElementById('customerForm').reset();
        }

        function deleteCustomer(id) {
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
                    fetch('delete_customer.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ id: id })
                    })
                    .then(response => {
                        if (response.ok) {
                            Swal.fire('Deleted!', 'The customer has been deleted.', 'success')
                                .then(() => location.reload());
                        } else {
                            Swal.fire('Error!', 'Failed to delete customer.', 'error');
                        }
                    })
                    .catch(err => console.error('Error:', err));
                }
            });
        }

        function handleSubmit(event) {
            event.preventDefault();
            const formData = new FormData(document.getElementById('customerForm'));
            const action = document.getElementById('customerId').value ? 'edit_customer.php' : 'add_customer.php';
            
            fetch(action, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (response.ok) {
                    Swal.fire('Success!', 'Customer details saved successfully.', 'success')
                        .then(() => {
                            location.reload();
                        });
                } else {
                    Swal.fire('Error!', 'Failed to save customer details.', 'error');
                }
            })
            .catch(err => console.error('Error:', err));
        }
    </script>
</body>
</html>
