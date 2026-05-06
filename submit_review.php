<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== "client") {
    header("Location: login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['project_id'], $_POST['rating'])) {
    header("Location: client-hire.php");
    exit();
}

$client_email = $_SESSION['user'];
$project_id = (int) $_POST['project_id'];
$rating = (int) $_POST['rating'];
$comment = trim($_POST['comment'] ?? '');

if ($rating < 1 || $rating > 5) {
    header("Location: client-hire.php?review=invalid_rating");
    exit();
}

$project_stmt = $conn->prepare("
    SELECT p.id, p.freelancer_email
    FROM projects p
    JOIN payments pay ON pay.project_id = p.id
    WHERE p.id = ?
      AND p.client_email = ?
      AND p.status = 'completed'
      AND pay.status = 'paid'
");
$project_stmt->bind_param("is", $project_id, $client_email);
$project_stmt->execute();
$project = $project_stmt->get_result()->fetch_assoc();
$project_stmt->close();

if (!$project) {
    header("Location: client-hire.php?review=not_allowed");
    exit();
}

$freelancer_email = $project['freelancer_email'];

$review_stmt = $conn->prepare("
    INSERT INTO reviews (project_id, client_email, freelancer_email, rating, comment)
    VALUES (?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment), created_at = CURRENT_TIMESTAMP
");
$review_stmt->bind_param("issis", $project_id, $client_email, $freelancer_email, $rating, $comment);
$review_stmt->execute();
$review_stmt->close();

header("Location: client-hire.php?review=saved");
exit();
?>
