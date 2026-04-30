<?php
session_start();
include 'db.php';

if(!isset($_SESSION['user']) || $_SESSION['role'] != "freelancer"){
    header("Location: login.html");
    exit();
}

$user_email = $_SESSION['user'];

// Fetch current freelancer profile data
$query = "SELECT * FROM freelancers WHERE email = '$user_email'";
$result = mysqli_query($conn, $query);
$user_data = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>
<head>
<title>Freelancer Profile</title>
<link rel="stylesheet" href="css/style.css">
<style>
    .sidebar a.active { background: #4CAF50; }
    .profile-form { display: flex; flex-direction: column; gap: 15px; max-width: 500px; }
    .profile-form input, .profile-form textarea { padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: inherit; }
    .save-btn { background: #4CAF50; color: white; border: none; padding: 12px; border-radius: 4px; cursor: pointer; font-weight: bold; }
    .save-btn:hover { background: #45a049; }
    .status-msg { padding: 10px; margin-bottom: 10px; border-radius: 4px; }
    .success { background: #d4edda; color: #155724; }
    .error { background: #f8d7da; color: #721c24; }
</style>
</head>
<body>
<div class="dashboard">
    <div class="sidebar">
        <h2>Freelancer</h2>
        <a href="freelancer-dashboard.php">Dashboard</a>
        <a href="freelancer-projects.php">Browse Projects</a>
        <a href="freelancer-profile.php" class="active">Profile</a>
        <a href="freelancer-reviews.php">Reviews</a>
        <a href="freelancer-payments.php">Payments</a>
        <button onclick="window.location.href='logout.php'">Logout</button>
    </div>

    <div class="main">
        <h1>My Freelancer Profile</h1>
        
        <?php if(isset($_GET['status'])): ?>
            <div class="status-msg <?php echo $_GET['status'] == 'success' ? 'success' : 'error'; ?>">
                <?php echo $_GET['status'] == 'success' ? "Profile updated!" : "Update failed."; ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <form action="update_profile.php" method="POST" class="profile-form">
                <label>Full Name</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($user_data['name'] ?? ''); ?>" required>
                
                <label>Email (ID)</label>
                <input type="email" value="<?php echo htmlspecialchars($user_email); ?>" disabled>

                <label>Professional Skills</label>
                <input type="text" name="skills" value="<?php echo htmlspecialchars($user_data['skills'] ?? ''); ?>" placeholder="e.g. PHP, SQL, Web Design">
                
                <label>Professional Bio</label>
                <textarea name="bio" rows="5" placeholder="Tell clients about your experience..."><?php echo htmlspecialchars($user_data['bio'] ?? ''); ?></textarea>
                
                <button type="submit" class="save-btn">Update Freelancer Profile</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
