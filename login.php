<?php
session_start();
require_once 'classes/Authentication.php';

$error_message = '';

if ($_POST) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $auth = new Authentication();
    $result = $auth->login($username, $password);
    
    if ($result['success']) {
        // Set session
        Authentication::setSession($result['user']);
        
        // Redirect based on role
        Authentication::redirectToDashboard($result['user']['role']);
    } else {
        $error_message = $result['message'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Library - Login</title>
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
                Sign in to your account
            </p>
        </div>
        
        <form class="mt-8 space-y-6" method="POST" id="loginForm" onsubmit="return validateLoginForm()">
            <?php if ($error_message): ?>
                <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">
                                <?php echo htmlspecialchars($error_message); ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="space-y-4">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">
                        <svg class="inline h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Username
                    </label>
                    <input id="username" name="username" type="text" required 
                           class="form-field appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                           placeholder="Enter your username">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                        <svg class="inline h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        Password
                    </label>
                    <input id="password" name="password" type="password" required 
                           class="form-field appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                           placeholder="Enter your password">
                </div>
            </div>

            <div>
                <button type="submit" id="login-btn"
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 transform hover:scale-105">
                    <span id="login-text">Sign in</span>
                    <span id="login-loading" class="hidden">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Signing in...
                    </span>
                </button>
            </div>
            
            <div class="text-center text-sm text-gray-600">
                <p>Demo Accounts:</p>
                <p><strong>Librarian:</strong> librarian1 / password</p>
                <p><strong>Staff:</strong> staff1 / password</p>
                <p><strong>Teacher:</strong> teacher1 / password</p>
                <p><strong>Student:</strong> student1 / password</p>
                <br>
                <p>Don't have an account? <a href="register.php" class="text-indigo-600 hover:text-indigo-500 font-medium">Register here</a></p>
            </div>
        </form>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.2.1/dist/flowbite.min.js"></script>
    <script>
        function validateLoginForm() {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();
            
            // Clear previous errors
            clearLoginErrors();
            
            let isValid = true;
            
            // Username validation
            if (username === '') {
                showLoginError('username', 'Username is required');
                isValid = false;
            }
            
            // Password validation
            if (password === '') {
                showLoginError('password', 'Password is required');
                isValid = false;
            }
            
            if (isValid) {
                showLoginLoading();
            }
            
            return isValid;
        }
        
        function showLoginError(fieldId, message) {
            const field = document.getElementById(fieldId);
            const errorDiv = document.createElement('div');
            errorDiv.className = 'mt-1 text-sm text-red-600 flex items-center';
            errorDiv.innerHTML = `
                <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                ${message}
            `;
            errorDiv.id = `login-error-${fieldId}`;
            field.parentNode.appendChild(errorDiv);
            field.classList.add('border-red-500');
        }
        
        function clearLoginErrors() {
            // Remove existing error messages
            document.querySelectorAll('[id^="login-error-"]').forEach(el => el.remove());
            // Remove error styling
            document.querySelectorAll('.border-red-500').forEach(el => el.classList.remove('border-red-500'));
        }
        
        function showLoginLoading() {
            const loginText = document.getElementById('login-text');
            const loginLoading = document.getElementById('login-loading');
            const loginBtn = document.getElementById('login-btn');
            
            loginText.classList.add('hidden');
            loginLoading.classList.remove('hidden');
            loginBtn.disabled = true;
        }
        
        // Add real-time validation feedback
        document.addEventListener('DOMContentLoaded', function() {
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
