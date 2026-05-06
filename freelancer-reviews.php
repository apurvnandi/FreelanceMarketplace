<?php
session_start();
include 'db.php';

if(!isset($_SESSION['user']) || $_SESSION['role'] != "freelancer"){
    header("Location: login.html");
    exit();
}

$freelancer_email = $_SESSION['user'];

$summary_stmt = $conn->prepare("SELECT COALESCE(AVG(rating), 0) AS average_rating, COUNT(*) AS total_reviews FROM reviews WHERE freelancer_email = ?");
$summary_stmt->bind_param("s", $freelancer_email);
$summary_stmt->execute();
$summary = $summary_stmt->get_result()->fetch_assoc();
$summary_stmt->close();

$average_rating = round((float)($summary['average_rating'] ?? 0), 1);
$total_reviews = (int)($summary['total_reviews'] ?? 0);
$filled_stars = (int) round($average_rating);

$reviews_stmt = $conn->prepare("
    SELECT r.*, p.title, c.name AS client_name
    FROM reviews r
    JOIN projects p ON p.id = r.project_id
    LEFT JOIN clients c ON c.email = r.client_email
    WHERE r.freelancer_email = ?
    ORDER BY r.created_at DESC
");
$reviews_stmt->bind_param("s", $freelancer_email);
$reviews_stmt->execute();
$reviews = $reviews_stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
<title>Reviews</title>
<link rel="stylesheet" href="css/style.css">
<style>
.sidebar a.active { background: #4CAF50; }
.review-summary {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 22px;
    align-items: center;
}
.rating-number {
    width: 110px;
    height: 110px;
    border-radius: 50%;
    background: #16a34a;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 34px;
    font-weight: 900;
}
.stars {
    color: #f59e0b;
    font-size: 26px;
    letter-spacing: 2px;
}
.review-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-left: 5px solid #16a34a;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 15px;
    box-shadow: 0 12px 28px rgba(15,23,42,0.06);
}
.review-meta {
    color: #64748b;
    font-size: 0.92em;
    margin: 6px 0 12px;
}
</style>
</head>
<body>
<div class="dashboard freelancer-theme">
<div class="sidebar">
<h2>Freelancer</h2>
<a href="freelancer-dashboard.php">Dashboard</a>
<a href="freelancer-projects.php">Projects</a>
<a href="freelancer-payments.php">Payments</a>
<a href="freelancer-reviews.php" class="active">Reviews</a>
<a href="freelancer-profile.php">Profile</a>
<button onclick="window.location.href='logout.php'">Logout</button>
</div>

<div class="main">
<div class="dashboard-header">
    <div>
        <h1>Reviews</h1>
        <p>Your default rating is 0 until a client reviews you after payment.</p>
    </div>
</div>

<div class="card review-summary">
    <div class="rating-number"><?php echo number_format($average_rating, 1); ?></div>
    <div>
        <h3>Average Rating</h3>
        <div class="stars">
            <?php echo str_repeat("★", $filled_stars) . str_repeat("☆", 5 - $filled_stars); ?>
        </div>
        <p><?php echo $total_reviews; ?> review(s)</p>
    </div>
</div>

<?php if(mysqli_num_rows($reviews) > 0): ?>
    <?php while($review = mysqli_fetch_assoc($reviews)): ?>
        <div class="review-card">
            <h3><?php echo htmlspecialchars($review['title']); ?></h3>
            <div class="stars">
                <?php echo str_repeat("★", (int)$review['rating']) . str_repeat("☆", 5 - (int)$review['rating']); ?>
            </div>
            <p class="review-meta">
                By <?php echo htmlspecialchars($review['client_name'] ?: $review['client_email']); ?>
                on <?php echo htmlspecialchars($review['created_at']); ?>
            </p>
            <?php if(!empty($review['comment'])): ?>
                <p><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <div class="card">No reviews yet.</div>
<?php endif; ?>
</div>
</div>
<script src="js/script.js"></script>
</body>
</html>
