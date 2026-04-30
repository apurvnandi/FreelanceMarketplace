<?php
session_start();
include 'db.php';

// Security: Ensure only the client can hire people
if(!isset($_SESSION['user']) || $_SESSION['role'] != "client"){
    echo "unauthorized";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['project_id']) && isset($_POST['application_id'])) {
    
    $project_id = (int) $_POST['project_id'];
    $application_id = (int) $_POST['application_id'];
    $client_email = $_SESSION['user'];

    $app_stmt = $conn->prepare("SELECT a.freelancer_email
                                FROM applications a
                                JOIN projects p ON p.id = a.project_id
                                WHERE a.id = ? AND a.project_id = ? AND p.client_email = ? AND p.status = 'open'");
    $app_stmt->bind_param("iis", $application_id, $project_id, $client_email);
    $app_stmt->execute();
    $application = $app_stmt->get_result()->fetch_assoc();
    $app_stmt->close();

    if (!$application) {
        header("Location: view_applications.php?project_id=" . $project_id . "&status=invalid");
        exit();
    }

    $freelancer_email = $application['freelancer_email'];

    $conn->begin_transaction();

    try {
        $project_stmt = $conn->prepare("UPDATE projects SET freelancer_email = ?, status = 'active' WHERE id = ? AND client_email = ?");
        $project_stmt->bind_param("sis", $freelancer_email, $project_id, $client_email);
        $project_stmt->execute();
        $project_stmt->close();

        $accept_stmt = $conn->prepare("UPDATE applications SET status = 'accepted' WHERE id = ?");
        $accept_stmt->bind_param("i", $application_id);
        $accept_stmt->execute();
        $accept_stmt->close();

        $reject_stmt = $conn->prepare("UPDATE applications SET status = 'rejected' WHERE project_id = ? AND id != ?");
        $reject_stmt->bind_param("ii", $project_id, $application_id);
        $reject_stmt->execute();
        $reject_stmt->close();

        $conn->commit();
        header("Location: client-hire.php?status=hired");
        exit();
    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        echo "Error hiring freelancer: " . htmlspecialchars($e->getMessage());
    }
} else {
    header("Location: client-manageprojects.php");
}

$conn->close();
?>
