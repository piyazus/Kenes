/**
 * Form Validation Logic for Kenes Platform
 */

const FormValidator = {
    // Regular expressions
    patterns: {
        email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
        password: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/, // Min 8, 1 uppercase, 1 lowercase, 1 number
        iin: /^\d{12}$/, // Exactly 12 digits
        phone: /^\+7\d{10}$/ // +7 followed by 10 digits
    },

    messages: {
        required: 'This field is required.',
        email: 'Please enter a valid email address.',
        password: 'Password must be at least 8 chars, with 1 uppercase, 1 lowercase, and 1 number.',
        confirmPassword: 'Passwords do not match.',
        iin: 'IIN must be exactly 12 digits.',
        phone: 'Phone must be in format +7XXXXXXXXXX.'
    },

    init: function () {
        const forms = document.querySelectorAll('.needs-validation');
        forms.forEach(form => {
            // Disable native HTML5 validation
            form.setAttribute('novalidate', true);

            // Validate on submit
            form.addEventListener('submit', (e) => {
                if (!this.validateForm(form)) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            });

            // Validate on input (real-time)
            const inputs = form.querySelectorAll('input, textarea, select');
            inputs.forEach(input => {
                input.addEventListener('input', () => {
                    this.validateField(input);
                });

                // Validate on blur as well for good UX
                input.addEventListener('blur', () => {
                    this.validateField(input);
                });
            });
        });
    },

    validateForm: function (form) {
        let isValid = true;
        const inputs = form.querySelectorAll('input, textarea, select');

        inputs.forEach(input => {
            if (!this.validateField(input)) {
                isValid = false;
            }
        });

        return isValid;
    },

    validateField: function (input) {
        let isValid = true;
        let errorMessage = '';

        // Reset
        this.setFieldStatus(input, 'neutral');

        // Check required
        if (input.hasAttribute('required') && !input.value.trim()) {
            isValid = false;
            errorMessage = this.messages.required;
        }
        // Check pattern types if value exists
        else if (input.value.trim()) {
            const type = input.getAttribute('data-validate');

            if (type === 'email' && !this.patterns.email.test(input.value)) {
                isValid = false;
                errorMessage = this.messages.email;
            }
            if (type === 'password' && !this.patterns.password.test(input.value)) {
                isValid = false;
                errorMessage = this.messages.password;
            }
            if (type === 'iin' && !this.patterns.iin.test(input.value)) {
                isValid = false;
                errorMessage = this.messages.iin;
            }
            if (type === 'phone' && !this.patterns.phone.test(input.value)) {
                isValid = false;
                errorMessage = this.messages.phone;
            }
            // Confirm Password Check
            if (type === 'confirm-password') {
                const passwordInput = document.querySelector('input[data-validate="password"]');
                if (passwordInput && input.value !== passwordInput.value) {
                    isValid = false;
                    errorMessage = this.messages.confirmPassword;
                }
            }
        }

        if (!isValid) {
            this.setFieldStatus(input, 'invalid', errorMessage);
        } else if (input.value.trim()) {
            this.setFieldStatus(input, 'valid');
        }

        return isValid;
    },

    setFieldStatus: function (input, status, message = '') {
        const feedback = input.parentElement.querySelector('.invalid-feedback');

        input.classList.remove('is-valid', 'is-invalid');

        if (status === 'invalid') {
            input.classList.add('is-invalid');
            if (feedback) feedback.textContent = message;
        } else if (status === 'valid') {
            input.classList.add('is-valid');
            if (feedback) feedback.textContent = '';
        }
    }
};

document.addEventListener('DOMContentLoaded', () => {
    FormValidator.init();
});
