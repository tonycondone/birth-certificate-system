// Main JavaScript file for Birth Certificate System - Enhanced Functionality

// Import dependencies
import 'bootstrap';
import '@fortawesome/fontawesome-free/js/all.js'; // Use JS for better tree-shaking if needed
import Swal from 'sweetalert2'; // Import SweetAlert2 directly

// Global utility functions

/**
 * Displays a beautiful and informative alert using SweetAlert2.
 * @param {string} message - The message to display.
 * @param {string} type - The type of alert (success, error, warning, info, question).
 * @param {string} title - Optional title for the alert.
 */
window.showAlert = function(message, type = 'success', title = '') {
    let icon = type;
    if (type === 'error') icon = 'error';
    if (type === 'success') icon = 'success';
    if (type === 'warning') icon = 'warning';
    if (type === 'info') icon = 'info';
    if (type === 'question') icon = 'question';

    Swal.fire({
        title: title || (type.charAt(0).toUpperCase() + type.slice(1) + '!'), // Capitalize type for title
        text: message,
        icon: icon,
        confirmButtonText: 'OK',
        buttonsStyling: false,
        customClass: {
            confirmButton: 'btn btn-primary' // Apply Bootstrap button style
        }
    });
};

/**
 * Client-side form validation with visual feedback.
 * @param {string} formId - The ID of the form to validate.
 * @returns {boolean} - True if the form is valid, false otherwise.
 */
window.validateForm = function(formId) {
    const form = document.getElementById(formId);
    if (!form) {
        console.warn(`Form with ID '${formId}' not found.`);
        return true; // Allow submission if form not found
    }

    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    let isValid = true;

    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
        }

        // Add event listener to remove validation feedback on input
        input.addEventListener('input', function() {
            if (this.value.trim()) {
                this.classList.remove('is-invalid');
            }
        }, { once: true }); // Only run once until invalid again
    });

    if (!isValid) {
        showAlert('Please fill in all required fields.', 'error', 'Validation Error');
    }

    return isValid;
};

/**
 * Shows a global loading indicator.
 */
window.showLoading = function() {
    const loadingOverlay = document.createElement('div');
    loadingOverlay.id = 'loading-overlay';
    loadingOverlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        opacity: 0;
        transition: opacity 0.3s ease-in-out;
    `;
    loadingOverlay.innerHTML = `
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    `;
    document.body.appendChild(loadingOverlay);
    setTimeout(() => loadingOverlay.style.opacity = '1', 10);
};

/**
 * Hides the global loading indicator.
 */
window.hideLoading = function() {
    const loadingOverlay = document.getElementById('loading-overlay');
    if (loadingOverlay) {
        loadingOverlay.style.opacity = '0';
        loadingOverlay.addEventListener('transitionend', () => loadingOverlay.remove(), { once: true });
    }
};

// QR Code scanner initialization
window.initQRScanner = function() {
    if (typeof QrScanner !== 'undefined') {
        const videoElement = document.getElementById('qr-video');
        const qrResultInput = document.getElementById('qr-result');
        const startButton = document.getElementById('start-scan');
        const scannerContainer = document.querySelector('.qr-scanner-container');

        if (videoElement && qrResultInput && startButton && scannerContainer) {
            let qrScanner = null;

            const startScanner = () => {
                if (qrScanner) {
                    qrScanner.destroy(); // Clean up previous instance if any
                }
                qrScanner = new QrScanner(
                    videoElement,
                    result => {
                        console.log('QR Code detected:', result.data);
                        qrResultInput.value = result.data;
                        showAlert('QR Code Scanned Successfully!', 'success');
                        qrScanner.stop();
                        scannerContainer.classList.remove('scanning');
                    },
                    { returnDetailedScanResult: true }
                );
                qrScanner.start().then(() => {
                    scannerContainer.classList.add('scanning');
                    showAlert('QR Scanner Started. Point your camera at a QR code.', 'info');
                }).catch(err => {
                    console.error('Failed to start QR scanner:', err);
                    showAlert('Could not start QR scanner. Please ensure camera access is granted.', 'error');
                });
            };

            startButton.addEventListener('click', startScanner);

            // Optional: Stop scanner when navigating away or closing modal
            // You might need to add more specific event listeners based on your UI flow
            window.addEventListener('beforeunload', () => {
                if (qrScanner) qrScanner.destroy();
            });

        } else {
            console.warn("QR Scanner elements not found. Skipping initialization.");
        }
    }
};

// Document ready - Main entry point for DOM manipulation
document.addEventListener('DOMContentLoaded', function() {
    // Initialize QR scanner if elements are present
    initQRScanner();

    // Initialize Bootstrap tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize Bootstrap popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Smooth scrolling for internal anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();

            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });

    // Example of using showLoading/hideLoading for AJAX calls
    // This is a conceptual example. You'd integrate this with your actual AJAX logic.
    /*
    document.getElementById('some-form-submit-button').addEventListener('click', function() {
        showLoading();
        // Simulate an AJAX call
        setTimeout(() => {
            hideLoading();
            showAlert('Data submitted successfully!', 'success');
        }, 2000);
    });
    */

    // Apply initial animations to elements (e.g., hero section content)
    const animatedElements = document.querySelectorAll('.animate-on-load');
    animatedElements.forEach(el => {
        el.classList.add('fadeInUp'); // Assuming fadeInUp is defined in CSS
    });
});