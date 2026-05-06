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

$completed_query = "SELECT p.*, f.name as freelancer_name, r.rating, r.comment
                    FROM projects p
                    JOIN freelancers f ON p.freelancer_email = f.email
                    JOIN payments pay ON pay.project_id = p.id AND pay.status = 'paid'
                    LEFT JOIN reviews r ON r.project_id = p.id
                    WHERE p.client_email = '$client_email' AND p.status = 'completed'
                    ORDER BY p.id DESC";
$completed_result = mysqli_query($conn, $completed_query);
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
    .review-form {
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #e2e8f0;
    }
    .star-rating {
        display: inline-flex;
        flex-direction: row-reverse;
        justify-content: flex-end;
        gap: 4px;
        margin: 8px 0;
    }
    .star-rating input {
        display: none;
    }
    .star-rating label {
        font-size: 30px;
        color: #cbd5e1;
        cursor: pointer;
        transition: color 0.2s ease, transform 0.2s ease;
    }
    .star-rating label:hover,
    .star-rating label:hover ~ label,
    .star-rating input:checked ~ label {
        color: #f59e0b;
    }
    .star-rating label:hover {
        transform: translateY(-2px);
    }
    .review-form textarea {
        width: 100%;
        min-height: 90px;
        resize: vertical;
        padding: 10px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-family: inherit;
        margin: 8px 0 12px;
    }
    .review-btn {
        background: #0f766e;
        color: white;
        border: none;
        padding: 10px 18px;
        border-radius: 4px;
        cursor: pointer;
        font-weight: bold;
    }
    .review-stars-display {
        color: #f59e0b;
        font-size: 20px;
        letter-spacing: 1px;
    }
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
        <?php if(isset($_GET['review']) && $_GET['review'] === 'saved'): ?>
            <div class="card" style="color: #2e7d32;">Review saved successfully.</div>
        <?php elseif(isset($_GET['review']) && $_GET['review'] === 'not_allowed'): ?>
            <div class="card" style="color: #c62828;">You can review only after payment is completed.</div>
        <?php elseif(isset($_GET['review']) && $_GET['review'] === 'invalid_rating'): ?>
            <div class="card" style="color: #c62828;">Please select a rating from 1 to 5 stars.</div>
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

        <h1 style="margin-top: 35px;">Completed Payments & Reviews</h1>
        <div class="hired-container">
            <?php if(mysqli_num_rows($completed_result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($completed_result)): ?>
                    <div class="hired-card">
                        <h3>Project: <?php echo htmlspecialchars($row['title']); ?></h3>
                        <p><strong>Freelancer:</strong> <?php echo htmlspecialchars($row['freelancer_name']); ?></p>
                        <p class="contact-info">Email: <?php echo htmlspecialchars($row['freelancer_email']); ?></p>
                        <p>Paid Amount: ₹<?php echo number_format($row['budget']); ?></p>

                        <?php if(!empty($row['rating'])): ?>
                            <p><strong>Your Rating:</strong></p>
                            <div class="review-stars-display">
                                <?php echo str_repeat("★", (int)$row['rating']) . str_repeat("☆", 5 - (int)$row['rating']); ?>
                            </div>
                            <?php if(!empty($row['comment'])): ?>
                                <p><?php echo nl2br(htmlspecialchars($row['comment'])); ?></p>
                            <?php endif; ?>
                        <?php endif; ?>

                        <form action="submit_review.php" method="POST" class="review-form">
                            <input type="hidden" name="project_id" value="<?php echo $row['id']; ?>">
                            <label><strong><?php echo empty($row['rating']) ? "Rate this freelancer" : "Update your review"; ?></strong></label>
                            <div class="star-rating">
                                <?php for($i = 5; $i >= 1; $i--): ?>
                                    <input type="radio" id="project-<?php echo $row['id']; ?>-star-<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" <?php echo ((int)($row['rating'] ?? 0) === $i) ? 'checked' : ''; ?> required>
                                    <label for="project-<?php echo $row['id']; ?>-star-<?php echo $i; ?>">★</label>
                                <?php endfor; ?>
                            </div>
                            <textarea name="comment" placeholder="Write a short review about this freelancer..."><?php echo htmlspecialchars($row['comment'] ?? ''); ?></textarea>
                            <button type="submit" class="review-btn">Save Review</button>
                        </form>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="card">
                    <p>No completed paid projects yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="js/script.js"></script>
</body>
</html>
