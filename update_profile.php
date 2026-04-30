<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: " . ($_SESSION['role'] === "freelancer" ? "freelancer-profile.php" : "client-profile.php"));
    exit();
}

$email = $_SESSION['user'];
$role = $_SESSION['role'];
$name = trim($_POST['name'] ?? '');
$skills = trim($_POST['skills'] ?? '');
$bio = trim($_POST['bio'] ?? '');

if ($name === '') {
    header("Location: " . ($role === "freelancer" ? "freelancer-profile.php" : "client-profile.php") . "?status=error");
    exit();
}

$conn->begin_transaction();

try {
    $stmt = $conn->prepare("UPDATE users SET name = ?, skills = ?, bio = ? WHERE email = ?");
    $stmt->bind_param("ssss", $name, $skills, $bio, $email);
    $stmt->execute();
    $stmt->close();

    if ($role === "freelancer") {
        $profile_stmt = $conn->prepare("UPDATE freelancers SET name = ?, skills = ?, bio = ? WHERE email = ?");
        $profile_stmt->bind_param("ssss", $name, $skills, $bio, $email);
    } else {
        $profile_stmt = $conn->prepare("UPDATE clients SET name = ?, company_name = ? WHERE email = ?");
        $profile_stmt->bind_param("sss", $name, $name, $email);
    }

    $profile_stmt->execute();
    $profile_stmt->close();

    $conn->commit();
    $status = "success";
} catch (mysqli_sql_exception $e) {
    $conn->rollback();
    $status = "error";
}

header("Location: " . ($role === "freelancer" ? "freelancer-profile.php" : "client-profile.php") . "?status=" . $status);
exit();
?>
