<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meuble Confort</title>
    <link rel="stylesheet" href="landingStyle.css">
   <style>
    /* Logout Modal Styles */
.logout-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    z-index: 2000;
    justify-content: center;
    align-items: center;
}

.logout-modal-content {
    background: white;
    padding: 2rem;
    border-radius: var(--border-radius);
    text-align: center;
    max-width: 400px;
    width: 90%;
}

.logout-modal-buttons {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin-top: 1.5rem;
}

.logout-modal-buttons button {
    padding: 0.5rem 1.5rem;
    border-radius: var(--border-radius);
    cursor: pointer;
    border: none;
}

.logout-modal-buttons button:first-child {
    background-color: var(--primary-color);
    color: white;
}

.logout-modal-buttons button:last-child {
    background-color: var(--gray-light);
    color: var(--text-dark);
}

.logout-btn {
    background: none;
    border: none;
    font: inherit;
    cursor: pointer;
    color: var(--primary-color);
    padding: 0;
}

    @media (max-width: 768px) {
        .modal-content {
            grid-template-columns: 1fr;
        }
        .modal-image {
            max-height: 300px;
        }
    }
   </style> 
</head>
<body>
<header class="header">
    <div class="header-container">
        <img src="images/MeubleConfort.png" alt="Meuble Confort logo" class="logo">

        <nav class="main-nav">
            <ul class="nav-list">
                <li class="nav-item"><a href="landingPage.php#catalog" class="nav-link">Home</a></li>
                <li class="nav-item"><a href="landingPage.php#catalog" class="nav-link">Catalog</a></li>
                <li class="nav-item"><a href="landingPage.php#us" class="nav-link">About Us</a></li>
                <li class="nav-item"><a href="basket.php" class="nav-link">Basket</a></li>
                <li class="nav-item"><a href="my_orders.php" class="btn btn-primary">Orders</a></li>

                <?php if(isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <form method="post" action="sign.php" id="logoutForm" style="display: inline;">
                            <button type="button" class="nav-link logout-btn" onclick="confirmLogout()">Logout</button>
                            <input type="hidden" name="logout" value="1">
                        </form>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
    <!-- Logout Confirmation Modal -->
    <div class="logout-modal" id="logoutModal">
    <div class="logout-modal-content">
        <p>Are you sure you want to log out?</p>
        <div class="logout-modal-buttons">
            <!-- Changed to submit the form instead of direct redirect -->
            <button type="button" onclick="document.getElementById('logoutForm').submit()">Yes</button>
            <button type="button" onclick="closeLogoutModal()">No</button>
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

function logout() {
    window.location.href = 'sign.php';
}
</script>
</body>
</html>
