<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== "client") {
    header("Location: login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['project_id'])) {
    header("Location: client-hire.php");
    exit();
}

$project_id = (int) $_POST['project_id'];
$client_email = $_SESSION['user'];

$project_stmt = $conn->prepare("
    SELECT id, client_email, freelancer_email, budget, status
    FROM projects
    WHERE id = ? AND client_email = ? AND status = 'active' AND freelancer_email IS NOT NULL
");
$project_stmt->bind_param("is", $project_id, $client_email);
$project_stmt->execute();
$project = $project_stmt->get_result()->fetch_assoc();
$project_stmt->close();

if (!$project) {
    header("Location: client-hire.php?payment=invalid");
    exit();
}

$exists_stmt = $conn->prepare("SELECT id FROM payments WHERE project_id = ?");
$exists_stmt->bind_param("i", $project_id);
$exists_stmt->execute();
$existing_payment = $exists_stmt->get_result();
$exists_stmt->close();

if ($existing_payment->num_rows > 0) {
    header("Location: client-hire.php?payment=already_paid");
    exit();
}

$freelancer_email = $project['freelancer_email'];
$amount = $project['budget'];

$conn->begin_transaction();

try {
    $payment_stmt = $conn->prepare("
        INSERT INTO payments (project_id, client_email, freelancer_email, amount, status)
        VALUES (?, ?, ?, ?, 'paid')
    ");
    $payment_stmt->bind_param("issd", $project_id, $client_email, $freelancer_email, $amount);
    $payment_stmt->execute();
    $payment_stmt->close();

    $project_update = $conn->prepare("UPDATE projects SET status = 'completed' WHERE id = ? AND client_email = ?");
    $project_update->bind_param("is", $project_id, $client_email);
    $project_update->execute();
    $project_update->close();

    $conn->commit();
    header("Location: client-hire.php?payment=paid");
    exit();
} catch (mysqli_sql_exception $e) {
    $conn->rollback();
    echo "Payment failed: " . htmlspecialchars($e->getMessage());
}
?>
