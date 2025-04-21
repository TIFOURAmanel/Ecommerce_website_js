<?php
require_once ('connection.php');

// Handle form submissions
$pdo = getConnection();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_product'])) {
        // Add product to database
        $stmt = $pdo->prepare("INSERT INTO products (name_prod, category_id, price, stock_quantity, description_prod) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['product_name'],
            $_POST['product_category'],
            $_POST['product_price'],
            $_POST['product_stock'],
            $_POST['product_description']
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
}

// Fetch data from database
$products = $pdo->query("SELECT p.*, c.name_categ as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id")->fetchAll(PDO::FETCH_ASSOC);
$categories = $pdo->query("SELECT c.*, COUNT(p.product_id) as product_count FROM categories c LEFT JOIN products p ON p.category_id = c.category_id GROUP BY c.category_id")->fetchAll(PDO::FETCH_ASSOC);

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
    <link rel="stylesheet" href="adminStyle.css">
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
                <a href="#">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="#products">
                    <i class="fas fa-box-open"></i>
                    <span>Products</span>
                </a>
            </li>
            <li>
                <a href="#categories">
                    <i class="fas fa-list"></i>
                    <span>Categories</span>
                </a>
            </li>
            <li>
                <a href="#orders">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Orders</span>
                </a>
            </li>
            <li>
                <a href="#users">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </a>
            </li>
            <li>
                <a href="#settings">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h2>Dashboard</h2>
            <div class="user-info">
                <img src="images/User.png" alt="User">
                <span>Admin User</span>
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

    <!-- Tables Section -->
    <div class="tables">
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
                            <button class="action-btn edit-btn">Edit</button>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                                <button type="submit" name="delete_product" class="action-btn delete-btn">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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
                            <button class="action-btn edit-btn">Edit</button>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="category_id" value="<?= $category['category_id'] ?>">
                                <button type="submit" name="delete_category" class="action-btn delete-btn">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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
                            <option value="<?= $category['category_id'] ?>"><?= htmlspecialchars($category['name_categ']) ?></option>
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

    <script>
        // Modal functionality (same as before)
        const productModal = document.getElementById('product-modal');
        const categoryModal = document.getElementById('category-modal');
        const addProductBtn = document.getElementById('add-product-btn');
        const addCategoryBtn = document.getElementById('add-category-btn');
        const closeBtns = document.querySelectorAll('.close-btn');

        addProductBtn.addEventListener('click', () => {
            productModal.style.display = 'flex';
        });

        addCategoryBtn.addEventListener('click', () => {
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

        // Form submission handled by PHP now
    </script>
</body>
</html>