<?php
session_start();
include 'db.php';

if(!isset($_SESSION['user']) || $_SESSION['role'] != "freelancer"){
    header("Location: login.html");
    exit();
}

$freelancer_email = $_SESSION['user'];

$summary_stmt = $conn->prepare("SELECT COALESCE(SUM(amount), 0) AS total, COUNT(*) AS count FROM payments WHERE freelancer_email = ?");
$summary_stmt->bind_param("s", $freelancer_email);
$summary_stmt->execute();
$summary = $summary_stmt->get_result()->fetch_assoc();
$summary_stmt->close();

$payments_stmt = $conn->prepare("
    SELECT pay.*, p.title
    FROM payments pay
    JOIN projects p ON p.id = pay.project_id
    WHERE pay.freelancer_email = ?
    ORDER BY pay.paid_at DESC
");
$payments_stmt->bind_param("s", $freelancer_email);
$payments_stmt->execute();
$payments = $payments_stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
<title>Payments</title>
<link rel="stylesheet" href="css/style.css">
<style>
.sidebar a.active { background: #4CAF50; }
.payment-card {
    background: #fff;
    padding: 20px;
    margin-bottom: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    border-left: 5px solid #4CAF50;
}
.payment-meta { color: #666; font-size: 0.9em; }
.paid-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 20px;
    background: #e8f5e9;
    color: #2e7d32;
    font-size: 0.85em;
    text-transform: uppercase;
}
</style>
</head>
<body>
<div class="dashboard">
<div class="sidebar">
<h2>Freelancer</h2>
<a href="freelancer-dashboard.php">Dashboard</a>
<a href="freelancer-projects.php">Projects</a>
<a href="freelancer-payments.php" class="active">Payments</a>
<a href="freelancer-reviews.php">Reviews</a>
<a href="freelancer-profile.php">Profile</a>
<button onclick="window.location.href='logout.php'">Logout</button>
</div>

<div class="main">
<h1>Payments</h1>

<div class="card">
    <h3>Total Earnings</h3>
    <p>₹<?php echo number_format($summary['total'], 2); ?></p>
    <p><?php echo (int) $summary['count']; ?> payment(s) received</p>
</div>

<?php if(mysqli_num_rows($payments) > 0): ?>
    <?php while($payment = mysqli_fetch_assoc($payments)): ?>
        <div class="payment-card">
            <h3><?php echo htmlspecialchars($payment['title']); ?></h3>
            <p><strong>Amount:</strong> ₹<?php echo number_format($payment['amount'], 2); ?></p>
            <p class="payment-meta"><strong>Client:</strong> <?php echo htmlspecialchars($payment['client_email']); ?></p>
            <p class="payment-meta"><strong>Paid on:</strong> <?php echo htmlspecialchars($payment['paid_at']); ?></p>
            <span class="paid-badge"><?php echo htmlspecialchars($payment['status']); ?></span>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <div class="card">No payments yet.</div>
<?php endif; ?>

</div>
</div>
<script src="js/script.js"></script>
</body>
</html>
