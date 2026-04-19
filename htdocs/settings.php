<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user = getUserById($conn, $_SESSION['user_id']);
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        $name = sanitizeInput($_POST['name']);
        $phone = sanitizeInput($_POST['phone']);
        $address = sanitizeInput($_POST['address']);
        
        $result = $auth->updateProfile($_SESSION['user_id'], [
            'name' => $name,
            'phone' => $phone,
            'address' => $address
        ]);
        
        if ($result['success']) {
            $success = $result['message'];
            $_SESSION['user_name'] = $name;
            $user = getUserById($conn, $_SESSION['user_id']);
        } else {
            $error = $result['message'];
        }
    }
    
    if (isset($_POST['change_password'])) {
        $current = $_POST['current_password'];
        $new = $_POST['new_password'];
        $confirm = $_POST['confirm_password'];
        
        if ($new !== $confirm) {
            $error = "New passwords do not match";
        } else {
            $result = $auth->changePassword($_SESSION['user_id'], $current, $new);
            if ($result['success']) {
                $success = $result['message'];
            } else {
                $error = $result['message'];
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Settings - RIT Bus Tracking System</title>
    <link rel="icon" href="assets/images/rit-logo-wide-1.png" type="image/png">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }
        
        .settings-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .settings-card h3 {
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ff9800;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn-secondary {
            background: #6c757d;
        }
        
        @media (max-width: 768px) {
            .settings-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container">
        <div class="college-header" style="margin-bottom: 20px; padding: 15px 25px;">
            <div class="college-info">
                <i class="fas fa-cog" style="font-size: 40px;"></i>
                <div class="college-text">
                    <h3>Account Settings</h3>
                    <p>Manage your profile and preferences</p>
                </div>
            </div>
        </div>
        
        <?php if($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="settings-grid">
            <!-- Profile Settings -->
            <div class="settings-card">
                <h3><i class="fas fa-user-circle"></i> Profile Information</h3>
                <form method="POST">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                        <small style="color: #666;">Email cannot be changed</small>
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <textarea name="address" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                    </div>
                    <button type="submit" name="update_profile">
                        <i class="fas fa-save"></i> Update Profile
                    </button>
                </form>
            </div>
            
            <!-- Password Settings -->
            <div class="settings-card">
                <h3><i class="fas fa-lock"></i> Change Password</h3>
                <form method="POST">
                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" name="current_password" required>
                    </div>
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" required>
                        <small>Minimum 6 characters</small>
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" required>
                    </div>
                    <button type="submit" name="change_password">
                        <i class="fas fa-key"></i> Change Password
                    </button>
                </form>
            </div>
            
            <!-- Preferences -->
            <div class="settings-card">
                <h3><i class="fas fa-bell"></i> Notification Preferences</h3>
                <div class="form-group">
                    <label>
                        <input type="checkbox" id="emailNotif"> 
                        <i class="fas fa-envelope"></i> Email notifications for bus updates
                    </label>
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" id="pushNotif"> 
                        <i class="fas fa-mobile-alt"></i> Push notifications for route changes
                    </label>
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" id="smsNotif"> 
                        <i class="fas fa-sms"></i> SMS alerts for bus delays
                    </label>
                </div>
                <button onclick="savePreferences()" class="btn-secondary">
                    <i class="fas fa-save"></i> Save Preferences
                </button>
            </div>
            
            <!-- About -->
            <div class="settings-card">
                <h3><i class="fas fa-info-circle"></i> About RIT Bus Tracker</h3>
                <p><strong>Version:</strong> 1.0.0</p>
                <p><strong>Institution:</strong> Ramco Institute of Technology</p>
                <p><strong>Location:</strong> Rajapalayam, Tamil Nadu - 626117</p>
                <p><strong>Accreditation:</strong> NBA Accredited Programmes</p>
                <hr>
                <p><strong>Contact:</strong></p>
                <p><i class="fas fa-phone"></i> +91-4563-123456</p>
                <p><i class="fas fa-envelope" style="font-size: 30px;color: #afe130"></i> support@ritbus.edu</p>
                <p><i class="fa-brands fa-instagram" style="font-size: 30px;color: #E1306C"></i> ritrajapalayam</p>
            </div>
        </div>
    </div>
    
    <script>
        function savePreferences() {
            const preferences = {
                email: document.getElementById('emailNotif').checked,
                push: document.getElementById('pushNotif').checked,
                sms: document.getElementById('smsNotif').checked
            };
            localStorage.setItem('userPreferences', JSON.stringify(preferences));
            alert('Preferences saved successfully!');
        }
        
        // Load saved preferences
        const savedPrefs = localStorage.getItem('userPreferences');
        if (savedPrefs) {
            const prefs = JSON.parse(savedPrefs);
            document.getElementById('emailNotif').checked = prefs.email || false;
            document.getElementById('pushNotif').checked = prefs.push || false;
            document.getElementById('smsNotif').checked = prefs.sms || false;
        }
    </script>
</body>
</html>