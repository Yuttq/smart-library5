<?php
session_start();
require_once '../classes/Authentication.php';
require_once '../classes/BookManager.php';

// Check if user is logged in and is a librarian
Authentication::requireRole('librarian');

$auth = new Authentication();
$database = new Database();
$db = $database->getConnection();
$bookManager = new BookManager($db);

// Handle book operations
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add_book':
            $bookData = [
                'title' => $_POST['title'] ?? '',
                'author' => $_POST['author'] ?? '',
                'isbn' => $_POST['isbn'] ?? '',
                'category' => $_POST['category'] ?? '',
                'price' => $_POST['price'] ?? ''
            ];
            
            if (!empty($bookData['title']) && !empty($bookData['author'])) {
                $result = $bookManager->addBook($bookData);
                
                if ($result['success']) {
                    $success_message = $result['message'];
                } else {
                    $error_message = $result['errors']['general'];
                }
            } else {
                $error_message = "Title and Author are required fields.";
            }
            break;
            
        case 'update_book':
            $book_id = $_POST['book_id'] ?? '';
            $bookData = [
                'title' => $_POST['title'] ?? '',
                'author' => $_POST['author'] ?? '',
                'isbn' => $_POST['isbn'] ?? '',
                'category' => $_POST['category'] ?? '',
                'price' => $_POST['price'] ?? ''
            ];
            
            if (!empty($book_id) && !empty($bookData['title']) && !empty($bookData['author'])) {
                $result = $bookManager->updateBook($book_id, $bookData);
                
                if ($result['success']) {
                    $success_message = $result['message'];
                } else {
                    $error_message = $result['errors']['general'];
                }
            } else {
                $error_message = "Book ID, Title and Author are required fields.";
            }
            break;
            
        case 'archive_book':
            $book_id = $_POST['book_id'] ?? '';
            if (!empty($book_id)) {
                $result = $bookManager->archiveBook($book_id);
                
                if ($result['success']) {
                    $success_message = $result['message'];
                } else {
                    $error_message = $result['errors']['general'];
                }
            }
            break;

        case 'unarchive_book':
            $book_id = $_POST['book_id'] ?? '';
            if (!empty($book_id)) {
                $result = $bookManager->unarchiveBook($book_id);
                
                if ($result['success']) {
                    $success_message = $result['message'];
                } else {
                    $error_message = $result['errors']['general'];
                }
            }
            break;
    }
}

// Get all books
$books = $bookManager->getAllBooks();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Librarian Dashboard - Smart Library</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.2.1/dist/flowbite.min.css" rel="stylesheet" />
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-semibold text-gray-800">Smart Library - Librarian Dashboard</h1>
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

        <!-- Quick Actions -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <div class="flex justify-between items-center">
                <h2 class="text-lg font-medium text-gray-900">Quick Actions</h2>
                <div class="space-x-4">
                    <a href="../admin/user_management.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        Manage Users
                    </a>
                </div>
            </div>
        </div>

        <!-- Add Book Form -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Add New Book</h2>
            <form method="POST" class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <input type="hidden" name="action" value="add_book">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                    <input type="text" name="title" id="title" required 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label for="author" class="block text-sm font-medium text-gray-700">Author</label>
                    <input type="text" name="author" id="author" required 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label for="isbn" class="block text-sm font-medium text-gray-700">ISBN</label>
                    <input type="text" name="isbn" id="isbn" 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
                    <input type="text" name="category" id="category" 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700">Price</label>
                    <input type="number" step="0.01" min="0" name="price" id="price" 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                <div class="sm:col-span-3">
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                        Add Book
                    </button>
                </div>
            </form>
        </div>

        <!-- Books List -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">Book Inventory</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Author</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ISBN</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($books as $book): ?>
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
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo htmlspecialchars($book['category']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo $book['price'] !== null ? '₱' . number_format((float)$book['price'], 2) : '—'; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    <?php 
                                        $statusClass = 'bg-blue-100 text-blue-800';
                                        if ($book['status'] === 'available') { $statusClass = 'bg-green-100 text-green-800'; }
                                        elseif ($book['status'] === 'borrowed') { $statusClass = 'bg-yellow-100 text-yellow-800'; }
                                        elseif ($book['status'] === 'archived') { $statusClass = 'bg-gray-200 text-gray-700'; }
                                        echo $statusClass;
                                    ?>">
                                    <?php echo ucfirst($book['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button onclick="editBook(<?php echo $book['id']; ?>, '<?php echo htmlspecialchars($book['title']); ?>', '<?php echo htmlspecialchars($book['author']); ?>', '<?php echo htmlspecialchars($book['isbn']); ?>', '<?php echo htmlspecialchars($book['category']); ?>', '<?php echo htmlspecialchars($book['price']); ?>')" 
                                        class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                                <?php if ($book['status'] !== 'archived'): ?>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="action" value="archive_book">
                                    <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                    <button type="submit" class="text-red-600 hover:text-red-900">Archive</button>
                                </form>
                                <?php else: ?>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="action" value="unarchive_book">
                                    <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                    <button type="submit" class="text-green-600 hover:text-green-900">Unarchive</button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Book Modal -->
    <div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Edit Book</h3>
                <form method="POST" id="editForm">
                    <input type="hidden" name="action" value="update_book">
                    <input type="hidden" name="book_id" id="edit_book_id">
                    <div class="mb-4">
                        <label for="edit_title" class="block text-sm font-medium text-gray-700">Title</label>
                        <input type="text" name="title" id="edit_title" required 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                    <div class="mb-4">
                        <label for="edit_author" class="block text-sm font-medium text-gray-700">Author</label>
                        <input type="text" name="author" id="edit_author" required 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                    <div class="mb-4">
                        <label for="edit_isbn" class="block text-sm font-medium text-gray-700">ISBN</label>
                        <input type="text" name="isbn" id="edit_isbn" 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                    <div class="mb-4">
                        <label for="edit_category" class="block text-sm font-medium text-gray-700">Category</label>
                        <input type="text" name="category" id="edit_category" 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                    <div class="mb-4">
                        <label for="edit_price" class="block text-sm font-medium text-gray-700">Price</label>
                        <input type="number" step="0.01" min="0" name="price" id="edit_price" 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeEditModal()" 
                                class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">Cancel</button>
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Modal removed; using archive instead -->

    <script>
        function editBook(id, title, author, isbn, category, price) {
            document.getElementById('edit_book_id').value = id;
            document.getElementById('edit_title').value = title;
            document.getElementById('edit_author').value = author;
            document.getElementById('edit_isbn').value = isbn;
            document.getElementById('edit_category').value = category || '';
            document.getElementById('edit_price').value = price || '';
            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        // Archive handled via inline form buttons
    </script>
</body>
</html>
