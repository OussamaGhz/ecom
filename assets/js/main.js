/**
 * Main JavaScript file for ShopEasy E-Commerce
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize dropdown menus
    initDropdowns();
    
    // Initialize form validation
    initFormValidation();
    
    // Initialize quantity inputs
    initQuantityInputs();
    
    // Add click event for add to cart buttons
    initAddToCartButtons();
});

/**
 * Initialize dropdown menus
 */
function initDropdowns() {
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.stopPropagation();
            
            // Find the dropdown menu
            const menu = this.nextElementSibling;
            
            // Toggle active class
            menu.classList.toggle('active');
            
            // Close other dropdowns
            document.querySelectorAll('.dropdown-menu.active').forEach(activeMenu => {
                if (activeMenu !== menu) {
                    activeMenu.classList.remove('active');
                }
            });
        });
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function() {
        document.querySelectorAll('.dropdown-menu.active').forEach(menu => {
            menu.classList.remove('active');
        });
    });
}

/**
 * Initialize form validation
 */
function initFormValidation() {
    const forms = document.querySelectorAll('form[data-validate="true"]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Check required fields
            const requiredFields = form.querySelectorAll('[required]');
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    showError(field, 'This field is required');
                } else {
                    clearError(field);
                }
            });
            
            // Check email format
            const emailFields = form.querySelectorAll('input[type="email"]');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            emailFields.forEach(field => {
                if (field.value.trim() && !emailRegex.test(field.value)) {
                    isValid = false;
                    showError(field, 'Please enter a valid email address');
                }
            });
            
            // Check password match (if applicable)
            const password = form.querySelector('input[name="password"]');
            const confirmPassword = form.querySelector('input[name="confirm_password"]');
            
            if (password && confirmPassword && password.value !== confirmPassword.value) {
                isValid = false;
                showError(confirmPassword, 'Passwords do not match');
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    });
}

/**
 * Initialize quantity inputs with +/- buttons
 */
function initQuantityInputs() {
    const quantityInputs = document.querySelectorAll('.quantity-input');
    
    quantityInputs.forEach(container => {
        const input = container.querySelector('input[type="number"]');
        const decBtn = container.querySelector('.quantity-decrease');
        const incBtn = container.querySelector('.quantity-increase');
        
        if (input && decBtn && incBtn) {
            decBtn.addEventListener('click', function() {
                if (input.value > parseInt(input.min || 1)) {
                    input.value = parseInt(input.value) - 1;
                    triggerChangeEvent(input);
                }
            });
            
            incBtn.addEventListener('click', function() {
                const max = parseInt(input.max || 9999);
                if (parseInt(input.value) < max) {
                    input.value = parseInt(input.value) + 1;
                    triggerChangeEvent(input);
                }
            });
        }
    });
}

/**
 * Initialize add to cart buttons with animation
 */
function initAddToCartButtons() {
    const addButtons = document.querySelectorAll('.add-to-cart');
    
    addButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Add animation class
            button.classList.add('adding');
            
            // Change text (optional)
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-check"></i> Added!';
            
            // Reset after animation
            setTimeout(() => {
                button.classList.remove('adding');
                setTimeout(() => {
                    button.innerHTML = originalText;
                }, 1000);
            }, 1200);
        });
    });
}

/**
 * Show validation error message
 */
function showError(field, message) {
    // Clear any existing error
    clearError(field);
    
    // Create error element
    const error = document.createElement('div');
    error.className = 'invalid-feedback';
    error.textContent = message;
    
    // Add error class to field
    field.classList.add('is-invalid');
    
    // Insert error after field
    field.parentNode.insertBefore(error, field.nextSibling);
}

/**
 * Clear validation error
 */
function clearError(field) {
    field.classList.remove('is-invalid');
    
    // Remove any existing error message
    const container = field.parentNode;
    const error = container.querySelector('.invalid-feedback');
    if (error) {
        container.removeChild(error);
    }
}

/**
 * Trigger change event for input
 */
function triggerChangeEvent(element) {
    const event = new Event('change', { bubbles: true });
    element.dispatchEvent(event);
}

/**
 * Show notification
 * @param {string} message - The notification message
 * @param {string} type - The notification type (success, error, warning, info)
 * @param {number} duration - How long to show the notification (ms)
 */
function showNotification(message, type = 'success', duration = 3000) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    
    // Create content
    const content = document.createElement('div');
    content.className = 'notification-content';
    
    // Add icon based on type
    let icon = 'info-circle';
    if (type === 'success') icon = 'check-circle';
    if (type === 'error') icon = 'exclamation-circle';
    if (type === 'warning') icon = 'exclamation-triangle';
    
    content.innerHTML = `
        <i class="fas fa-${icon}"></i>
        <p>${message}</p>
    `;
    
    // Add close button
    const closeBtn = document.createElement('button');
    closeBtn.innerHTML = '<i class="fas fa-times"></i>';
    closeBtn.className = 'notification-close';
    closeBtn.addEventListener('click', () => {
        notification.classList.add('hiding');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    });
    
    // Assemble notification
    notification.appendChild(content);
    notification.appendChild(closeBtn);
    
    // Add to container or create one
    let container = document.querySelector('.notification-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'notification-container';
        document.body.appendChild(container);
    }
    
    // Add to DOM
    container.appendChild(notification);
    
    // Show with animation
    setTimeout(() => {
        notification.classList.add('visible');
    }, 10);
    
    // Auto-close after duration
    if (duration) {
        setTimeout(() => {
            if (notification.parentNode) {
                notification.classList.add('hiding');
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }
        }, duration);
    }
}