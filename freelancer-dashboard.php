<?php
session_start();
include 'db.php'; 

if(!isset($_SESSION['user']) || $_SESSION['role'] != "freelancer"){
    header("Location: login.html");
    exit();
}

$user_email = $_SESSION['user']; // Using the email stored in session to match your table style

// 1. Fetch Active Projects Count (Filtering by assigned freelancer)
$active_query = "SELECT COUNT(*) as count FROM projects WHERE freelancer_email = '$user_email' AND status = 'active'";
$active_res = mysqli_query($conn, $active_query);
$active_count = mysqli_fetch_assoc($active_res)['count'] ?? 0;

// 2. Fetch Total Earnings from completed payments
$earnings_query = "SELECT SUM(amount) as total FROM payments WHERE freelancer_email = '$user_email'";
$earnings_res = mysqli_query($conn, $earnings_query);
$total_earnings = mysqli_fetch_assoc($earnings_res)['total'] ?? 0;

// 3. Fetch Completed Projects Count
$completed_query = "SELECT COUNT(*) as count FROM projects WHERE freelancer_email = '$user_email' AND status = 'completed'";
$completed_res = mysqli_query($conn, $completed_query);
$completed_count = mysqli_fetch_assoc($completed_res)['count'] ?? 0;

// 4. Fetch Recent Activity
$activity_query = "SELECT title, status FROM projects WHERE freelancer_email = '$user_email' ORDER BY id DESC LIMIT 3";
$activity_res = mysqli_query($conn, $activity_query);
?>

<!DOCTYPE html>
<html>
<head>
<title>Freelancer Dashboard</title>
<link rel="stylesheet" href="css/style.css">

<style>
.sidebar a.active {
    background: #4CAF50;
}
</style>

</head>

<body>

<div class="dashboard">

<!-- ✅ SIDEBAR (CONSISTENT WITH CLIENT) -->
<div class="sidebar">
<h2>Freelancer</h2>

<a href="freelancer-dashboard.php" class="active">Dashboard</a>
<a href="freelancer-projects.php">Browse Projects</a>
<a href="freelancer-profile.php">Profile</a>
<a href="freelancer-reviews.php">Reviews</a>
<a href="freelancer-payments.php">Payments</a>

<button onclick="window.location.href='logout.php'">Logout</button>
</div>

<!-- ✅ MAIN -->
<div class="main">

<h1>Welcome <?php echo htmlspecialchars($_SESSION['user']); ?></h1>

<div class="cards">

<div class="card">
<h3>Active Projects</h3>
<p><?php echo $active_count; ?></p>
</div>

<div class="card">
<h3>Total Earnings</h3>
<p>₹<?php echo number_format($total_earnings, 2); ?></p>
</div>

<div class="card">
<h3>Completed Projects</h3>
<p><?php echo $completed_count; ?></p>
</div>

<div class="card">
<h3>Recent Activity</h3>
<p>
    <?php 
    if($activity_res && mysqli_num_rows($activity_res) > 0) {
        while($row = mysqli_fetch_assoc($activity_res)) {
            echo htmlspecialchars($row['title']) . " (" . ucfirst($row['status']) . ")<br>";
        }
    } else {
        echo "No activity yet";
    }
    ?>
</p>
</div>

</div>

</div>

</div>

<!-- ✅ ADD SCRIPT FOR FUTURE FEATURES -->
<script src="js/script.js"></script>

</body>
</html>
