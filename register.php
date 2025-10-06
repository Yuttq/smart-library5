<?php
session_start();
require_once 'classes/Authentication.php';

// Redirect if already logged in
if (Authentication::isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$auth = new Authentication();
$errors = [];
$success_message = '';

if ($_POST) {
    $result = $auth->register($_POST);
    
    if ($result['success']) {
        $success_message = $result['message'];
        // Clear form data
        $_POST = [];
    } else {
        $errors = $result['errors'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Smart Library</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.2.1/dist/flowbite.min.css" rel="stylesheet" />
    <style>
        .success-animation {
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .loading-spinner {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .form-field {
            transition: all 0.3s ease;
        }
        
        .form-field:focus {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-indigo-50 to-blue-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <!-- Library Icon -->
            <div class="mx-auto h-16 w-16 bg-indigo-600 rounded-full flex items-center justify-center mb-4">
                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
            </div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Smart Library System
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Create your account to get started
            </p>
        </div>
        
        <form class="mt-8 space-y-6" method="POST" id="registerForm" onsubmit="return validateForm()">
            <?php if ($success_message): ?>
                <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-4 success-animation">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700">
                                <?php echo htmlspecialchars($success_message); ?>
                            </p>
                            <div class="mt-2">
                                <a href="login.php" class="text-sm font-medium text-green-800 hover:text-green-600">
                                    Click here to login →
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (isset($errors['general'])): ?>
                <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">
                                <?php echo htmlspecialchars($errors['general']); ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">
                        <svg class="inline h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        First Name
                    </label>
                    <input id="first_name" name="first_name" type="text" required 
                           value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>"
                           class="form-field mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm <?php echo isset($errors['first_name']) ? 'border-red-500' : ''; ?>">
                    <?php if (isset($errors['first_name'])): ?>
                        <p class="mt-1 text-sm text-red-600 flex items-center">
                            <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <?php echo htmlspecialchars($errors['first_name']); ?>
                        </p>
                    <?php endif; ?>
                </div>
                
                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">
                        <svg class="inline h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Last Name
                    </label>
                    <input id="last_name" name="last_name" type="text" required 
                           value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>"
                           class="form-field mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm <?php echo isset($errors['last_name']) ? 'border-red-500' : ''; ?>">
                    <?php if (isset($errors['last_name'])): ?>
                        <p class="mt-1 text-sm text-red-600 flex items-center">
                            <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <?php echo htmlspecialchars($errors['last_name']); ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                <input id="username" name="username" type="text" required 
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                       class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm <?php echo isset($errors['username']) ? 'border-red-500' : ''; ?>">
                <?php if (isset($errors['username'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?php echo htmlspecialchars($errors['username']); ?></p>
                <?php endif; ?>
            </div>
            
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                <input id="email" name="email" type="email" required 
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                       class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm <?php echo isset($errors['email']) ? 'border-red-500' : ''; ?>">
                <?php if (isset($errors['email'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?php echo htmlspecialchars($errors['email']); ?></p>
                <?php endif; ?>
            </div>
            
            <div>
                <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                <select id="role" name="role" required 
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm <?php echo isset($errors['role']) ? 'border-red-500' : ''; ?>">
                    <option value="">Select your role</option>
                    <option value="student" <?php echo (($_POST['role'] ?? '') === 'student') ? 'selected' : ''; ?>>Student</option>
                    <option value="teacher" <?php echo (($_POST['role'] ?? '') === 'teacher') ? 'selected' : ''; ?>>Teacher</option>
                </select>
                <?php if (isset($errors['role'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?php echo htmlspecialchars($errors['role']); ?></p>
                <?php endif; ?>
            </div>
            
            <div id="student_id_field" style="display: none;">
                <label for="student_id" class="block text-sm font-medium text-gray-700">Student ID</label>
                <input id="student_id" name="student_id" type="text" 
                       value="<?php echo htmlspecialchars($_POST['student_id'] ?? ''); ?>"
                       placeholder="e.g., 1349802"
                       class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm <?php echo isset($errors['student_id']) ? 'border-red-500' : ''; ?>">
                <?php if (isset($errors['student_id'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?php echo htmlspecialchars($errors['student_id']); ?></p>
                <?php endif; ?>
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input id="password" name="password" type="password" required 
                       class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm <?php echo isset($errors['password']) ? 'border-red-500' : ''; ?>">
                <?php if (isset($errors['password'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?php echo htmlspecialchars($errors['password']); ?></p>
                <?php endif; ?>
            </div>
            
            <div>
                <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                <input id="confirm_password" name="confirm_password" type="password" required 
                       class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>

            <div>
                <button type="submit" id="submit-btn"
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 transform hover:scale-105">
                    <span id="btn-text">Create Account</span>
                    <span id="btn-loading" class="hidden">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Creating Account...
                    </span>
                </button>
            </div>
            
            <div class="text-center">
                <p class="text-sm text-gray-600">
                    Already have an account? 
                    <a href="login.php" class="font-medium text-indigo-600 hover:text-indigo-500">
                        Sign in here
                    </a>
                </p>
            </div>
        </form>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.2.1/dist/flowbite.min.js"></script>
    <script>
        // Enhanced form validation
        function validateForm() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const role = document.getElementById('role').value;
            const studentId = document.getElementById('student_id').value;
            
            // Clear previous errors
            clearErrors();
            
            let isValid = true;
            
            // Password validation
            if (password.length < 6) {
                showError('password', 'Password must be at least 6 characters long');
                isValid = false;
            }
            
            // Password confirmation
            if (password !== confirmPassword) {
                showError('confirm_password', 'Passwords do not match');
                isValid = false;
            }
            
            // Student ID validation for students
            if (role === 'student' && studentId.trim() === '') {
                showError('student_id', 'Student ID is required for students');
                isValid = false;
            }
            
            // Email validation
            const email = document.getElementById('email').value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showError('email', 'Please enter a valid email address');
                isValid = false;
            }
            
            if (isValid) {
                showLoading();
            }
            
            return isValid;
        }
        
        function showError(fieldId, message) {
            const field = document.getElementById(fieldId);
            const errorDiv = document.createElement('div');
            errorDiv.className = 'mt-1 text-sm text-red-600 flex items-center';
            errorDiv.innerHTML = `
                <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                ${message}
            `;
            errorDiv.id = `error-${fieldId}`;
            field.parentNode.appendChild(errorDiv);
            field.classList.add('border-red-500');
        }
        
        function clearErrors() {
            // Remove existing error messages
            document.querySelectorAll('[id^="error-"]').forEach(el => el.remove());
            // Remove error styling
            document.querySelectorAll('.border-red-500').forEach(el => el.classList.remove('border-red-500'));
        }
        
        function showLoading() {
            const btnText = document.getElementById('btn-text');
            const btnLoading = document.getElementById('btn-loading');
            const submitBtn = document.getElementById('submit-btn');
            
            btnText.classList.add('hidden');
            btnLoading.classList.remove('hidden');
            submitBtn.disabled = true;
        }
        
        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (password !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
        
        document.getElementById('password').addEventListener('input', function() {
            const confirmPassword = document.getElementById('confirm_password');
            if (confirmPassword.value) {
                confirmPassword.dispatchEvent(new Event('input'));
            }
        });
        
        // Show/hide student ID field based on role selection
        document.getElementById('role').addEventListener('change', function() {
            const studentIdField = document.getElementById('student_id_field');
            const studentIdInput = document.getElementById('student_id');
            
            if (this.value === 'student') {
                studentIdField.style.display = 'block';
                studentIdInput.required = true;
            } else {
                studentIdField.style.display = 'none';
                studentIdInput.required = false;
                studentIdInput.value = '';
            }
        });
        
        // Show student ID field if role is already selected (for form validation errors)
        document.addEventListener('DOMContentLoaded', function() {
            const roleSelect = document.getElementById('role');
            const studentIdField = document.getElementById('student_id_field');
            const studentIdInput = document.getElementById('student_id');
            
            if (roleSelect.value === 'student') {
                studentIdField.style.display = 'block';
                studentIdInput.required = true;
            }
            
            // Add real-time validation feedback
            const inputs = document.querySelectorAll('input[required]');
            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    if (this.value.trim() === '') {
                        this.classList.add('border-red-300');
                    } else {
                        this.classList.remove('border-red-300');
                    }
                });
            });
        });
    </script>
</body>
</html>
