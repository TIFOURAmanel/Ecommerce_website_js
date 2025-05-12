<?php
session_start();
require_once('connection.php');

// Handle form submissions
$pdo = getConnection();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_product'])) {
        // Add product to database
        $stmt = $pdo->prepare("INSERT INTO products (name_prod, category_id, price, stock_quantity, description_prod , image_url) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['product_name'],
            $_POST['product_category'],
            $_POST['product_price'],
            $_POST['product_stock'],
            $_POST['product_description'],
            $_POST['product_image']
        ]);
    }

    if (isset($_POST['add_category'])) {
        // Add category to database
        $stmt = $pdo->prepare("INSERT INTO categories (name_categ, description_categ) VALUES (?, ?)");
        $stmt->execute([
            $_POST['category_name'],
            $_POST['category_description']
        ]);
    }

    if (isset($_POST['delete_product'])) {
        $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = ?");
        $stmt->execute([$_POST['product_id']]);
    }

    if (isset($_POST['delete_category'])) {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE category_id = ?");
        $stmt->execute([$_POST['category_id']]);
    }
    if (isset($_POST['delete_user'])) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->execute([$_POST['user_id']]);
    }
}

// Fetch data from database
$products = $pdo->query("SELECT p.*, c.name_categ as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id")->fetchAll(PDO::FETCH_ASSOC);
$categories = $pdo->query("SELECT c.*, COUNT(p.product_id) as product_count FROM categories c LEFT JOIN products p ON p.category_id = c.category_id GROUP BY c.category_id")->fetchAll(PDO::FETCH_ASSOC);
$users = $pdo->query("SELECT * FROM users")->fetchAll(PDO::FETCH_ASSOC);
$orders = $pdo->query("
    SELECT o.*, u.email, CONCAT(u.first_name, ' ', u.last_name) as customer_name, 
    SUM(oi.quantity * p.price) as total_amount,
    COUNT(oi.order_item_id) as item_count
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    JOIN order_items oi ON o.order_id = oi.order_id
    JOIN products p ON oi.product_id = p.product_id
    GROUP BY o.order_id
")->fetchAll(PDO::FETCH_ASSOC);

// Get counts for dashboard
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalCategories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalRevenue = $pdo->query("SELECT SUM(oi.quantity * p.price) FROM order_items oi JOIN products p ON oi.product_id = p.product_id")->fetchColumn();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="admin_Style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <i class="fas fa-store-alt"></i>
            <h3>Admin Panel</h3>
        </div>
        <ul class="sidebar-menu">
            <li class="active">
                <a href="#" onclick="showSection('dashboard-section')">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="#" onclick="showSection('products-section')">
                    <i class="fas fa-box-open"></i>
                    <span>Products</span>
                </a>
            </li>
            <li>
                <a href="#" onclick="showSection('categories-section')">
                    <i class="fas fa-list"></i>
                    <span>Categories</span>
                </a>
            </li>
            <li>
                <a href="#" onclick="showSection('orders-section')">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Orders</span>
                </a>
            </li>
            <li>
                <a href="#" onclick="showSection('users-section')">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </a>
            </li>
            <li>
                <a href="#">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Dashboard Section -->
        <div id="dashboard-section">
            <div class="header">
                <h2>Dashboard</h2>
                <div class="user-info">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="logout-container">
                            <button type="button" class="logout-btn" onclick="confirmLogout()">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </button>
                            <span class="user-name">
                                <img src="images/User.png" alt="User">
                                Admin User
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Cards Section -->
            <div class="cards">
                <div class="card">
                    <div class="card-header">
                        <h3>Total Products</h3>
                        <i class="fas fa-box-open"></i>
                    </div>
                    <div class="card-body">
                        <h2><?= $totalProducts ?></h2>
                        <p>+12 this week</p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h3>Total Categories</h3>
                        <i class="fas fa-list"></i>
                    </div>
                    <div class="card-body">
                        <h2><?= $totalCategories ?></h2>
                        <p>+1 this month</p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h3>Total Orders</h3>
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="card-body">
                        <h2><?= $totalOrders ?></h2>
                        <p>+24 today</p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h3>Total Revenue</h3>
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="card-body">
                        <h2>$12,345</h2>
                        <p>+$1,234 this week</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Section (initially hidden) -->
        <div id="products-section" style="display:none;">
            <div class="header">
                <h2>Products</h2>
                <div class="user-info">
                    <img src="images/User.png" alt="User">
                    <span>Admin User</span>
                </div>
            </div>

            <div class="table-card">
                <div class="table-header">
                    <h3>Products</h3>
                    <button class="btn" id="add-product-btn">Add Product</button>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?= $product['product_id'] ?></td>
                                <td><?= htmlspecialchars($product['name_prod']) ?></td>
                                <td><?= htmlspecialchars($product['category_name']) ?></td>
                                <td><?= number_format($product['price'], 4) ?> da</td>
                                <td><?= $product['stock_quantity'] ?></td>
                                <td>
                                    <span class="status <?= $product['stock_quantity'] > 0 ? 'active' : 'inactive' ?>">
                                        <?= $product['stock_quantity'] > 0 ? 'Active' : 'Out of Stock' ?>
                                    </span>
                                </td>
                                <td>

                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                                        <button type="submit" name="delete_product"
                                            class="action-btn delete-btn">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Categories Section (initially hidden) -->
        <div id="categories-section" style="display:none;">
            <div class="header">
                <h2>Categories</h2>
                <div class="user-info">
                    <img src="images/User.png" alt="User">
                    <span>Admin User</span>
                </div>
            </div>

            <div class="table-card">
                <div class="table-header">
                    <h3>Categories</h3>
                    <button class="btn" id="add-category-btn">Add Category</button>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Products</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><?= $category['category_id'] ?></td>
                                <td><?= htmlspecialchars($category['name_categ']) ?></td>
                                <td><?= htmlspecialchars($category['description_categ']) ?></td>
                                <td><?= $category['product_count'] ?></td>
                                <td>

                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="category_id" value="<?= $category['category_id'] ?>">
                                        <button type="submit" name="delete_category"
                                            class="action-btn delete-btn">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="orders-section" style="display:none;">
            <div class="header">
                <h2>Orders</h2>
                <div class="user-info">
                    <img src="images/User.png" alt="User">
                    <span>Admin User</span>
                </div>
            </div>

            <div class="table-card">
                <div class="table-header">
                    <h3>All Orders</h3>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?= $order['order_id'] ?></td>
                                <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                <td><?= date('M j, Y', strtotime($order['order_date'])) ?></td>
                                <td><?= number_format($order['total_amount'], 2) ?>da</td>
                                <td>
                                    <span class="status active">Completed</span>
                                </td>
                                <td>
                                    <button class="action-btn view-btn">View</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Users Section -->
        <div id="users-section" style="display:none;">
            <div class="header">
                <h2>Users</h2>
                <div class="user-info">
                    <img src="images/User.png" alt="User">
                    <span>Admin User</span>
                </div>
            </div>

            <div class="table-card">
                <div class="table-header">
                    <h3>All Users</h3>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Address</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= $user['user_id'] ?></td>
                                <td><?= htmlspecialchars($user['first_name'] . ' ' . htmlspecialchars($user['last_name'])) ?>
                                </td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= ucfirst($user['role_user']) ?></td>
                                <td><?= htmlspecialchars($user['city'] . ', ' . $user['country']) ?></td>
                                <td>

                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                        <button type="submit" name="delete_user"
                                            class="action-btn delete-btn">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </div>

    <!-- Add Product Modal -->
    <div class="modal" id="product-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New Product</h3>
                <button class="close-btn">&times;</button>
            </div>
            <form id="product-form" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="product-name">Product Name</label>
                    <input type="text" id="product-name" name="product_name" required>
                </div>
                <div class="form-group">
                    <label for="product-category">Category</label>
                    <select id="product-category" name="product_category" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['category_id'] ?>"><?= htmlspecialchars($category['name_categ']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="product-price">Price</label>
                        <input type="number" id="product-price" name="product_price" step="0.0001" required>
                    </div>
                    <div class="form-group">
                        <label for="product-stock">Stock Quantity</label>
                        <input type="number" id="product-stock" name="product_stock" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="product-description">Description</label>
                    <textarea id="product-description" name="product_description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="product-image">Image URL</label>
                    <input type="text" id="product-image" name="product_image">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn close-btn">Cancel</button>
                    <button type="submit" name="add_product" class="btn">Save Product</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div class="modal" id="category-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New Category</h3>
                <button class="close-btn">&times;</button>
            </div>
            <form id="category-form" method="POST">
                <div class="form-group">
                    <label for="category-name">Category Name</label>
                    <input type="text" id="category-name" name="category_name" required>
                </div>
                <div class="form-group">
                    <label for="category-description">Description</label>
                    <textarea id="category-description" name="category_description" rows="3"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn close-btn">Cancel</button>
                    <button type="submit" name="add_category" class="btn">Save Category</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div class="modal" id="logoutModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Logout</h3>
                <button class="close-btn" onclick="closeLogoutModal()">&times;</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to log out?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeLogoutModal()">No</button>
                <form method="post" action="sign.php" style="display: inline;">
                    <input type="hidden" name="logout" value="1">
                    <button type="submit" class="btn btn-primary">Yes</button>
                </form>
            </div>
        </div>
    </div>
    <script>
        function confirmLogout() {
            document.getElementById('logoutModal').style.display = 'flex';
        }

        function closeLogoutModal() {
            document.getElementById('logoutModal').style.display = 'none';
        }
        // Section Navigation
        function showSection(sectionId) {
            // Hide all sections
            document.querySelectorAll('.main-content > div').forEach(section => {
                section.style.display = 'none';
            });

            // Show selected section
            document.getElementById(sectionId).style.display = 'block';

            // Update active menu item
            document.querySelectorAll('.sidebar-menu li').forEach(item => {
                item.classList.remove('active');
            });
            event.currentTarget.parentElement.classList.add('active');
        }

        // Initialize to show dashboard by default
        document.addEventListener('DOMContentLoaded', function () {
            showSection('dashboard-section');
        });

        // Modal functionality
        const productModal = document.getElementById('product-modal');
        const categoryModal = document.getElementById('category-modal');
        const addProductBtn = document.getElementById('add-product-btn');
        const addCategoryBtn = document.getElementById('add-category-btn');
        const closeBtns = document.querySelectorAll('.close-btn');

        addProductBtn?.addEventListener('click', () => {
            productModal.style.display = 'flex';
        });

        addCategoryBtn?.addEventListener('click', () => {
            categoryModal.style.display = 'flex';
        });

        closeBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                productModal.style.display = 'none';
                categoryModal.style.display = 'none';
            });
        });

        window.addEventListener('click', (e) => {
            if (e.target === productModal) {
                productModal.style.display = 'none';
            }
            if (e.target === categoryModal) {
                categoryModal.style.display = 'none';
            }
        });
    </script>
</body>

</html>