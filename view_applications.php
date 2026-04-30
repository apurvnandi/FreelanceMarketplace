<?php
session_start();
include 'db.php';

if(!isset($_SESSION['user']) || $_SESSION['role'] != "client"){
    header("Location: login.html");
    exit();
}

$project_id = (int) ($_GET['project_id'] ?? 0);

// Fetch the project details first to ensure the client owns it
$project_stmt = $conn->prepare("SELECT title, status, freelancer_email FROM projects WHERE id = ? AND client_email = ?");
$project_stmt->bind_param("is", $project_id, $_SESSION['user']);
$project_stmt->execute();
$project = $project_stmt->get_result()->fetch_assoc();
$project_stmt->close();

if(!$project) {
    die("Access Denied or Project not found.");
}

// Fetch all applications for this specific project
$apps_stmt = $conn->prepare("SELECT a.*, f.name AS freelancer_name, f.skills
                             FROM applications a
                             LEFT JOIN freelancers f ON f.email = a.freelancer_email
                             WHERE a.project_id = ?
                             ORDER BY a.id DESC");
$apps_stmt->bind_param("i", $project_id);
$apps_stmt->execute();
$apps_result = $apps_stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
<title>Project Applications</title>
<link rel="stylesheet" href="css/style.css">
<style>
    .app-card {
        background: #fff;
        padding: 20px;
        margin-bottom: 20px;
        border-left: 5px solid #2196F3;
        border-radius: 4px;
    }
    .hire-btn {
        background: #4CAF50;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 4px;
        cursor: pointer;
    }
    .status-pill {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 20px;
        background: #e3f2fd;
        color: #1976d2;
        font-size: 0.85em;
        margin-top: 8px;
    }
    .status-accepted { background: #e8f5e9; color: #2e7d32; }
    .status-rejected { background: #ffebee; color: #c62828; }
</style>
</head>
<body>
<div class="dashboard">
    <div class="sidebar">
        <h2>Client</h2>
        <a href="client-manageprojects.php">Back to Projects</a>
    </div>

    <div class="main">
        <h1>Applications for: <?php echo htmlspecialchars($project['title']); ?></h1>

        <?php if(mysqli_num_rows($apps_result) > 0): ?>
            <?php while($app = mysqli_fetch_assoc($apps_result)): ?>
                <div class="app-card">
                    <h4>Freelancer: <?php echo htmlspecialchars($app['freelancer_name'] ?: $app['freelancer_email']); ?></h4>
                    <p>Email: <?php echo htmlspecialchars($app['freelancer_email']); ?></p>
                    <?php if(!empty($app['skills'])): ?>
                        <p><strong>Skills:</strong> <?php echo htmlspecialchars($app['skills']); ?></p>
                    <?php endif; ?>
                    <p><strong>Proposal:</strong><br><?php echo nl2br(htmlspecialchars($app['proposal'])); ?></p>
                    <span class="status-pill status-<?php echo htmlspecialchars($app['status']); ?>">
                        <?php echo ucfirst(htmlspecialchars($app['status'])); ?>
                    </span>
                    
                    <?php if($project['status'] === 'open'): ?>
                        <form action="hire_freelancer.php" method="POST" style="margin-top: 15px;">
                            <input type="hidden" name="project_id" value="<?php echo $project_id; ?>">
                            <input type="hidden" name="application_id" value="<?php echo $app['id']; ?>">
                            <button type="submit" class="hire-btn">Hire this Freelancer</button>
                        </form>
                    <?php elseif($project['freelancer_email'] === $app['freelancer_email']): ?>
                        <p style="margin-top: 15px; color: #2e7d32;"><strong>Selected freelancer</strong></p>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No applications received for this project yet.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
