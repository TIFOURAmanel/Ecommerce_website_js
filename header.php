<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="header.css"> <!-- Changed path to css folder -->
</head>
<body>
<header class="header">
    <div class="header-container">
        <!-- Added link around logo for better UX -->
        <a href="landingPage.php" class="logo-link">
            <img src="images/MeubleConfort.png" alt="Meuble Confort logo" class="logo">
        </a>

        <!-- Reorganized nav structure for better semantics -->
        <nav class="main-nav">
            <ul class="nav-list">
                <li class="nav-item"><a href="landingPage.php#catalog" class="nav-link">Home</a></li>
                <li class="nav-item"><a href="landingPage.php#catalog" class="nav-link">Catalog</a></li>
                <li class="nav-item"><a href="landingPage.php#us" class="nav-link">About Us</a></li>
                <li class="nav-item"><a href="basket.php" class="nav-link">Basket</a></li>
                <li class="nav-item"><a href="my_orders.php" class="nav-link">Orders</a></li> <!-- Changed class for consistency -->

                <?php if(isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <form method="post" action="sign.php" id="logoutForm" class="logout-form">
                            <button type="button" class="nav-link logout-btn" onclick="confirmLogout()">Logout</button>
                            <input type="hidden" name="logout" value="1">
                        </form>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>

    <!-- Logout Confirmation Modal - Improved structure -->
    <div class="logout-modal" id="logoutModal">
        <div class="logout-modal-content">
            <p>Are you sure you want to log out?</p>
            <div class="logout-modal-buttons">
                <button type="button" class="modal-btn confirm-btn" onclick="document.getElementById('logoutForm').submit()">Yes</button>
                <button type="button" class="modal-btn cancel-btn" onclick="closeLogoutModal()">No</button>
            </div>
        </div>
    </div>
</header>

<script>
function confirmLogout() {
    document.getElementById('logoutModal').style.display = 'flex';
}

function closeLogoutModal() {
    document.getElementById('logoutModal').style.display = 'none';
}
</script>
</body>
</html>