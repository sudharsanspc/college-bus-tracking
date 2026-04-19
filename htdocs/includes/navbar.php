<?php
// Navigation Bar Component with RIT Logo
?>
<div class="navbar">
    
    <!-- LOGO -->
    <div class="logo-container">
        <img src="assets/images/rit-logo-wide-1.png" 
             alt="Ramco Institute of Technology Logo" 
             class="rit-logo">
        <div class="logo-text">
            <h2>🚌 College Bus Tracker</h2>
            <p class="logo-subtitle">Real-time Bus Tracking System</p>
        </div>
    </div>

    <!-- NAV LINKS -->
    <div class="nav-links">

        <!-- HOME -->
        <a href="home.php" 
           class="nav-btn <?php echo basename($_SERVER['PHP_SELF']) == 'home.php' ? 'active' : ''; ?>">
            <i class="fas fa-home"></i> Home
        </a>

        <!-- SEARCH (LOCATION) -->
        <form method="GET" action="home.php" class="nav-search">
            <select name="location" class="search-select">
                <option value="">📍 Location</option>

                <?php
                require_once 'includes/db.php';
                $res = $conn->query("SELECT DISTINCT origin FROM routes");
                while($row = $res->fetch_assoc()) {
                    $selected = (isset($_GET['location']) && $_GET['location'] == $row['origin']) ? 'selected' : '';
                    echo "<option value='".$row['origin']."' $selected>".$row['origin']."</option>";
                }
                ?>
            </select>

            <button type="submit" class="search-btn">
                🔍
            </button>
        </form>
        
        <a href="map.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'map.php' ? 'active' : ''; ?>">
    <i class="fas fa-map"></i> Map
</a>

        <!-- SETTINGS -->
        <a href="settings.php" 
           class="nav-btn <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
            <i class="fas fa-cog"></i> Settings
        </a>

        <!-- LOGOUT -->
        <a href="logout.php" class="nav-btn">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>

    </div>

    <!-- USER -->
    <div class="user-info">
        <i class="fas fa-user-circle"></i> 
        <span><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Guest'); ?></span>
    </div>

</div>