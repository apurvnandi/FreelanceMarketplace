<?php
session_start();
include 'db.php';

if(!isset($_SESSION['user']) || $_SESSION['role'] != "client"){
    header("Location: login.html");
    exit();
}

$client_email = $_SESSION['user'];

$client_stmt = $conn->prepare("SELECT name FROM clients WHERE email = ?");
$client_stmt->bind_param("s", $client_email);
$client_stmt->execute();
$client_data = $client_stmt->get_result()->fetch_assoc();
$client_stmt->close();
$client_name = $client_data['name'] ?? ($_SESSION['name'] ?? $client_email);
$client_initial = strtoupper(substr($client_name, 0, 1));

// 1. Fetch Total Projects Posted by this client
$project_count_query = "SELECT COUNT(*) as total FROM projects WHERE client_email = '$client_email'";
$project_count_res = mysqli_query($conn, $project_count_query);
$total_projects = mysqli_fetch_assoc($project_count_res)['total'] ?? 0;

// 2. Fetch Total Applications received across all projects owned by this client
$app_count_query = "SELECT COUNT(a.id) as total 
                    FROM applications a 
                    JOIN projects p ON a.project_id = p.id 
                    WHERE p.client_email = '$client_email'";
$app_count_res = mysqli_query($conn, $app_count_query);
$total_apps = mysqli_fetch_assoc($app_count_res)['total'] ?? 0;

// 3. Fetch Recent Applications (Last 5) to display in the dashboard
$recent_apps_query = "SELECT a.*, p.title as project_title 
                      FROM applications a 
                      JOIN projects p ON a.project_id = p.id 
                      WHERE p.client_email = '$client_email' 
                      ORDER BY a.id DESC LIMIT 5";
$recent_apps_res = mysqli_query($conn, $recent_apps_query);
?>

<!DOCTYPE html>
<html>
<head>
<title>Client Dashboard</title>
<link rel="stylesheet" href="css/style.css">

<style>
/* Keeping your exact sidebar styles */
.sidebar a {
    display: block;
    padding: 12px;
    text-decoration: none;
    color: white;
    margin-bottom: 5px;
}

.sidebar a.active {
    background: #2196F3; /* Set to blue for Client consistency */
}

.sidebar a:hover {
    background: #555;
}

.stats-container {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
}

.stat-card {
    flex: 1;
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    text-align: center;
}

.stat-card h3 { margin: 0; color: #666; font-size: 1em; }
.stat-card p { font-size: 2em; font-weight: bold; margin: 10px 0 0 0; color: #2196F3; }

.app-item {
    border-bottom: 1px solid #eee;
    padding: 10px 0;
}
.app-item:last-child { border-bottom: none; }
.app-item small { color: #888; }
</style>

</head>

<body>

<div class="dashboard client-theme">

<!-- ✅ SIDEBAR FIXED -->
<div class="sidebar">
<h2>Client</h2>

<a href="client-dashboard.php" class="active">Dashboard</a>
<a href="client-postproject.php">Post Project</a>
<a href="client-manageprojects.php">Manage Projects</a>
<a href="client-hire.php">Hire Freelancer</a>
<a href="client-profile.php">Profile</a>

<button onclick="window.location.href='logout.php'">Logout</button>
</div>

<!-- ✅ MAIN CONTENT -->
<div class="main">

<div class="dashboard-header">
    <div>
        <h1>Welcome, <?php echo htmlspecialchars($client_name); ?></h1>
        <p>Here is your project activity, recent applications, and hiring progress.</p>
    </div>
    <div class="dashboard-avatar"><?php echo htmlspecialchars($client_initial); ?></div>
</div>

<!-- ✅ STATS OVERVIEW -->
<div class="stats-container">
    <div class="stat-card">
        <h3>Total Projects</h3>
        <p><?php echo $total_projects; ?></p>
    </div>
    <div class="stat-card">
        <h3>Apps Received</h3>
        <p><?php echo $total_apps; ?></p>
    </div>
</div>

<!-- ✅ RECENT APPLICATIONS -->
<div class="card">
    <h3>Recent Applications</h3>
    <div id="applicationsContainer">
        <?php if(mysqli_num_rows($recent_apps_res) > 0): ?>
            <?php while($app = mysqli_fetch_assoc($recent_apps_res)): ?>
                <div class="app-item">
                    <strong><?php echo htmlspecialchars($app['freelancer_email']); ?></strong> applied for 
                    <em><?php echo htmlspecialchars($app['project_title']); ?></em><br>
                    <small>Proposal snippet: <?php echo htmlspecialchars(substr($app['proposal'], 0, 50)) . '...'; ?></small>
                </div>
            <?php endwhile; ?>
            <br>
            <a href="client-manageprojects.php" class="dashboard-link">View all project applications</a>
        <?php else: ?>
            <p>No applications received yet.</p>
        <?php endif; ?>
    </div>
</div>

</div>

</div>

<!-- SCRIPT -->
<script src="js/script.js"></script>

</body>
</html>
