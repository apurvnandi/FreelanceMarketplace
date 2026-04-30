<?php
include "db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: register.html");
    exit();
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$role = $_POST['role'] ?? '';

if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '' || !in_array($role, ['client', 'freelancer'], true)) {
    die("Please fill all registration fields correctly. <a href='register.html'>Go back</a>");
}

$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$existing = $stmt->get_result();
$stmt->close();

if ($existing->num_rows > 0) {
    die("This email is already registered. <a href='login.html'>Login here</a>");
}

$conn->begin_transaction();

try {
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $password, $role);
    $stmt->execute();
    $user_id = $conn->insert_id;
    $stmt->close();

    if ($role === 'client') {
        $profile_stmt = $conn->prepare("
            INSERT INTO clients (user_id, name, email)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE user_id = VALUES(user_id), name = VALUES(name)
        ");
        $profile_stmt->bind_param("iss", $user_id, $name, $email);
    } else {
        $profile_stmt = $conn->prepare("
            INSERT INTO freelancers (user_id, name, email)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE user_id = VALUES(user_id), name = VALUES(name)
        ");
        $profile_stmt->bind_param("iss", $user_id, $name, $email);
    }

    $profile_stmt->execute();
    $profile_stmt->close();

    $conn->commit();
    header("Location: login.html?registered=1");
    exit();
} catch (mysqli_sql_exception $e) {
    $conn->rollback();
    error_log("Registration failed for $email: " . $e->getMessage());
}

echo "Registration failed. Please try a different email or try again. <a href='register.html'>Go back</a>";
?>
