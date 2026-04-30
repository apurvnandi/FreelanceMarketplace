<?php
session_start();
include 'db.php';

if(!isset($_SESSION['user']) || $_SESSION['role'] != "client"){
    header("Location: login.html");
    exit();
}

$client_email = $_SESSION['user'];

// Fetch projects posted by this client and count applications for each
$query = "SELECT p.*, 
          (SELECT COUNT(*) FROM applications WHERE project_id = p.id) as app_count 
          FROM projects p 
          WHERE p.client_email = '$client_email' 
          ORDER BY p.id DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
<title>Manage Projects</title>
<link rel="stylesheet" href="css/style.css">
<style>
    .sidebar a.active { background: #2196F3; }
    .project-list-card {
        background: #fff;
        padding: 20px;
        margin-bottom: 15px;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .status-badge {
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.85em;
        text-transform: uppercase;
    }
    .status-open { background: #e3f2fd; color: #1976d2; }
    .status-active { background: #e8f5e9; color: #2e7d32; }
    .view-apps-btn {
        background: #2196F3;
        color: white;
        text-decoration: none;
        padding: 8px 15px;
        border-radius: 4px;
        font-size: 0.9em;
    }
</style>
</head>
<body>
<div class="dashboard">
    <div class="sidebar">
        <h2>Client</h2>
        <a href="client-dashboard.php">Dashboard</a>
        <a href="client-postproject.php">Post a Project</a>
        <a href="client-manageprojects.php" class="active">Manage Projects</a>
        <a href="client-hire.php">Hire Freelancer</a>
        <a href="client-profile.php">Profile</a>
        <button onclick="window.location.href='logout.php'">Logout</button>
    </div>

    <div class="main">
        <h1>My Posted Projects</h1>

        <div class="project-container">
            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <div class="project-list-card">
                        <div>
                            <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                            <p>Budget: ₹<?php echo number_format($row['budget']); ?> | 
                               <span class="status-badge status-<?php echo $row['status']; ?>">
                                   <?php echo $row['status']; ?>
                               </span>
                            </p>
                        </div>
                        <div>
                            <a href="view_applications.php?project_id=<?php echo $row['id']; ?>" class="view-apps-btn">
                                View Applications (<?php echo $row['app_count']; ?>)
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="card">
                    <p>You haven't posted any projects yet.</p>
                    <a href="client-postproject.php" style="color: #2196F3;">Post your first project here.</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
