<?php
session_start();
include 'db.php';

if(!isset($_SESSION['user']) || $_SESSION['role'] != "client"){
    header("Location: login.html");
    exit();
}

$user_email = $_SESSION['user'];

// Fetch current client profile data
$query = "SELECT * FROM clients WHERE email = '$user_email'";
$result = mysqli_query($conn, $query);
$user_data = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>
<head>
<title>Client Profile</title>
<link rel="stylesheet" href="css/style.css">
<style>
    .sidebar a.active { background: #2196F3; } /* Different color for client consistency */
    .profile-form { display: flex; flex-direction: column; gap: 15px; max-width: 500px; }
    .profile-form input { padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: inherit; }
    .save-btn { background: #2196F3; color: white; border: none; padding: 12px; border-radius: 4px; cursor: pointer; font-weight: bold; }
    .save-btn:hover { background: #1976D2; }
    .status-msg { padding: 10px; margin-bottom: 10px; border-radius: 4px; }
    .success { background: #d4edda; color: #155724; }
</style>
</head>
<body>
<div class="dashboard">
    <div class="sidebar">
        <h2>Client</h2>
        <a href="client-dashboard.php">Dashboard</a>
        <a href="client-postproject.php">Post a Project</a>
        <a href="client-manageprojects.php">Manage Projects</a>
        <a href="client-profile.php" class="active">Profile</a>
        <button onclick="window.location.href='logout.php'">Logout</button>
    </div>

    <div class="main">
        <h1>Client Settings</h1>
        
        <?php if(isset($_GET['status'])): ?>
            <div class="status-msg success">Account updated successfully!</div>
        <?php endif; ?>

        <div class="card">
            <form action="update_profile.php" method="POST" class="profile-form">
                <label>Company/Contact Name</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($user_data['name'] ?? ''); ?>" required>
                
                <label>Login Email</label>
                <input type="email" value="<?php echo htmlspecialchars($user_email); ?>" disabled>
                
                <!-- Hidden fields to prevent update_profile.php from throwing errors -->
                <input type="hidden" name="skills" value="">
                <input type="hidden" name="bio" value="">
                
                <button type="submit" class="save-btn">Update Client Info</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
