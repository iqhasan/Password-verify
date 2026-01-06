<?php
session_start();

// Configuration - প্রোডাকশনে এনভায়রনমেন্ট ভেরিয়েবল ব্যবহার করুন
$correct_password = "SecurePass123!";
$max_attempts = 3;
$lockout_time = 300; // 5 minutes in seconds

// Initialize session variables
if (!isset($_SESSION['attempts'])) {
    $_SESSION['attempts'] = 0;
    $_SESSION['locked_until'] = 0;
}

// Handle form submission
$message = "";
$message_type = "";
$is_locked = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    $current_time = time();
    
    // Check if locked
    if ($_SESSION['locked_until'] > $current_time) {
        $remaining = $_SESSION['locked_until'] - $current_time;
        $message = "Too many failed attempts. Try again in " . ceil($remaining / 60) . " minutes.";
        $message_type = "error";
        $is_locked = true;
    } else {
        // Reset lock if time has passed
        if ($_SESSION['attempts'] >= $max_attempts && $_SESSION['locked_until'] <= $current_time) {
            $_SESSION['attempts'] = 0;
            $_SESSION['locked_until'] = 0;
        }
        
        $user_password = $_POST['password'];
        
        if ($user_password === $correct_password) {
            // Successful login
            $_SESSION['attempts'] = 0;
            $_SESSION['locked_until'] = 0;
            $message = "✅ Password verified successfully!";
            $message_type = "success";
        } else {
            // Failed attempt
            $_SESSION['attempts']++;
            
            if ($_SESSION['attempts'] >= $max_attempts) {
                $_SESSION['locked_until'] = $current_time + $lockout_time;
                $message = "Too many failed attempts. Account locked for 5 minutes.";
                $message_type = "error";
                $is_locked = true;
            } else {
                $remaining_attempts = $max_attempts - $_SESSION['attempts'];
                $message = "❌ Incorrect password. " . $remaining_attempts . " attempts remaining.";
                $message_type = "error";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Verifier</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <i class="fas fa-lock"></i>
                <h1>Password Verification System</h1>
                <p>Enter the password to verify access</p>
            </div>
            
            <?php if ($message): ?>
                <div class="message <?php echo $message_type; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-key"></i> Password
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Enter password"
                        required
                        <?php echo $is_locked ? 'disabled' : ''; ?>
                    >
                    <div class="password-toggle">
                        <i class="fas fa-eye" id="togglePassword"></i>
                    </div>
                </div>
                
                <div class="attempts-info">
                    <i class="fas fa-shield-alt"></i>
                    Attempts: <?php echo $_SESSION['attempts']; ?>/<?php echo $max_attempts; ?>
                </div>
                
                <button type="submit" class="submit-btn" <?php echo $is_locked ? 'disabled' : ''; ?>>
                    <i class="fas fa-check-circle"></i> Verify Password
                </button>
                
                <?php if ($is_locked): ?>
                    <div class="lock-info">
                        <i class="fas fa-clock"></i>
                        Account locked. Try again later.
                    </div>
                <?php endif; ?>
            </form>
            
            <div class="footer">
                <div class="security-tips">
                    <h3><i class="fas fa-lightbulb"></i> Security Tips:</h3>
                    <ul>
                        <li>Use strong passwords with mixed characters</li>
                        <li>Don't share your password with anyone</li>
                        <li>Default password: <code>SecurePass123!</code></li>
                    </ul>
                </div>
                
                <div class="server-info">
                    <p><i class="fas fa-server"></i> Server: Render PHP Web Service</p>
                    <p><i class="fas fa-code"></i> PHP Version: <?php echo phpversion(); ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Toggle password visibility
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');
        
        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
        
        // Auto-focus on password field
        window.onload = function() {
            if (!password.disabled) {
                password.focus();
            }
        };
    </script>
</body>
</html>
