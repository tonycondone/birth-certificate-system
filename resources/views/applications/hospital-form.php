
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
$pageTitle = 'Submit Birth Record';
require_once __DIR__ . '/../layouts/base.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Hospital Birth Record Submission</h4>
                </div>
                <div class="card-body">
                    <form id="hospitalSubmissionForm" method="POST" action="/hospital/submit-record" class="needs-validation" novalidate enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                        
                        <!-- Birth Details -->
                        <h5 class="mb-4">Birth Details</h5>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="childFirstName" class="form-label">Child's First Name</label>
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
                            <div class="col-md-3 mb-3">
                                <label for="dateOfBirth" class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" id="dateOfBirth" name="date_of_birth" required>
                                <div class="invalid-feedback">Please select date of birth.</div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="timeOfBirth" class="form-label">Time of Birth</label>
                                <input type="time" class="form-control" id="timeOfBirth" name="time_of_birth" required>
                                <div class="invalid-feedback">Please enter time of birth.</div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="gender" class="form-label">Gender</label>
                                <select class="form-select" id="gender" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                </select>
                                <div class="invalid-feedback">Please select gender.</div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="weight" class="form-label">Weight (grams)</label>
                                <input type="number" class="form-control" id="weight" name="weight" required>
                                <div class="invalid-feedback">Please enter birth weight.</div>
                            </div>
                        </div>

                        <!-- Medical Details -->
                        <h5 class="mb-4 mt-4">Medical Details</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="deliveryType" class="form-label">Type of Delivery</label>
                                <select class="form-select" id="deliveryType" name="delivery_type" required>
                                    <option value="">Select Type</option>
                                    <option value="normal">Normal Delivery</option>
                                    <option value="cesarean">Cesarean Section</option>
                                    <option value="assisted">Assisted Delivery</option>
                                </select>
                                <div class="invalid-feedback">Please select delivery type.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="attendant" class="form-label">Attending Medical Professional</label>
                                <input type="text" class="form-control" id="attendant" name="attendant" required>
                                <div class="invalid-feedback">Please enter attending professional's name.</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="complications" class="form-label">Complications (if any)</label>
                                <textarea class="form-control" id="complications" name="complications" rows="2"></textarea>
                            </div>
                        </div>

                        <!-- Parent Information -->
                        <h5 class="mb-4 mt-4">Parent Information</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="motherName" class="form-label">Mother's Full Name</label>
                                <input type="text" class="form-control" id="motherName" name="mother_name" required>
                                <div class="invalid-feedback">Please enter mother's name.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="motherNationalId" class="form-label">Mother's National ID</label>
                                <input type="text" class="form-control" id="motherNationalId" name="mother_national_id" required>
                                <div class="invalid-feedback">Please enter mother's national ID.</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="fatherName" class="form-label">Father's Full Name</label>
                                <input type="text" class="form-control" id="fatherName" name="father_name">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="fatherNationalId" class="form-label">Father's National ID</label>
                                <input type="text" class="form-control" id="fatherNationalId" name="father_national_id">
                            </div>
                        </div>

                        <!-- Supporting Documents -->
                        <h5 class="mb-4 mt-4">Medical Documentation</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="medicalRecord" class="form-label">Medical Record</label>
                                <input type="file" class="form-control" id="medicalRecord" name="medical_record" 
                                       accept=".pdf,.jpg,.jpeg,.png" required>
                                <div class="form-text">Upload PDF, JPG, or PNG (max 5MB)</div>
                                <div class="invalid-feedback">Please upload medical record.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="deliveryNotes" class="form-label">Delivery Notes</label>
                                <input type="file" class="form-control" id="deliveryNotes" name="delivery_notes" 
                                       accept=".pdf,.jpg,.jpeg,.png" required>
                                <div class="form-text">Upload PDF, JPG, or PNG (max 5MB)</div>
                                <div class="invalid-feedback">Please upload delivery notes.</div>
                            </div>
                        </div>

                        <!-- Verification -->
                        <div class="mb-4 mt-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="verification" required>
                                <label class="form-check-label" for="verification">
                                    I verify that all the information provided is accurate and complete according to hospital records.
                                </label>
                                <div class="invalid-feedback">
                                    You must verify the information before submitting.
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">Submit Birth Record</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('hospitalSubmissionForm');
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
        if (submitBtnText) submitBtnText.textContent = 'Submit Birth Record';
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
            document.getElementById('medicalRecord').files[0],
            document.getElementById('deliveryNotes').files[0]
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
    // Weight validation
    const weight = document.getElementById('weight');
    weight.addEventListener('input', function() {
        if (this.value < 100 || this.value > 9999) {
            this.setCustomValidity('Weight must be between 100 and 9999 grams');
        } else {
            this.setCustomValidity('');
        }
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>