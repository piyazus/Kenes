/**
 * Form Validation — Kenes Platform
 * Enhanced client-side validation with live feedback
 */
document.addEventListener('DOMContentLoaded', function () {
    var forms = document.querySelectorAll('form.needs-validation');

    forms.forEach(function (form) {
        // Real-time validation on blur
        form.querySelectorAll('[required], [data-validate]').forEach(function (input) {
            input.addEventListener('blur', function () {
                validateField(this);
            });
            input.addEventListener('input', function () {
                // Clear error on typing
                if (this.classList.contains('is-invalid')) {
                    this.classList.remove('is-invalid');
                    var fb = this.parentElement.querySelector('.invalid-feedback');
                    if (fb) fb.textContent = '';
                }
            });
        });

        // Submit validation
        form.addEventListener('submit', function (e) {
            var valid = true;
            form.querySelectorAll('[required], [data-validate]').forEach(function (input) {
                if (!validateField(input)) {
                    valid = false;
                }
            });
            if (!valid) {
                e.preventDefault();
                // Focus first invalid field
                var firstInvalid = form.querySelector('.is-invalid');
                if (firstInvalid) firstInvalid.focus();
            }
        });
    });

    function validateField(input) {
        var type = input.dataset.validate || '';
        var value = input.value.trim();
        var valid = true;
        var message = '';

        // Required check
        if (input.required && !value) {
            valid = false;
            message = 'This field is required.';
        }
        // Email
        else if (type === 'email' && value) {
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                valid = false;
                message = 'Please enter a valid email address.';
            }
        }
        // Phone
        else if (type === 'phone' && value) {
            var phoneClean = value.replace(/[\s()-]/g, '');
            if (phoneClean.length < 10 || phoneClean.length > 15) {
                valid = false;
                message = 'Please enter a valid phone number.';
            }
        }
        // Password (8+ chars, 1 upper, 1 lower, 1 digit)
        else if (type === 'password' && value) {
            if (value.length < 8) {
                valid = false;
                message = 'Password must be at least 8 characters.';
            } else if (!/[A-Z]/.test(value)) {
                valid = false;
                message = 'Password needs at least 1 uppercase letter.';
            } else if (!/[a-z]/.test(value)) {
                valid = false;
                message = 'Password needs at least 1 lowercase letter.';
            } else if (!/\d/.test(value)) {
                valid = false;
                message = 'Password needs at least 1 number.';
            }
        }
        // Confirm password
        else if (type === 'confirm-password' && value) {
            var passwordField = document.getElementById('password');
            if (passwordField && value !== passwordField.value) {
                valid = false;
                message = 'Passwords do not match.';
            }
        }
        // IIN (12 digits)
        else if (type === 'iin' && value) {
            if (!/^\d{12}$/.test(value)) {
                valid = false;
                message = 'IIN/BIN must be exactly 12 digits.';
            }
        }

        // Apply styling
        input.classList.toggle('is-invalid', !valid);
        input.classList.toggle('is-valid', valid && !!value);

        var feedback = input.parentElement.querySelector('.invalid-feedback');
        if (feedback) {
            feedback.textContent = message;
        }

        return valid;
    }
});
