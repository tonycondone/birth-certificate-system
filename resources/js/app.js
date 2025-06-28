// Main JavaScript file for Birth Certificate System

// Import dependencies
import 'bootstrap';
import '@fortawesome/fontawesome-free/css/all.css';
import 'sweetalert2/dist/sweetalert2.min.css';

// Initialize SweetAlert2
window.Swal = require('sweetalert2');

// Global functions
window.showAlert = function(message, type = 'success') {
    Swal.fire({
        title: type === 'success' ? 'Success!' : 'Error!',
        text: message,
        icon: type,
        confirmButtonText: 'OK'
    });
};

// Form validation
window.validateForm = function(formId) {
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
    
    return isValid;
};

// QR Code scanner initialization
window.initQRScanner = function() {
    if (typeof QrScanner !== 'undefined') {
        const videoElement = document.getElementById('qr-video');
        if (videoElement) {
            const qrScanner = new QrScanner(
                videoElement,
                result => {
                    console.log('QR Code detected:', result.data);
                    // Handle QR code result
                    document.getElementById('qr-result').value = result.data;
                    qrScanner.stop();
                },
                { returnDetailedScanResult: true }
            );
            
            // Start scanning when button is clicked
            const startButton = document.getElementById('start-scan');
            if (startButton) {
                startButton.addEventListener('click', () => qrScanner.start());
            }
        }
    }
};

// Document ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize QR scanner if on verification page
    if (document.getElementById('qr-video')) {
        initQRScanner();
    }
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
}); 