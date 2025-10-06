<?php
/**
 * Test Data Generator for Smart Library System
 * This script generates comprehensive test data for testing all features
 */

require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

try {
    echo "Generating test data for Smart Library System...\n";
    
    // Clear existing test data (outside transaction; disable FK checks to avoid deadlocks)
    echo "Clearing existing test data...\n";
    $db->exec("SET FOREIGN_KEY_CHECKS=0");
    // Delete child tables first
    $db->exec("DELETE FROM fines WHERE user_id > 4");
    $db->exec("DELETE FROM transactions WHERE user_id > 4");
    $db->exec("DELETE FROM clearances WHERE user_id > 4");
    // Optionally clear reservations linked to test users
    if ($db->query("SHOW TABLES LIKE 'reservations'")->rowCount() > 0) {
        $db->exec("DELETE FROM reservations WHERE user_id > 4");
    }
    // Delete users last
    $db->exec("DELETE FROM users WHERE id > 4");
    // Reset book statuses for higher IDs (sample data remains)
    $db->exec("UPDATE books SET status = 'available' WHERE id > 8");
    $db->exec("SET FOREIGN_KEY_CHECKS=1");

    // Start a fresh transaction for inserts only
    $db->beginTransaction();
    
    // Generate additional test students
    echo "Creating test students...\n";
    $students = [
        ['username' => 'student_test1', 'first_name' => 'Alice', 'last_name' => 'Johnson', 'email' => 'alice.johnson@student.com', 'student_id' => '1349807'],
        ['username' => 'student_test2', 'first_name' => 'Bob', 'last_name' => 'Smith', 'email' => 'bob.smith@student.com', 'student_id' => '1349808'],
        ['username' => 'student_test3', 'first_name' => 'Carol', 'last_name' => 'Davis', 'email' => 'carol.davis@student.com', 'student_id' => '1349809'],
        ['username' => 'student_test4', 'first_name' => 'David', 'last_name' => 'Wilson', 'email' => 'david.wilson@student.com', 'student_id' => '1349810'],
        ['username' => 'student_test5', 'first_name' => 'Eva', 'last_name' => 'Brown', 'email' => 'eva.brown@student.com', 'student_id' => '1349811'],
    ];
    $insertedStudentIds = [];
    foreach ($students as $student) {
        $query = "INSERT INTO users (username, password, role, first_name, last_name, email, student_id) VALUES (?, ?, 'student', ?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->execute([
            $student['username'],
            password_hash('password', PASSWORD_DEFAULT),
            $student['first_name'],
            $student['last_name'],
            $student['email'],
            $student['student_id']
        ]);
        $insertedStudentIds[] = (int)$db->lastInsertId();
    }
    
    // Generate additional test books
    echo "Creating test books...\n";
    $books = [
        ['title' => 'Python Programming', 'author' => 'Dr. Mark Taylor', 'isbn' => '978-1234567902', 'category' => 'Computer Science', 'price' => 650.00],
        ['title' => 'Linear Algebra', 'author' => 'Prof. Jennifer Lee', 'isbn' => '978-1234567903', 'category' => 'Mathematics', 'price' => 500.00],
        ['title' => 'Organic Chemistry', 'author' => 'Dr. Robert Chen', 'isbn' => '978-1234567904', 'category' => 'Chemistry', 'price' => 700.00],
        ['title' => 'World History', 'author' => 'Prof. Maria Rodriguez', 'isbn' => '978-1234567905', 'category' => 'History', 'price' => 450.00],
        ['title' => 'Microeconomics', 'author' => 'Dr. James Wilson', 'isbn' => '978-1234567906', 'category' => 'Economics', 'price' => 550.00],
    ];
    $insertedBookIds = [];
    foreach ($books as $book) {
        $query = "INSERT INTO books (title, author, isbn, category, price, status) VALUES (?, ?, ?, ?, ?, 'available')";
        $stmt = $db->prepare($query);
        $stmt->execute([
            $book['title'],
            $book['author'],
            $book['isbn'],
            $book['category'],
            $book['price']
        ]);
        $insertedBookIds[] = (int)$db->lastInsertId();
    }
    
    // Get current semester
    $query = "SELECT id FROM semesters WHERE is_current = 1 LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $semester = $stmt->fetch(PDO::FETCH_ASSOC);
    $semester_id = $semester['id'];
    
    // Prepare user IDs for transactions (use existing student1 if present + newly inserted)
    $userIdsForTransactions = [];
    // Try to include existing 'student1' (often id <= 4) if it exists
    $stmt = $db->prepare("SELECT id FROM users WHERE username = 'student1' LIMIT 1");
    $stmt->execute();
    $existingStudent1 = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($existingStudent1) {
        $userIdsForTransactions[] = (int)$existingStudent1['id'];
    }
    // Append inserted student ids
    foreach ($insertedStudentIds as $sid) {
        $userIdsForTransactions[] = $sid;
    }
    // Ensure we have at least 5 user ids by duplicating existing if needed
    while (count($userIdsForTransactions) < 5) {
        $userIdsForTransactions[] = $userIdsForTransactions[array_rand($userIdsForTransactions)];
    }

    // Ensure enough book IDs
    $bookIdsForTransactions = $insertedBookIds;
    if (count($bookIdsForTransactions) < 5) {
        // Fallback: pull some existing available books
        $stmt = $db->prepare("SELECT id FROM books WHERE status = 'available' LIMIT 5");
        $stmt->execute();
        $fallbackBooks = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        foreach ($fallbackBooks as $bid) {
            if (!in_array((int)$bid, $bookIdsForTransactions, true)) {
                $bookIdsForTransactions[] = (int)$bid;
            }
        }
    }

    // Generate test transactions with various scenarios
    echo "Creating test transactions...\n";
    
    // Scenario 1: Recently borrowed books (within due date)
    $recent_borrows = [];
    $recentBorrowDates = [
        ['borrow' => '2025-01-25', 'due' => '2025-02-08'],
        ['borrow' => '2025-01-26', 'due' => '2025-02-09'],
        ['borrow' => '2025-01-27', 'due' => '2025-02-10'],
    ];
    for ($i = 0; $i < 3; $i++) {
        $recent_borrows[] = [
            'user_id' => $userIdsForTransactions[$i % count($userIdsForTransactions)],
            'book_id' => $bookIdsForTransactions[$i % count($bookIdsForTransactions)],
            'borrow_date' => $recentBorrowDates[$i]['borrow'],
            'due_date' => $recentBorrowDates[$i]['due']
        ];
    }
    
    foreach ($recent_borrows as $borrow) {
        $query = "INSERT INTO transactions (user_id, book_id, semester_id, transaction_type, transaction_date, due_date, status) VALUES (?, ?, ?, 'borrow', ?, ?, 'active')";
        $stmt = $db->prepare($query);
        $stmt->execute([$borrow['user_id'], $borrow['book_id'], $semester_id, $borrow['borrow_date'], $borrow['due_date']]);
        
        // Update book status
        $query = "UPDATE books SET status = 'borrowed' WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$borrow['book_id']]);
    }
    
    // Scenario 2: Overdue books (past due date)
    $overdue_borrows = [];
    $overdueBorrowDates = [
        ['borrow' => '2025-01-10', 'due' => '2025-01-24', 'penalty' => 40.00],
        ['borrow' => '2025-01-12', 'due' => '2025-01-26', 'penalty' => 20.00],
    ];
    for ($i = 0; $i < 2; $i++) {
        $userIndex = ($i + 3) % count($userIdsForTransactions);
        $bookIndex = ($i + 3) % count($bookIdsForTransactions);
        $overdue_borrows[] = [
            'user_id' => $userIdsForTransactions[$userIndex],
            'book_id' => $bookIdsForTransactions[$bookIndex],
            'borrow_date' => $overdueBorrowDates[$i]['borrow'],
            'due_date' => $overdueBorrowDates[$i]['due'],
            'penalty' => $overdueBorrowDates[$i]['penalty']
        ];
    }
    
    foreach ($overdue_borrows as $borrow) {
        $query = "INSERT INTO transactions (user_id, book_id, semester_id, transaction_type, transaction_date, due_date, status, penalty_amount) VALUES (?, ?, ?, 'borrow', ?, ?, 'overdue', ?)";
        $stmt = $db->prepare($query);
        $stmt->execute([$borrow['user_id'], $borrow['book_id'], $semester_id, $borrow['borrow_date'], $borrow['due_date'], $borrow['penalty']]);
        
        // Update book status
        $query = "UPDATE books SET status = 'borrowed' WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$borrow['book_id']]);
    }
    
    // Generate fines for overdue books
    echo "Creating test fines...\n";
    $query = "SELECT id, user_id, penalty_amount FROM transactions WHERE status = 'overdue'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $overdue_transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($overdue_transactions as $transaction) {
        $query = "INSERT INTO fines (user_id, transaction_id, amount, reason, status) VALUES (?, ?, ?, 'overdue', 'pending')";
        $stmt = $db->prepare($query);
        $stmt->execute([$transaction['user_id'], $transaction['id'], $transaction['penalty_amount']]);
    }
    
    // Generate clearance records
    echo "Creating test clearances...\n";
    $clearances = [
        ['user_id' => 5, 'status' => 'pending', 'notes' => 'No issues'],
        ['user_id' => 6, 'status' => 'pending', 'notes' => 'No issues'],
        ['user_id' => 7, 'status' => 'pending', 'notes' => 'No issues'],
        ['user_id' => 8, 'status' => 'blocked', 'notes' => 'Has overdue books and unpaid fines'],
        ['user_id' => 9, 'status' => 'blocked', 'notes' => 'Has overdue books and unpaid fines'],
    ];
    
    foreach ($clearances as $clearance) {
        $query = "INSERT INTO clearances (user_id, semester_id, status, notes) VALUES (?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->execute([$clearance['user_id'], $semester_id, $clearance['status'], $clearance['notes']]);
    }
    
    // Generate some completed transactions (returned books)
    echo "Creating completed transactions...\n";
    $completed_transactions = [
        ['user_id' => 4, 'book_id' => 1, 'borrow_date' => '2025-01-15', 'due_date' => '2025-01-29', 'return_date' => '2025-01-28'],
        ['user_id' => 5, 'book_id' => 2, 'borrow_date' => '2025-01-16', 'due_date' => '2025-01-30', 'return_date' => '2025-01-29'],
    ];
    
    foreach ($completed_transactions as $transaction) {
        // Insert borrow transaction
        $query = "INSERT INTO transactions (user_id, book_id, semester_id, transaction_type, transaction_date, due_date, status) VALUES (?, ?, ?, 'borrow', ?, ?, 'completed')";
        $stmt = $db->prepare($query);
        $stmt->execute([$transaction['user_id'], $transaction['book_id'], $semester_id, $transaction['borrow_date'], $transaction['due_date']]);
        
        // Insert return transaction
        $query = "INSERT INTO transactions (user_id, book_id, semester_id, transaction_type, transaction_date, status) VALUES (?, ?, ?, 'return', ?, 'completed')";
        $stmt = $db->prepare($query);
        $stmt->execute([$transaction['user_id'], $transaction['book_id'], $semester_id, $transaction['return_date']]);
    }
    
    $db->commit();
    
    echo "\n=== Test Data Generation Complete ===\n";
    echo "Generated test data includes:\n";
    echo "- 5 additional test students with student IDs\n";
    echo "- 5 additional test books with various categories\n";
    echo "- Recent borrows (within due date)\n";
    echo "- Overdue books with penalties\n";
    echo "- Fines for overdue books\n";
    echo "- Clearance records with different statuses\n";
    echo "- Completed transactions (returned books)\n\n";
    
    echo "Test Login Credentials:\n";
    echo "Staff: staff1 / password\n";
    echo "Librarian: librarian1 / password\n";
    echo "Students: student1-student5 / password\n";
    echo "Teachers: teacher1 / password\n\n";
    
    echo "Sample Student IDs for testing:\n";
    echo "- 1349802 (Mike Wilson)\n";
    echo "- 1349803 (Sarah Brown)\n";
    echo "- 1349804 (John Davis)\n";
    echo "- 1349805 (Emily Garcia)\n";
    echo "- 1349806 (Michael Johnson)\n\n";
    
    echo "You can now test:\n";
    echo "1. User registration with student ID\n";
    echo "2. Advanced search by student ID, name, email\n";
    echo "3. Book search by title, author, ISBN, category\n";
    echo "4. Penalty management and payment processing\n";
    echo "5. Clearance status updates\n";
    echo "6. Overdue book handling\n";
    
} catch (Exception $e) {
    $db->rollback();
    echo "Error generating test data: " . $e->getMessage() . "\n";
}
?>
