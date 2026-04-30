<?php
session_start();
include 'db.php';

// Security: Ensure only clients can access this page
if(!isset($_SESSION['user']) || $_SESSION['role'] != "client"){
    header("Location: login.html");
    exit();
}

$client_email = $_SESSION['user'];

// Fetch projects that have a freelancer assigned (Hired status)
$query = "SELECT p.*, f.name as freelancer_name 
          FROM projects p 
          JOIN freelancers f ON p.freelancer_email = f.email 
          WHERE p.client_email = '$client_email' AND p.status = 'active'";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
<title>Hire Freelancer</title>
<link rel="stylesheet" href="css/style.css">
<style>
    .sidebar a.active {
        background: #2196F3;
    }
    .hired-card {
        background: #fff;
        padding: 20px;
        margin-bottom: 15px;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        border-left: 5px solid #4CAF50;
    }
    .contact-info {
        color: #666;
        font-size: 0.9em;
    }
    .pay-btn {
        background: #2196F3;
        color: white;
        border: none;
        padding: 10px 18px;
        border-radius: 4px;
        cursor: pointer;
        font-weight: bold;
    }
    .pay-btn:hover { background: #1976D2; }
</style>
</head>
<body>
<div class="dashboard"> <!-- Matches your sidebar structure from previous files -->
    <div class="sidebar">
        <h2>Client</h2>
        <a href="client-dashboard.php">Dashboard</a>
        <a href="client-postproject.php">Post Project</a>
        <a href="client-manageprojects.php">Manage Projects</a>
        <a href="client-hire.php" class="active">Hire Freelancer</a>
        <a href="client-profile.php">Profile</a>
        <button onclick="window.location.href='logout.php'">Logout</button>
    </div>

    <div class="main">
        <h1>Active Hires</h1>
        <?php if(isset($_GET['status']) && $_GET['status'] === 'hired'): ?>
            <div class="card" style="color: #2e7d32;">Freelancer hired successfully.</div>
        <?php endif; ?>
        <?php if(isset($_GET['payment']) && $_GET['payment'] === 'paid'): ?>
            <div class="card" style="color: #2e7d32;">Payment completed successfully.</div>
        <?php elseif(isset($_GET['payment']) && $_GET['payment'] === 'already_paid'): ?>
            <div class="card" style="color: #1976d2;">This project has already been paid.</div>
        <?php elseif(isset($_GET['payment']) && $_GET['payment'] === 'invalid'): ?>
            <div class="card" style="color: #c62828;">Payment could not be processed for that project.</div>
        <?php endif; ?>
        
        <div class="hired-container">
            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <div class="hired-card">
                        <h3>Project: <?php echo htmlspecialchars($row['title']); ?></h3>
                        <p><strong>Hired Professional:</strong> <?php echo htmlspecialchars($row['freelancer_name']); ?></p>
                        <p class="contact-info">Email: <?php echo htmlspecialchars($row['freelancer_email']); ?></p>
                        <p>Budget: ₹<?php echo number_format($row['budget']); ?></p>
                        <form action="process_payment.php" method="POST">
                            <input type="hidden" name="project_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" class="pay-btn">Pay Freelancer</button>
                        </form>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="card">
                    <p>No active hires yet.</p>
                    <a href="client-manageprojects.php" style="color: #2196F3;">View applications to hire a freelancer.</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="js/script.js"></script>
</body>
</html>
