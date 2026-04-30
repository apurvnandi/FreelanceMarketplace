<?php
session_start();
include "db.php";

// Check if user is logged in and is a freelancer
if(!isset($_SESSION['user']) || $_SESSION['role'] != "freelancer"){
    echo "unauthorized";
    exit();
}

if(isset($_POST['project_id']) && isset($_POST['proposal'])){

    $project_id = (int) $_POST['project_id'];
    $email = $_SESSION['user']; // Pulling email from session is more secure than POST
    $proposal = trim($_POST['proposal']);

    if ($proposal === '') {
        header("Location: freelancer-projects.php?status=proposal_required");
        exit();
    }

    $project_check = $conn->prepare("SELECT id FROM projects WHERE id = ? AND status = 'open'");
    $project_check->bind_param("i", $project_id);
    $project_check->execute();
    $project_result = $project_check->get_result();
    $project_check->close();

    if ($project_result->num_rows === 0) {
        header("Location: freelancer-projects.php?status=not_available");
        exit();
    }

    $duplicate_check = $conn->prepare("SELECT id FROM applications WHERE project_id = ? AND freelancer_email = ?");
    $duplicate_check->bind_param("is", $project_id, $email);
    $duplicate_check->execute();
    $duplicate_result = $duplicate_check->get_result();
    $duplicate_check->close();

    if ($duplicate_result->num_rows > 0) {
        header("Location: freelancer-projects.php?status=already_applied");
        exit();
    }

    // Using Prepared Statements for security
    $stmt = $conn->prepare("INSERT INTO applications (project_id, freelancer_email, proposal) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $project_id, $email, $proposal);

    if($stmt->execute()){
        header("Location: freelancer-projects.php?status=applied");
        exit();
    } else {
        echo "error: " . $conn->error;
    }

    $stmt->close();

} else {
    header("Location: freelancer-projects.php?status=no_data");
    exit();
}

$conn->close();
?>
