<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Map</title>
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .bus-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 20px;
        }

        .bus-item {
            padding: 14px;
            background: white;
            border-radius: 12px;
            cursor: pointer;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: 0.3s;
            font-weight: 500;
        }

        .bus-item:hover {
            background: #ff9800;
            color: white;
            transform: translateX(5px);
        }
    </style>
</head>

<body>

<?php include 'includes/navbar.php'; ?>

<div class="container">

    <h3><i class="fas fa-map-marked-alt"></i> Live Tracking</h3>

    <div class="bus-list">

        <a href="track.php?bus=14">
            <div class="bus-item">🚌 Bus 14</div>
        </a>

        <a href="track.php?bus=15">
            <div class="bus-item">🚌 Bus 15</div>
        </a>

        <a href="track.php?bus=16">
            <div class="bus-item">🚌 Bus 16</div>
        </a>

    </div>

</div>

</body>
</html>