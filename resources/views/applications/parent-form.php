
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
$pageTitle = 'New Birth Certificate Application';
require_once __DIR__ . '/../layouts/base.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Birth Certificate Application</h4>
                </div>
                <div class="card-body">
                    <form id="birthApplicationForm" method="POST" action="/applications/submit" class="needs-validation" novalidate enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                        
                        <!-- Child Information -->
                        <h5 class="mb-4">Child's Information</h5>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="childFirstName" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="childFirstName" name="child_first_name" required>
                                <div class="invalid-feedback">Please enter child's first name.</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="childMiddleName" class="form-label">Middle Name (Optional)</label>
                                <input type="text" class="form-control" id="childMiddleName" name="child_middle_name">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="childLastName" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="childLastName" name="child_last_name" required>
                                <div class="invalid-feedback">Please enter child's last name.</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="dateOfBirth" class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" id="dateOfBirth" name="date_of_birth" required>
                                <div class="invalid-feedback">Please select date of birth.</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="timeOfBirth" class="form-label">Time of Birth</label>
                                <input type="time" class="form-control" id="timeOfBirth" name="time_of_birth" required>
                                <div class="invalid-feedback">Please enter time of birth.</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="gender" class="form-label">Gender</label>
                                <select class="form-select" id="gender" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                </select>
                                <div class="invalid-feedback">Please select gender.</div>
                            </div>
                        </div>

                        <!-- Place of Birth -->
                        <h5 class="mb-4 mt-4">Place of Birth</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="hospital" class="form-label">Hospital/Institution</label>
                                <input type="text" class="form-control" id="hospital" name="hospital" required>
                                <div class="invalid-feedback">Please enter hospital name.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="hospitalRegistrationNumber" class="form-label">Hospital Registration Number</label>
                                <input type="text" class="form-control" id="hospitalRegistrationNumber" name="hospital_registration_number" required>
                                <div class="invalid-feedback">Please enter hospital registration number.</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city" required>
                                <div class="invalid-feedback">Please enter city.</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="state" class="form-label">State/Province</label>
                                <input type="text" class="form-control" id="state" name="state" required>
                                <div class="invalid-feedback">Please enter state/province.</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="country" class="form-label">Country</label>
                                <input type="text" class="form-control" id="country" name="country" required>
                                <div class="invalid-feedback">Please enter country.</div>
                            </div>
                        </div>

                        <!-- Supporting Documents -->
                        <h5 class="mb-4 mt-4">Supporting Documents</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="hospitalLetter" class="form-label">Hospital Birth Letter</label>
                                <input type="file" class="form-control" id="hospitalLetter" name="hospital_letter" 
                                       accept=".pdf,.jpg,.jpeg,.png" required>
                                <div class="form-text">Upload PDF, JPG, or PNG (max 5MB)</div>
                                <div class="invalid-feedback">Please upload hospital birth letter.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="parentId" class="form-label">Parent's ID</label>
                                <input type="file" class="form-control" id="parentId" name="parent_id" 
                                       accept=".pdf,.jpg,.jpeg,.png" required>
                                <div class="form-text">Upload PDF, JPG, or PNG (max 5MB)</div>
                                <div class="invalid-feedback">Please upload parent's ID.</div>
                            </div>
                        </div>

                        <!-- Declaration -->
                        <div class="mb-4 mt-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="declaration" required>
                                <label class="form-check-label" for="declaration">
                                    I declare that all the information provided is true and correct to the best of my knowledge.
                                </label>
                                <div class="invalid-feedback">
                                    You must agree before submitting.
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">Submit Application</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const form = document.getElementById('birthApplicationForm');
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
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
            resetButtonState();
        } else {
            setLoadingState();
        }
        // File size validation
        const maxSize = 5 * 1024 * 1024; // 5MB
        const files = [
            document.getElementById('hospitalLetter').files[0],
            document.getElementById('parentId').files[0]
        ];
        for (const file of files) {
            if (file && file.size > maxSize) {
                event.preventDefault();
                alert('File size must not exceed 5MB');
                resetButtonState();
                return;
            }
        }
        form.classList.add('was-validated');
    });
    // Date validation
    const dateOfBirth = document.getElementById('dateOfBirth');
    dateOfBirth.max = new Date().toISOString().split('T')[0];
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>