<?php
session_start();
require_once '../config/database.php';
require_once '../config/semester_manager.php';

// Check if user is logged in and is staff
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header('Location: ../login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$semesterManager = new SemesterManager();
$current_semester = $semesterManager->getCurrentSemester();

$success_message = '';
$error_message = '';

// Handle penalty payment
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'pay_penalty') {
    $fine_id = $_POST['fine_id'] ?? '';
    $amount_paid = $_POST['amount_paid'] ?? 0;
    
    if (!empty($fine_id) && $amount_paid > 0) {
        try {
            $db->beginTransaction();
            
            // Update fine status
            $query = "UPDATE fines SET status = 'paid', paid_at = NOW() WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$fine_id]);
            
            // Update transaction penalty_paid status
            $query = "UPDATE transactions t 
                      JOIN fines f ON t.id = f.transaction_id 
                      SET t.penalty_paid = TRUE 
                      WHERE f.id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$fine_id]);
            
            $db->commit();
            $success_message = "Penalty payment recorded successfully!";
        } catch (Exception $e) {
            $db->rollback();
            $error_message = "Error processing payment: " . $e->getMessage();
        }
    }
}

// Handle clearance update
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'update_clearance') {
    $user_id = $_POST['user_id'] ?? '';
    $clearance_status = $_POST['clearance_status'] ?? '';
    $notes = $_POST['notes'] ?? '';
    
    if (!empty($user_id) && !empty($clearance_status)) {
        try {
            $query = "UPDATE clearances SET status = ?, notes = ?, cleared_by = ? WHERE user_id = ? AND semester_id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$clearance_status, $notes, $_SESSION['user_id'], $user_id, $current_semester['id']]);
            
            $success_message = "Clearance status updated successfully!";
        } catch (Exception $e) {
            $error_message = "Error updating clearance: " . $e->getMessage();
        }
    }
}

// Get all fines with user and transaction details
$query = "SELECT f.*, u.first_name, u.last_name, u.student_id, u.role, 
                 t.due_date, t.transaction_date, b.title as book_title, b.author
          FROM fines f
          JOIN users u ON f.user_id = u.id
          JOIN transactions t ON f.transaction_id = t.id
          JOIN books b ON t.book_id = b.id
          ORDER BY f.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$fines = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all clearances
$query = "SELECT c.*, u.first_name, u.last_name, u.student_id, u.role
          FROM clearances c
          JOIN users u ON c.user_id = u.id
          WHERE c.semester_id = ?
          ORDER BY c.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute([$current_semester['id']]);
$clearances = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics
$total_fines = array_sum(array_column($fines, 'amount'));
$paid_fines = array_sum(array_column(array_filter($fines, function($f) { return $f['status'] === 'paid'; }), 'amount'));
$pending_fines = $total_fines - $paid_fines;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penalty Management - Smart Library</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.2.1/dist/flowbite.min.css" rel="stylesheet" />
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-semibold text-gray-800">Smart Library - Penalty Management</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="dashboard.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Back to Dashboard</a>
                    <span class="text-gray-700">Welcome, <?php echo htmlspecialchars($_SESSION['first_name']); ?>!</span>
                    <a href="../logout.php" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <?php if ($success_message): ?>
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Fines</dt>
                                <dd class="text-lg font-medium text-gray-900">₱<?php echo number_format($total_fines, 2); ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Paid Fines</dt>
                                <dd class="text-lg font-medium text-gray-900">₱<?php echo number_format($paid_fines, 2); ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Pending Fines</dt>
                                <dd class="text-lg font-medium text-gray-900">₱<?php echo number_format($pending_fines, 2); ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fines Management -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">Fines Management</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Book</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($fines as $fine): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($fine['first_name'] . ' ' . $fine['last_name']); ?>
                                <br><span class="text-xs text-gray-500"><?php echo ucfirst($fine['role']); ?></span>
                                <?php if ($fine['student_id']): ?>
                                    <br><span class="text-xs text-blue-600">ID: <?php echo htmlspecialchars($fine['student_id']); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo htmlspecialchars($fine['book_title']); ?>
                                <br><span class="text-xs text-gray-400">by <?php echo htmlspecialchars($fine['author']); ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ₱<?php echo number_format($fine['amount'], 2); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo ucfirst($fine['reason']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    <?php echo $fine['status'] === 'paid' ? 'bg-green-100 text-green-800' : 
                                              ($fine['status'] === 'waived' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); ?>">
                                    <?php echo ucfirst($fine['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <?php if ($fine['status'] === 'pending'): ?>
                                    <button onclick="openPaymentModal(<?php echo $fine['id']; ?>, <?php echo $fine['amount']; ?>)"
                                            class="text-indigo-600 hover:text-indigo-900">Pay</button>
                                <?php else: ?>
                                    <span class="text-gray-400">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Clearance Management -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">Clearance Management</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($clearances as $clearance): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($clearance['first_name'] . ' ' . $clearance['last_name']); ?>
                                <br><span class="text-xs text-gray-500"><?php echo ucfirst($clearance['role']); ?></span>
                                <?php if ($clearance['student_id']): ?>
                                    <br><span class="text-xs text-blue-600">ID: <?php echo htmlspecialchars($clearance['student_id']); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    <?php echo $clearance['status'] === 'cleared' ? 'bg-green-100 text-green-800' : 
                                              ($clearance['status'] === 'blocked' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'); ?>">
                                    <?php echo ucfirst($clearance['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <?php echo htmlspecialchars($clearance['notes']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button onclick="openClearanceModal(<?php echo $clearance['user_id']; ?>, '<?php echo $clearance['status']; ?>', '<?php echo htmlspecialchars($clearance['notes']); ?>')"
                                        class="text-indigo-600 hover:text-indigo-900">Update</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div id="paymentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Process Penalty Payment</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="pay_penalty">
                    <input type="hidden" name="fine_id" id="payment_fine_id">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Amount to Pay</label>
                        <input type="number" name="amount_paid" id="payment_amount" step="0.01" min="0" required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closePaymentModal()" 
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">Cancel</button>
                        <button type="submit" 
                                class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Process Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Clearance Modal -->
    <div id="clearanceModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Update Clearance Status</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="update_clearance">
                    <input type="hidden" name="user_id" id="clearance_user_id">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="clearance_status" id="clearance_status" required
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="pending">Pending</option>
                            <option value="cleared">Cleared</option>
                            <option value="blocked">Blocked</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea name="notes" id="clearance_notes" rows="3"
                                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeClearanceModal()" 
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">Cancel</button>
                        <button type="submit" 
                                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.2.1/dist/flowbite.min.js"></script>
    <script>
        function openPaymentModal(fineId, amount) {
            document.getElementById('payment_fine_id').value = fineId;
            document.getElementById('payment_amount').value = amount;
            document.getElementById('paymentModal').classList.remove('hidden');
        }

        function closePaymentModal() {
            document.getElementById('paymentModal').classList.add('hidden');
        }

        function openClearanceModal(userId, status, notes) {
            document.getElementById('clearance_user_id').value = userId;
            document.getElementById('clearance_status').value = status;
            document.getElementById('clearance_notes').value = notes;
            document.getElementById('clearanceModal').classList.remove('hidden');
        }

        function closeClearanceModal() {
            document.getElementById('clearanceModal').classList.add('hidden');
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const paymentModal = document.getElementById('paymentModal');
            const clearanceModal = document.getElementById('clearanceModal');
            if (event.target === paymentModal) {
                closePaymentModal();
            }
            if (event.target === clearanceModal) {
                closeClearanceModal();
            }
        }
    </script>
</body>
</html>