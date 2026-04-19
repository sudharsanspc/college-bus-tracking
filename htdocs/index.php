<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

$auth = new Auth($conn);

if ($auth->isLoggedIn()) {
    header("Location: home.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);
    
    $result = $auth->login($email, $password, $remember);
    
    if ($result['success']) {
        header("Location: home.php");
        exit();
    } else {
        $error = $result['message'];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - RIT Bus Tracking System</title>
    <link rel="icon" href="assets/images/rit-logo-wide-1.png" type="image/png">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #1a237e 0%, #0d47a1 50%, #ff9800 100%);
            min-height: 100vh;
        }
        
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .login-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: slideUp 0.5s ease;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .institution-name {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }
        
        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .remember-forgot label {
            display: flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
        }
        
        .forgot-link {
            color: #ff9800;
            text-decoration: none;
        }
        
        .forgot-link:hover {
            text-decoration: underline;
        }
        
        .register-link {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .register-link a {
            color: #ff9800;
            text-decoration: none;
            font-weight: bold;
        }
        
        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-logo">
                <img src="assets/images/rit-logo-wide-1.png" 
                     alt="Ramco Institute of Technology Logo"
                     onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'200\' height=\'80\' viewBox=\'0 0 200 80\'%3E%3Crect width=\'200\' height=\'80\' fill=\'%231a237e\'/%3E%3Ctext x=\'15\' y=\'30\' font-size=\'14\' fill=\'%23ff9800\'%3ERamco Institute%3C/text%3E%3Ctext x=\'15\' y=\'48\' font-size=\'12\' fill=\'%23ffffff\'%3Eof Technology%3C/text%3E%3Ctext x=\'15\' y=\'62\' font-size=\'9\' fill=\'%23ff9800\'%3ERajapalayam%3C/text%3E%3C/svg%3E'">
                <h1>🚌 Bus Tracking System</h1>
                <p class="institution-name">(An Autonomous Institution) | Approved by AICTE, New Delhi</p>
                <p class="institution-name">Affiliated to Anna University | NBA Accredited</p>
            </div>
            
            <h3 style="text-align: center; color: #666; margin-bottom: 25px;">Login to Track Your Bus</h3>
            
            <?php if($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
    <label><i class="fas fa-envelope"></i> Email Address</label>
    <input type="email" name="email" required placeholder="Enter your email">
</div>

<div class="form-group">
    <label><i class="fas fa-lock"></i> Password</label>
    <input type="password" name="password" required placeholder="Enter your password">
</div>
                
                <div class="remember-forgot">
                    <label>
                        <input type="checkbox" name="remember"> Remember Me
                    </label>
                    <a href="forgot-password.php" class="forgot-link">Forgot Password?</a>
                </div>
                
                <button type="submit">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>
            
            <div class="register-link">
                Don't have an account? <a href="register.php">Create Account</a>
            </div>
            
            <div style="margin-top: 20px; text-align: center; font-size: 11px; color: #999;">
                <p>NBA Accredited Programmes: CSE, EEE, ECE, MECH & CIVIL</p>
            </div>
        </div>
    </div>
</body>
</html>