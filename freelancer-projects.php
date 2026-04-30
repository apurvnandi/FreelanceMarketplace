<?php
session_start();
include 'db.php';

if(!isset($_SESSION['user']) || $_SESSION['role'] != "freelancer"){
    header("Location: login.html");
    exit();
}

$user_email = $_SESSION['user'];

// Fetch open projects plus projects connected to this freelancer.
$query = "SELECT p.*, a.id AS application_id, a.status AS application_status
          FROM projects p
          LEFT JOIN applications a
            ON a.project_id = p.id AND a.freelancer_email = ?
          WHERE (
                p.status = 'open'
                AND (p.freelancer_email IS NULL OR p.freelancer_email = '')
            )
            OR p.freelancer_email = ?
            OR a.id IS NOT NULL
          ORDER BY p.id DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $user_email, $user_email);
$stmt->execute();
$result = $stmt->get_result();

$status_messages = [
    'applied' => 'Proposal submitted successfully.',
    'already_applied' => 'You already applied to that project.',
    'not_available' => 'That project is no longer available.',
    'proposal_required' => 'Please enter a proposal before applying.',
    'no_data' => 'Missing application details.'
];
?>

<!DOCTYPE html>
<html>
<head>
<title>Freelancer Projects</title>
<link rel="stylesheet" href="css/style.css">

<style>
.sidebar a.active {
    background: #4CAF50;
}
.project-card {
    background: #fff;
    padding: 20px;
    margin-bottom: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.apply-btn {
    background: #4CAF50;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 4px;
    cursor: pointer;
    margin-top: 10px;
}
.apply-btn:hover {
    background: #45a049;
}
/* New style for the proposal box */
.proposal-box {
    width: 100%;
    padding: 10px;
    margin-top: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-family: inherit;
    resize: vertical;
}
</style>

</head>

<body>

<div class="dashboard">

<!-- ✅ SIDEBAR (FULL + CONSISTENT) -->
<div class="sidebar">
<h2>Freelancer</h2>

<a href="freelancer-dashboard.php">Dashboard</a>
<a href="freelancer-projects.php" class="active">Browse Projects</a>
<a href="freelancer-profile.php">Profile</a>
<a href="freelancer-reviews.php">Reviews</a>
<a href="freelancer-payments.php">Payments</a>

<button onclick="window.location.href='logout.php'">Logout</button>
</div>

<!-- ✅ MAIN -->
<div class="main">

<h1>Browse Projects</h1>

<?php if(isset($_GET['status'], $status_messages[$_GET['status']])): ?>
    <div class="card"><?php echo htmlspecialchars($status_messages[$_GET['status']]); ?></div>
<?php endif; ?>

<div id="projectsContainer">
    <?php if(mysqli_num_rows($result) > 0): ?>
        <?php while($row = mysqli_fetch_assoc($result)): ?>
            <div class="project-card">
                <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                <p><?php echo htmlspecialchars($row['description']); ?></p>
                <p><strong>Budget:</strong> ₹<?php echo number_format($row['budget']); ?></p>
                <p><strong>Client:</strong> <?php echo htmlspecialchars($row['client_email']); ?></p>
                
                <?php if($row['freelancer_email'] === $user_email && $row['status'] === 'completed'): ?>
                    <p><strong>Status:</strong> Completed / Paid</p>
                    <p><strong>Your application:</strong> Accepted</p>
                    <button type="button" class="apply-btn" disabled>Payment Received</button>
                <?php elseif($row['freelancer_email'] === $user_email && $row['status'] === 'active'): ?>
                    <p><strong>Status:</strong> Hired / Active</p>
                    <p><strong>Your application:</strong> Accepted</p>
                    <button type="button" class="apply-btn" disabled>Selected for Project</button>
                <?php elseif($row['application_id']): ?>
                    <p><strong>Your application:</strong> <?php echo ucfirst(htmlspecialchars($row['application_status'])); ?></p>
                    <button type="button" class="apply-btn" disabled>Already Applied</button>
                <?php else: ?>
                    <form action="apply_project.php" method="POST">
                        <input type="hidden" name="project_id" value="<?php echo $row['id']; ?>">
                        
                        <textarea 
                            name="proposal" 
                            class="proposal-box" 
                            placeholder="Describe why you are the best fit for this project..." 
                            required></textarea>
                        
                        <button type="submit" class="apply-btn">Submit Proposal</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No projects found at the moment. Check back later!</p>
    <?php endif; ?>
</div>

</div>

</div>

<script src="js/script.js"></script>

</body>
</html>
