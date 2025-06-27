<?php
$pageTitle = 'Register - Digital Birth Certificate System';
require_once __DIR__ . '/../layouts/base.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Create Account</h4>
                </div>
                <div class="card-body">
                    <form id="registrationForm" method="POST" action="/auth/register" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                        
                        <!-- Role Selection -->
                        <div class="mb-4">
                            <label class="form-label">Register as</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="role" id="parent" value="parent" checked>
                                <label class="btn btn-outline-primary" for="parent">Parent</label>
                                
                                <input type="radio" class="btn-check" name="role" id="hospital" value="hospital">
                                <label class="btn btn-outline-primary" for="hospital">Hospital</label>
                                
                                <input type="radio" class="btn-check" name="role" id="registrar" value="registrar">
                                <label class="btn btn-outline-primary" for="registrar">Registrar</label>
                            </div>
                        </div>

                        <div class="row">
                            <!-- First Name -->
                            <div class="col-md-6 mb-3">
                                <label for="firstName" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="firstName" name="first_name" required>
                                <div class="invalid-feedback">Please enter your first name.</div>
                            </div>

                            <!-- Last Name -->
                            <div class="col-md-6 mb-3">
                                <label for="lastName" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="lastName" name="last_name" required>
                                <div class="invalid-feedback">Please enter your last name.</div>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                            <div class="invalid-feedback">Please enter a valid email address.</div>
                        </div>

                        <!-- Phone -->
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone_number" required>
                            <div class="invalid-feedback">Please enter a valid phone number.</div>
                        </div>

                        <!-- Role-specific fields -->
                        <div id="parentFields" class="mb-3">
                            <label for="nationalId" class="form-label">National ID</label>
                            <input type="text" class="form-control" id="nationalId" name="national_id">
                            <div class="invalid-feedback">Please enter your National ID number.</div>
                        </div>

                        <div id="hospitalFields" class="mb-3 d-none">
                            <label for="hospitalId" class="form-label">Hospital Registration Number</label>
                            <input type="text" class="form-control" id="hospitalId" name="hospital_id">
                            <div class="invalid-feedback">Please enter the hospital registration number.</div>
                        </div>

                        <div id="registrarFields" class="mb-3 d-none">
                            <label for="registrarId" class="form-label">Registrar ID</label>
                            <input type="text" class="form-control" id="registrarId" name="registrar_id">
                            <div class="invalid-feedback">Please enter your registrar ID.</div>
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" required 
                                       pattern="^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">
                                Password must be at least 8 characters long and include letters and numbers.
                            </div>
                            <div class="invalid-feedback">
                                Please enter a valid password.
                            </div>
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-4">
                            <label for="confirmPassword" class="form-label">Confirm Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirmPassword" required>
                                <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback">Passwords do not match.</div>
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="/terms" target="_blank">Terms and Conditions</a>
                                </label>
                                <div class="invalid-feedback">
                                    You must agree to the terms and conditions.
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">Create Account</button>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                        Already have an account? <a href="/login">Login here</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const form = document.getElementById('registrationForm');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirmPassword');
    const roleInputs = document.querySelectorAll('input[name="role"]');
    
    // Toggle password visibility
    document.getElementById('togglePassword').addEventListener('click', function() {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        this.querySelector('i').classList.toggle('fa-eye');
        this.querySelector('i').classList.toggle('fa-eye-slash');
    });

    document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
        const type = confirmPassword.getAttribute('type') === 'password' ? 'text' : 'password';
        confirmPassword.setAttribute('type', type);
        this.querySelector('i').classList.toggle('fa-eye');
        this.querySelector('i').classList.toggle('fa-eye-slash');
    });

    // Role-specific fields toggle
    roleInputs.forEach(input => {
        input.addEventListener('change', function() {
            document.getElementById('parentFields').classList.add('d-none');
            document.getElementById('hospitalFields').classList.add('d-none');
            document.getElementById('registrarFields').classList.add('d-none');
            
            const selectedRole = this.value;
            document.getElementById(selectedRole + 'Fields').classList.remove('d-none');
            
            // Update required attributes
            document.getElementById('nationalId').required = (selectedRole === 'parent');
            document.getElementById('hospitalId').required = (selectedRole === 'hospital');
            document.getElementById('registrarId').required = (selectedRole === 'registrar');
        });
    });

    // Form validation
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }

        if (password.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity('Passwords do not match');
            event.preventDefault();
        } else {
            confirmPassword.setCustomValidity('');
        }

        form.classList.add('was-validated');
    });

    // Password validation
    password.addEventListener('input', function() {
        if (!this.value.match(/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/)) {
            this.setCustomValidity('Password must be at least 8 characters long and include letters and numbers');
        } else {
            this.setCustomValidity('');
        }
    });

    // Confirm password validation
    confirmPassword.addEventListener('input', function() {
        if (this.value !== password.value) {
            this.setCustomValidity('Passwords do not match');
        } else {
            this.setCustomValidity('');
        }
    });
});
</script>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>