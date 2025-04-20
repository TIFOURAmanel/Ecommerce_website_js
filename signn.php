<?php
session_start();
require_once('connection.php'); // File with db connection details

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['signin'])) {
        handleSignIn();
    } elseif (isset($_POST['signup'])) {
        handleSignUp();
    }
}

function handleSignIn() {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password']; // We'll verify the hash
    
    // Validate inputs
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Please fill in all fields";
        return;
    }
    
    // Check user credentials in database
    $conn = getConnection();
    $stmt = oci_parse($conn, "SELECT user_id, password_hash, first_name, is_admin FROM users WHERE email = :email");
    oci_bind_by_name($stmt, ':email', $email);
    oci_execute($stmt);
    
    if ($user = oci_fetch_assoc($stmt)) {
        if (password_verify($password, $user['PASSWORD_HASH'])) {
            // Successful login
            $_SESSION['user_id'] = $user['USER_ID'];
            $_SESSION['email'] = $email;
            $_SESSION['first_name'] = $user['FIRST_NAME'];
            $_SESSION['is_admin'] = $user['IS_ADMIN'];
            
            // Set "remember me" cookie if checked
            if (isset($_POST['remember'])) {
                $token = bin2hex(random_bytes(32));
                $expiry = time() + 60*60*24*30; // 30 days
                
                setcookie('remember_token', $token, [
                    'expires' => $expiry,
                    'path' => '/',
                    'domain' => $_SERVER['HTTP_HOST'],
                    'secure' => true,
                    'httponly' => true,
                    'samesite' => 'Strict'
                ]);
                
                // Store hashed token in database
                $hashedToken = password_hash($token, PASSWORD_BCRYPT);
                $updateStmt = oci_parse($conn, "UPDATE users SET remember_token = :token WHERE user_id = :user_id");
                oci_bind_by_name($updateStmt, ':token', $hashedToken);
                oci_bind_by_name($updateStmt, ':user_id', $user['USER_ID']);
                oci_execute($updateStmt);
            }
            
            header("Location: dashboard.php");
            exit();
        }
    }
    
    $_SESSION['error'] = "Invalid email or password";
    oci_close($conn);
}

function handleSignUp() {
    // Collect and sanitize inputs
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $firstName = htmlspecialchars($_POST['firstname']);
    $lastName = htmlspecialchars($_POST['lastname']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm'];
    $address = htmlspecialchars($_POST['address']);
    $city = htmlspecialchars($_POST['city']);
    $country = htmlspecialchars($_POST['country']);
    $phone = htmlspecialchars($_POST['phone']);
    
    // Validate inputs
    if (empty($email) || empty($firstName) || empty($lastName) || empty($password) || empty($confirmPassword)) {
        $_SESSION['error'] = "Please fill in all required fields";
        return;
    }
    
    if ($password !== $confirmPassword) {
        $_SESSION['error'] = "Passwords do not match";
        return;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format";
        return;
    }
    
    // Hash password
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);
    
    // Call the Oracle procedure
    $conn = getOracleConnection();
    $stmt = oci_parse($conn, "BEGIN add_user(:email, :first_name, :last_name, :password_hash, :address, :city, :country, :phone, 0, :user_id, :status, :message); END;");
    
    // Bind parameters
    oci_bind_by_name($stmt, ':email', $email);
    oci_bind_by_name($stmt, ':first_name', $firstName);
    oci_bind_by_name($stmt, ':last_name', $lastName);
    oci_bind_by_name($stmt, ':password_hash', $passwordHash);
    oci_bind_by_name($stmt, ':address', $address);
    oci_bind_by_name($stmt, ':city', $city);
    oci_bind_by_name($stmt, ':country', $country);
    oci_bind_by_name($stmt, ':phone', $phone);
    
    // Bind output parameters
    oci_bind_by_name($stmt, ':user_id', $userId, 32);
    oci_bind_by_name($stmt, ':status', $status, 1);
    oci_bind_by_name($stmt, ':message', $message, 200);
    
    oci_execute($stmt);
    
    if ($status == 1) {
        // Registration successful - log user in
        $_SESSION['user_id'] = $userId;
        $_SESSION['email'] = $email;
        $_SESSION['first_name'] = $firstName;
        $_SESSION['is_admin'] = 0;
        
        header("Location: dashboard.php");
        exit();
    } else {
        $_SESSION['error'] = $message;
    }
    
    oci_close($conn);
}

// Check for remember me cookie
function checkRememberMe() {
    if (empty($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
        $conn = getOracleConnection();
        $stmt = oci_parse($conn, "SELECT user_id, email, first_name, is_admin FROM users WHERE remember_token = :token");
        
        $hashedToken = password_hash($_COOKIE['remember_token'], PASSWORD_BCRYPT);
        oci_bind_by_name($stmt, ':token', $hashedToken);
        oci_execute($stmt);
        
        if ($user = oci_fetch_assoc($stmt)) {
            $_SESSION['user_id'] = $user['USER_ID'];
            $_SESSION['email'] = $user['EMAIL'];
            $_SESSION['first_name'] = $user['FIRST_NAME'];
            $_SESSION['is_admin'] = $user['IS_ADMIN'];
        }
        
        oci_close($conn);
    }
}

checkRememberMe();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In / Sign Up</title>
    <link rel="stylesheet" href="signStyle.css"/>
</head>
<body>
    <div class="auth-container">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error']); ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <div class="form-toggle">
            <button class="toggle-btn active" id="signin-toggle">Sign In</button>
            <button class="toggle-btn" id="signup-toggle">Sign Up</button>
        </div>

        <form class="auth-form" id="signin-form" method="POST" action="auth.php">
            <input type="hidden" name="signin" value="1">
            <h2 class="auth-title">Welcome Back</h2>
            
            <div class="input-group">
                <label for="signin-email">Email</label>
                <input type="email" id="signin-email" name="email" placeholder="Enter your email" required>
            </div>
            
            <div class="input-group">
                <label for="signin-password">Password</label>
                <input type="password" id="signin-password" name="password" placeholder="Enter your password" required>
            </div>
            
            <div class="input-group remember-me">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Remember me</label>
            </div>
            
            <button type="submit" class="auth-button">Sign In</button>
            
            <div class="form-footer">
                <a href="forgot_password.php">Forgot password?</a>
            </div>
        </form>

        <form class="auth-form" id="signup-form" style="display: none;" method="POST" action="auth.php">
            <input type="hidden" name="signup" value="1">
            <h2 class="auth-title">Create Account</h2>
            
            <div class="name-row">
                <div class="input-group">
                    <label for="signup-firstname">First Name</label>
                    <input type="text" id="signup-firstname" name="firstname" placeholder="Enter your first name" required>
                </div>
                
                <div class="input-group">
                    <label for="signup-lastname">Last Name</label>
                    <input type="text" id="signup-lastname" name="lastname" placeholder="Enter your last name" required>
                </div>
            </div>
            
            <div class="input-group">
                <label for="signup-email">Email</label>
                <input type="email" id="signup-email" name="email" placeholder="Enter your email" required>
            </div>
            
            <div class="input-group">
                <label for="signup-password">Password</label>
                <input type="password" id="signup-password" name="password" placeholder="Create a password" required>
            </div>
            
            <div class="input-group">
                <label for="signup-confirm">Confirm Password</label>
                <input type="password" id="signup-confirm" name="confirm" placeholder="Confirm your password" required>
            </div>
            
            <div class="input-group">
                <label for="signup-address">Address</label>
                <input type="text" id="signup-address" name="address" placeholder="Enter your street address">
            </div>
            
            <div class="name-row">
                <div class="input-group">
                    <label for="signup-city">City</label>
                    <input type="text" id="signup-city" name="city" placeholder="Enter your city">
                </div>
                
                <div class="input-group">
                    <label for="signup-country">Country</label>
                    <input type="text" id="signup-country" name="country" placeholder="Enter your country">
                </div>
            </div>
            
            <div class="input-group">
                <label for="signup-phone">Phone Number</label>
                <input type="tel" id="signup-phone" name="phone" placeholder="Enter your phone number">
            </div>
            
            <button type="submit" class="auth-button">Sign Up</button>
            
            <div class="form-footer">
                Already have an account? <a href="#" id="switch-to-signin">Sign In</a>
            </div>
        </form>
    </div>

    <script>
        // Toggle between Sign In and Sign Up forms
        document.getElementById('signin-toggle').addEventListener('click', function() {
            document.getElementById('signin-form').style.display = 'flex';
            document.getElementById('signup-form').style.display = 'none';
            document.getElementById('signin-toggle').classList.add('active');
            document.getElementById('signup-toggle').classList.remove('active');
        });
    
        document.getElementById('signup-toggle').addEventListener('click', function() {
            document.getElementById('signin-form').style.display = 'none';
            document.getElementById('signup-form').style.display = 'flex';
            document.getElementById('signin-toggle').classList.remove('active');
            document.getElementById('signup-toggle').classList.add('active');
        });
    
        document.getElementById('switch-to-signin').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('signin-toggle').click();
        });
    </script>
</body>
</html>