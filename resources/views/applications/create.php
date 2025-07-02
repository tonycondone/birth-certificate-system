
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container-fluid">
                <a class="navbar-brand" href="/">Birth Certificate System</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link" href="/">
                                <i class="fas fa-home me-1"></i>Home
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <?php
$pageTitle = 'New Application - Digital Birth Certificate System';
include __DIR__ . '/../layouts/base.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-plus-circle me-2"></i>
                        New Birth Certificate Application
                    </h4>
                </div>
                <div class="card-body">
                    <form id="applicationForm" method="POST" action="/applications/submit" enctype="multipart/form-data">
                        <!-- CSRF Token -->
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="child_name" class="form-label">Child's Full Name *</label>
                                    <input type="text" class="form-control" id="child_name" name="child_name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date_of_birth" class="form-label">Date of Birth *</label>
                                    <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="place_of_birth" class="form-label">Place of Birth *</label>
                                    <input type="text" class="form-control" id="place_of_birth" name="place_of_birth" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="gender" class="form-label">Gender *</label>
                                    <select class="form-select" id="gender" name="gender" required>
                                        <option value="">Select Gender</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="documents" class="form-label">Supporting Documents</label>
                            <input type="file" class="form-control" id="documents" name="documents[]" multiple accept=".jpg,.jpeg,.png,.pdf">
                            <div class="form-text">Upload birth records, hospital documents, or other supporting files (JPG, PNG, PDF)</div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Additional Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Any additional information or special circumstances..."></textarea>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="/applications" class="btn btn-secondary me-md-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i>
                                Submit Application
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('applicationForm');
    const submitBtn = form.querySelector('button[type="submit"]');
    let submitBtnText = submitBtn.querySelector('span') || submitBtn;
    let submitBtnSpinner = submitBtn.querySelector('.spinner-border');
    if (!submitBtnSpinner) {
        submitBtnSpinner = document.createElement('span');
        submitBtnSpinner.className = 'spinner-border spinner-border-sm ms-2';
        submitBtnSpinner.style.display = 'none';
        submitBtn.appendChild(submitBtnSpinner);
    }
    function resetButtonState() {
        submitBtn.disabled = false;
        if (submitBtnText) submitBtnText.textContent = 'Submit Application';
        submitBtnSpinner.style.display = 'none';
    }
    function setLoadingState() {
        submitBtn.disabled = true;
        if (submitBtnText) submitBtnText.textContent = 'Submitting...';
        submitBtnSpinner.style.display = 'inline-block';
    }
    function validateFormAndUpdateButton() {
        if (form.checkValidity()) {
            resetButtonState();
        }
    }
    const inputs = form.querySelectorAll('input[required], input[pattern], select[required], textarea[required]');
    inputs.forEach(input => {
        input.addEventListener('input', validateFormAndUpdateButton);
        input.addEventListener('blur', validateFormAndUpdateButton);
    });
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        if (!form.checkValidity()) {
            resetButtonState();
            form.classList.add('was-validated');
            return;
        }
        setLoadingState();
        const formData = new FormData(form);
        fetch('/applications/submit', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            resetButtonState();
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message,
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = '/applications';
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: data.error || 'An error occurred while submitting the application.',
                    confirmButtonText: 'OK'
                });
            }
        })
        .catch(error => {
            resetButtonState();
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'An error occurred while submitting the application.',
                confirmButtonText: 'OK'
            });
        });
    });
});
</script> 