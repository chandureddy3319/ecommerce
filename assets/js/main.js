// Main JavaScript for E-commerce Store

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initCart();
    initSearch();
    initFilters();
    initNotifications();
});

// Cart Functionality
function initCart() {
    // Add to cart buttons
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.dataset.productId;
            addToCart(productId);
        });
    });

    // Remove from cart buttons
    document.querySelectorAll('.remove-cart-item').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const cartId = this.dataset.cartId;
            removeFromCart(cartId);
        });
    });

    // Update cart quantity
    document.querySelectorAll('.update-cart-quantity').forEach(input => {
        input.addEventListener('change', function() {
            const cartId = this.dataset.cartId;
            const quantity = this.value;
            updateCartQuantity(cartId, quantity);
        });
    });
}

function addToCart(productId) {
    if (!isLoggedIn()) {
        showNotification('Please login to add items to cart', 'warning');
        return;
    }

    const button = document.querySelector(`[data-product-id="${productId}"]`);
    const originalText = button.innerHTML;
    
    // Show loading state
    button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Adding...';
    button.disabled = true;

    fetch('ajax/cart_actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=add&product_id=${productId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Product added to cart successfully!', 'success');
            updateCartCount(data.cart_count);
            updateCartDropdown();
        } else {
            showNotification(data.message || 'Error adding product to cart', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error adding product to cart', 'error');
    })
    .finally(() => {
        // Restore button state
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function removeFromCart(cartId) {
    fetch('ajax/cart_actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=remove&cart_id=${cartId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Item removed from cart', 'success');
            updateCartCount(data.cart_count);
            updateCartDropdown();
            // Remove item from DOM
            const item = document.querySelector(`[data-cart-id="${cartId}"]`).closest('.dropdown-item');
            if (item) item.remove();
        } else {
            showNotification(data.message || 'Error removing item', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error removing item from cart', 'error');
    });
}

function updateCartQuantity(cartId, quantity) {
    fetch('ajax/cart_actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=update&cart_id=${cartId}&quantity=${quantity}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartTotal(data.cart_total);
            updateCartCount(data.cart_count);
        } else {
            showNotification(data.message || 'Error updating quantity', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error updating quantity', 'error');
    });
}

function updateCartCount(count) {
    const cartCountElements = document.querySelectorAll('.cart-count');
    cartCountElements.forEach(element => {
        element.textContent = count;
    });
}

function updateCartTotal(total) {
    const cartTotalElements = document.querySelectorAll('.cart-total');
    cartTotalElements.forEach(element => {
        element.textContent = `$${parseFloat(total).toFixed(2)}`;
    });
}

function updateCartDropdown() {
    // This would typically reload the cart dropdown content via AJAX
    // For now, we'll just show a notification
    showNotification('Cart updated', 'info');
}

// Search Functionality
function initSearch() {
    const searchForm = document.getElementById('search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const searchInput = document.getElementById('search-input');
            const query = searchInput.value.trim();
            
            if (query) {
                window.location.href = `products.php?search=${encodeURIComponent(query)}`;
            }
        });
    }

    // Live search (optional)
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length >= 3) {
                searchTimeout = setTimeout(() => {
                    performLiveSearch(query);
                }, 500);
            }
        });
    }
}

function performLiveSearch(query) {
    fetch(`ajax/search.php?q=${encodeURIComponent(query)}`)
    .then(response => response.json())
    .then(data => {
        // Update search results dropdown
        updateSearchResults(data.results);
    })
    .catch(error => {
        console.error('Search error:', error);
    });
}

function updateSearchResults(results) {
    const resultsContainer = document.getElementById('search-results');
    if (!resultsContainer) return;

    resultsContainer.innerHTML = '';
    
    if (results.length === 0) {
        resultsContainer.innerHTML = '<div class="p-3 text-muted">No products found</div>';
        return;
    }

    results.forEach(product => {
        const item = document.createElement('div');
        item.className = 'dropdown-item d-flex align-items-center';
        item.innerHTML = `
            <img src="${product.image}" alt="${product.name}" class="me-2" style="width: 30px; height: 30px; object-fit: cover;">
            <div>
                <div class="fw-bold">${product.name}</div>
                <div class="text-muted">$${product.price}</div>
            </div>
        `;
        item.addEventListener('click', () => {
            window.location.href = `product.php?id=${product.id}`;
        });
        resultsContainer.appendChild(item);
    });
}

// Filter Functionality
function initFilters() {
    const filterForm = document.getElementById('filter-form');
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            applyFilters();
        });
    }

    // Price range slider
    const priceRange = document.getElementById('price-range');
    if (priceRange) {
        const priceDisplay = document.getElementById('price-display');
        priceRange.addEventListener('input', function() {
            priceDisplay.textContent = `$${this.value}`;
        });
    }
}

function applyFilters() {
    const form = document.getElementById('filter-form');
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    
    window.location.href = `products.php?${params.toString()}`;
}

// Notification System
function initNotifications() {
    // Create notification container if it doesn't exist
    if (!document.getElementById('notification-container')) {
        const container = document.createElement('div');
        container.id = 'notification-container';
        container.className = 'toast-container';
        document.body.appendChild(container);
    }
}

function showNotification(message, type = 'info', duration = 5000) {
    const container = document.getElementById('notification-container');
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;
    
    container.appendChild(toast);
    
    const bsToast = new bootstrap.Toast(toast, {
        autohide: true,
        delay: duration
    });
    
    bsToast.show();
    
    // Remove toast from DOM after it's hidden
    toast.addEventListener('hidden.bs.toast', () => {
        container.removeChild(toast);
    });
}

// Utility Functions
function isLoggedIn() {
    // Check if user is logged in (you might want to check a session variable)
    return document.querySelector('.navbar-nav .dropdown') !== null;
}

function formatPrice(price) {
    return `$${parseFloat(price).toFixed(2)}`;
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Form Validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;

    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    let isValid = true;

    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
        }
    });

    // Email validation
    const emailInputs = form.querySelectorAll('input[type="email"]');
    emailInputs.forEach(input => {
        if (input.value && !isValidEmail(input.value)) {
            input.classList.add('is-invalid');
            isValid = false;
        }
    });

    return isValid;
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Image Preview
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById(previewId).src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Confirm Delete
function confirmDelete(message = 'Are you sure you want to delete this item?') {
    return confirm(message);
}

// Loading States
function setLoadingState(button, isLoading) {
    if (isLoading) {
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';
    } else {
        button.disabled = false;
        button.innerHTML = button.dataset.originalText || 'Submit';
    }
}

// Initialize loading states
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('button[type="submit"]').forEach(button => {
        button.dataset.originalText = button.innerHTML;
    });
}); 