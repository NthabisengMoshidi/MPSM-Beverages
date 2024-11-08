// Create Expense vs Profit Chart
const ctx = document.getElementById('expenseProfitChart').getContext('2d');
const expenseProfitChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Dec', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [
            {
                label: 'Expenses',
                data: [30000, 40000, 35000, 45000, 50000, 55000, 60000],
                borderColor: 'red',
                fill: false
            },
            {
                label: 'Profit',
                data: [10000, 15000, 20000, 25000, 30000, 35000, 40000],
                borderColor: 'green',
                fill: false
            }
        ]
    },
    options: {
        responsive: true,
        scales: {
            x: {
                title: {
                    display: true,
                    text: 'Months'
                }
            },
            y: {
                title: {
                    display: true,
                    text: 'Amount (in Rands)'
                }
            }
        }
    }
});

// Sample notifications for demonstration
const notifications = [
    { text: "Product X is running low in stock.", date: "2024-09-30" },
    { text: "New orders have been placed.", date: "2024-09-29" },
    { text: "Monthly inventory analysis is due.", date: "2024-09-28" },
];

function displayNotifications(filteredNotifications) {
    const notificationList = document.getElementById('notificationList');
    notificationList.innerHTML = '';

    filteredNotifications.forEach(notification => {
        const item = document.createElement('div');
        item.classList.add('notification-item');
        item.innerHTML = `<input type="checkbox" class="notification-checkbox">
                          <span>${notification.text}</span>
                          <span class="notification-date">${notification.date}</span>`;
        notificationList.appendChild(item);
    });
}

function filterNotifications() {
    const searchValue = document.getElementById('notificationSearch').value.toLowerCase();
    const filtered = notifications.filter(notification =>
        notification.text.toLowerCase().includes(searchValue)
    );
    displayNotifications(filtered);
}

// Initialize the notification display on page load
displayNotifications(notifications);

function deleteSelected() {
    const checkboxes = document.querySelectorAll('.notification-checkbox:checked');
    checkboxes.forEach(checkbox => {
        checkbox.closest('.notification-item').remove();
    });
}

function openEditModal(product) {
    document.getElementById('editProductId').value = product.productId;
    document.getElementById('editProductName').value = product.productName;
    document.getElementById('editSKU').value = product.SKU;
    document.getElementById('editPrice').value = product.Price;
    document.getElementById('editCategory').value = product.Category;
    document.getElementById('editAvailabilityStatus').value = product.availabilityStatus;
    document.getElementById('editVolume').value = product.volume;
    document.getElementById('editExpirationDate').value = product.expirationDate || '';
    
    document.getElementById('editModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('editModal').style.display = 'none';
}

// Close the modal when clicking outside of it
window.onclick = function(event) {
    const modal = document.getElementById('editModal');
    if (event.target == modal) {
        closeModal();
    }
};
 // Show footer when scrolled to the bottom
 window.onscroll = function() {
    var footer = document.getElementById('footer');
    if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight) {
        footer.classList.add('footer-visible');
    } else {
        footer.classList.remove('footer-visible');
    }
};