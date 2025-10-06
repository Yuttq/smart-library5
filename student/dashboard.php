<?php
session_start();
require_once '../classes/Authentication.php';
require_once '../classes/Reservation.php';
require_once '../classes/Transaction.php';
require_once '../classes/BookManager.php';
require_once '../config/semester_manager.php';

// Check if user is logged in and is a student
Authentication::requireRole('student');

$auth = new Authentication();
$database = new Database();
$db = $database->getConnection();
$reservationManager = new Reservation($db);
$transactionManager = new Transaction($db);
$bookManager = new BookManager($db);
$semesterManager = new SemesterManager();
$current_semester = $semesterManager->getCurrentSemester();

// Handle reservations
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'reserve') {
        $book_id = $_POST['book_id'] ?? '';
        
        if (!empty($book_id)) {
            $result = $reservationManager->createReservation($_SESSION['user_id'], $book_id, $current_semester['id']);
            
            if ($result['success']) {
                $success_message = $result['message'];
            } else {
                $error_message = $result['errors']['general'];
            }
        }
    } elseif ($action === 'cancel_reservation') {
        $reservation_id = $_POST['reservation_id'] ?? '';
        
        if (!empty($reservation_id)) {
            if ($reservationManager->loadById($reservation_id)) {
                $result = $reservationManager->cancelReservation();
                
                if ($result['success']) {
                    $success_message = $result['message'];
                } else {
                    $error_message = $result['errors']['general'];
                }
            } else {
                $error_message = "Reservation not found";
            }
        }
    }
}

// Get available books
$available_books = $bookManager->getAvailableBooks();

// Get user's active reservations for current semester
$reservations = Reservation::getActiveReservations($db, $_SESSION['user_id'], $current_semester['id']);

// Get user's borrowed books for current semester
$borrowed_books = Transaction::getActiveBorrows($db, $_SESSION['user_id'], $current_semester['id']);

// Check borrowing limit for current semester
$borrow_count = $semesterManager->getStudentBorrowingCount($_SESSION['user_id'], $current_semester['id']);
$can_borrow = $semesterManager->canStudentBorrow($_SESSION['user_id'], $current_semester['id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Smart Library</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.2.1/dist/flowbite.min.css" rel="stylesheet" />
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-semibold text-gray-800">Smart Library - Student Dashboard</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-700">Welcome, <?php echo htmlspecialchars($_SESSION['first_name']); ?>!</span>
                    <a href="../logout.php" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <?php if (isset($success_message)): ?>
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <?php echo htmlspecialchars($error_message); ?>
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

        <!-- Borrowing Status -->
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-green-800">
                        Borrowing Status (This Semester)
                    </h3>
                    <div class="mt-2 text-sm text-green-700">
                        <p>Books borrowed: <?php echo $borrow_count; ?>/3</p>
                        <?php if (!$can_borrow): ?>
                            <p class="text-red-600 font-medium">You have reached the maximum borrowing limit (3 books per semester)</p>
                        <?php else: ?>
                            <p class="text-green-600 font-medium">You can borrow <?php echo (3 - $borrow_count); ?> more book(s) this semester</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <div class="flex justify-between items-center">
                <h2 class="text-lg font-medium text-gray-900">Quick Actions</h2>
                <div class="space-x-4">
                    <a href="fines.php" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                        View My Fines
                    </a>
                </div>
            </div>
        </div>

        <!-- My Borrowed Books -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">My Borrowed Books</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Book</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Borrowed Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($borrowed_books)): ?>
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-center text-gray-500">No borrowed books</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($borrowed_books as $book): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($book['title']); ?>
                                    <br><span class="text-xs text-gray-400">by <?php echo htmlspecialchars($book['author']); ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('M d, Y', strtotime($book['transaction_date'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('M d, Y', strtotime($book['due_date'])); ?>
                                    <?php if (strtotime($book['due_date']) < time()): ?>
                                        <span class="text-red-600 text-xs">(Overdue)</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- My Reservations -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">My Reservations</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Book</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reserved Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($reservations)): ?>
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-center text-gray-500">No active reservations</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($reservations as $reservation): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($reservation['title']); ?>
                                    <br><span class="text-xs text-gray-400">by <?php echo htmlspecialchars($reservation['author']); ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('M d, Y', strtotime($reservation['reservation_date'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="action" value="cancel_reservation">
                                        <input type="hidden" name="reservation_id" value="<?php echo $reservation['id']; ?>">
                                        <button type="submit" class="text-red-600 hover:text-red-900">Cancel</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Available Books -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">Available Books</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Author</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ISBN</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($available_books as $book): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($book['title']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo htmlspecialchars($book['author']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo htmlspecialchars($book['isbn']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <form method="POST" class="inline">
                                    <input type="hidden" name="action" value="reserve">
                                    <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                    <button type="submit" class="text-indigo-600 hover:text-indigo-900">Reserve</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
