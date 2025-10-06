<?php
session_start();
require_once '../config/database.php';
require_once '../config/semester_manager.php';

// Check if user is logged in and is staff
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header('Location: ../login.php');
    exit();
}

$semesterManager = new SemesterManager();
$database = new Database();
$db = $database->getConnection();

$current_semester = $semesterManager->getCurrentSemester();
$message = '';
$error = '';

// Handle clearance operations
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'process_clearance':
            $user_id = $_POST['user_id'] ?? '';
            $semester_id = $_POST['semester_id'] ?? $current_semester['id'];
            
            if (!empty($user_id)) {
                $result = $semesterManager->processTeacherClearance($user_id, $semester_id, $_SESSION['user_id']);
                if ($result['success']) {
                    $message = $result['message'];
                } else {
                    $error = $result['message'];
                }
            }
            break;
            
        case 'mark_book_lost':
            $transaction_id = $_POST['transaction_id'] ?? '';
            $book_price = $_POST['book_price'] ?? '';
            
            if (!empty($transaction_id) && !empty($book_price)) {
                $result = $semesterManager->markBookAsLost($transaction_id, $book_price);
                if ($result['success']) {
                    $message = $result['message'];
                } else {
                    $error = $result['message'];
                }
            }
            break;
            
        case 'process_book_payment':
            $transaction_id = $_POST['transaction_id'] ?? '';
            $amount_paid = $_POST['amount_paid'] ?? '';
            
            if (!empty($transaction_id) && !empty($amount_paid)) {
                $result = $semesterManager->processBookPricePayment($transaction_id, $amount_paid);
                if ($result['success']) {
                    $message = $result['message'];
                } else {
                    $error = $result['message'];
                }
            }
            break;
    }
}

// Get pending clearances
$pending_clearances = $semesterManager->getPendingClearances($current_semester['id']);

// Get teachers with active books
$query = "SELECT DISTINCT u.id, u.first_name, u.last_name, u.role, 
                 COUNT(t.id) as active_books_count
          FROM users u 
          JOIN transactions t ON u.id = t.user_id 
          WHERE u.role = 'teacher' 
          AND t.semester_id = ? 
          AND t.transaction_type = 'borrow' 
          AND t.status = 'active'
          GROUP BY u.id, u.first_name, u.last_name, u.role
          ORDER BY u.first_name, u.last_name";
$stmt = $db->prepare($query);
$stmt->execute([$current_semester['id']]);
$teachers_with_books = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get students with active books
$query = "SELECT DISTINCT u.id, u.first_name, u.last_name, u.role, 
                 COUNT(t.id) as active_books_count
          FROM users u 
          JOIN transactions t ON u.id = t.user_id 
          WHERE u.role = 'student' 
          AND t.semester_id = ? 
          AND t.transaction_type = 'borrow' 
          AND t.status = 'active'
          GROUP BY u.id, u.first_name, u.last_name, u.role
          ORDER BY u.first_name, u.last_name";
$stmt = $db->prepare($query);
$stmt->execute([$current_semester['id']]);
$students_with_books = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get lost books requiring payment
$query = "SELECT t.*, u.first_name, u.last_name, u.role, b.title, b.author, b.price
          FROM transactions t 
          JOIN users u ON t.user_id = u.id 
          JOIN books b ON t.book_id = b.id 
          WHERE t.semester_id = ? 
          AND t.status = 'lost' 
          AND t.book_price_paid_boolean = FALSE
          ORDER BY t.transaction_date DESC";
$stmt = $db->prepare($query);
$stmt->execute([$current_semester['id']]);
$lost_books = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clearance Management - Smart Library</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.2.1/dist/flowbite.min.css" rel="stylesheet" />
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-semibold text-gray-800">Clearance Management</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="dashboard.php" class="text-indigo-600 hover:text-indigo-900">← Back to Dashboard</a>
                    <span class="text-gray-700">Welcome, <?php echo htmlspecialchars($_SESSION['first_name']); ?>!</span>
                    <a href="../logout.php" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <?php if ($message): ?>
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Current Semester Info -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">
                        Current Semester: <?php echo htmlspecialchars($current_semester['name']); ?>
                    </h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <p>Period: <?php echo date('M d, Y', strtotime($current_semester['start_date'])); ?> - <?php echo date('M d, Y', strtotime($current_semester['end_date'])); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Teachers Requiring Clearance -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">Teachers Requiring Clearance</h2>
                <p class="text-sm text-gray-500">Teachers must return all books before semester clearance</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teacher</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Active Books</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($teachers_with_books)): ?>
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500">No teachers with active books</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($teachers_with_books as $teacher): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo $teacher['active_books_count']; ?> book(s)
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Pending Clearance
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="viewTeacherBooks(<?php echo $teacher['id']; ?>)" 
                                            class="text-indigo-600 hover:text-indigo-900">View Books</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Students with Active Books -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">Students with Active Books</h2>
                <p class="text-sm text-gray-500">Students can borrow up to 3 books per semester</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Active Books</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($students_with_books)): ?>
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500">No students with active books</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($students_with_books as $student): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo $student['active_books_count']; ?>/3 book(s)
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?php echo $student['active_books_count'] >= 3 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'; ?>">
                                        <?php echo $student['active_books_count'] >= 3 ? 'Limit Reached' : 'Within Limit'; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="viewStudentBooks(<?php echo $student['id']; ?>)" 
                                            class="text-indigo-600 hover:text-indigo-900">View Books</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Lost Books Requiring Payment -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">Lost Books Requiring Payment</h2>
                <p class="text-sm text-gray-500">Students must pay book price for lost books</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Book</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Book Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($lost_books)): ?>
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500">No lost books requiring payment</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($lost_books as $book): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($book['first_name'] . ' ' . $book['last_name']); ?>
                                    <br><span class="text-xs text-gray-500">(<?php echo ucfirst($book['role']); ?>)</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo htmlspecialchars($book['title']); ?>
                                    <br><span class="text-xs text-gray-400">by <?php echo htmlspecialchars($book['author']); ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    ₱<?php echo number_format($book['price'], 2); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="openPaymentModal(<?php echo $book['id']; ?>, <?php echo $book['price']; ?>)" 
                                            class="text-green-600 hover:text-green-900">Process Payment</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div id="paymentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Process Book Price Payment</h3>
                <form method="POST" id="paymentForm">
                    <input type="hidden" name="action" value="process_book_payment">
                    <input type="hidden" name="transaction_id" id="payment_transaction_id">
                    <div class="mb-4">
                        <label for="amount_paid" class="block text-sm font-medium text-gray-700">Amount Paid</label>
                        <input type="number" name="amount_paid" id="amount_paid" step="0.01" required 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closePaymentModal()" 
                                class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">Cancel</button>
                        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Process Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openPaymentModal(transactionId, bookPrice) {
            document.getElementById('payment_transaction_id').value = transactionId;
            document.getElementById('amount_paid').value = bookPrice;
            document.getElementById('paymentModal').classList.remove('hidden');
        }

        function closePaymentModal() {
            document.getElementById('paymentModal').classList.add('hidden');
        }

        function viewTeacherBooks(userId) {
            // Get teacher's active books via AJAX
            fetch(`get_user_books.php?user_id=${userId}&type=teacher`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showBooksModal(data.books, 'Teacher');
                    } else {
                        alert('Error loading books: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Error: ' + error);
                });
        }

        function viewStudentBooks(userId) {
            // Get student's active books via AJAX
            fetch(`get_user_books.php?user_id=${userId}&type=student`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showBooksModal(data.books, 'Student');
                    } else {
                        alert('Error loading books: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Error: ' + error);
                });
        }

        function showBooksModal(books, userType) {
            let modalContent = `
                <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                    <div class="relative top-20 mx-auto p-5 border w-3/4 shadow-lg rounded-md bg-white">
                        <div class="mt-3">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">${userType} Active Books</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Book</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Borrowed Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
            `;
            
            if (books.length === 0) {
                modalContent += `
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">No active books</td>
                    </tr>
                `;
            } else {
                books.forEach(book => {
                    const isOverdue = new Date(book.due_date) < new Date();
                    modalContent += `
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                ${book.title}
                                <br><span class="text-xs text-gray-400">by ${book.author}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ${new Date(book.transaction_date).toLocaleDateString()}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ${new Date(book.due_date).toLocaleDateString()}
                                ${isOverdue ? '<span class="text-red-600 text-xs">(Overdue)</span>' : ''}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${isOverdue ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'}">
                                    ${isOverdue ? 'Overdue' : 'Active'}
                                </span>
                            </td>
                        </tr>
                    `;
                });
            }
            
            modalContent += `
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-4 flex justify-end">
                                <button onclick="closeBooksModal()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', modalContent);
        }

        function closeBooksModal() {
            const modal = document.querySelector('.fixed.inset-0.bg-gray-600');
            if (modal) {
                modal.remove();
            }
        }
    </script>
</body>
</html>
