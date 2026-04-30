<?php
session_start();
include 'db.php';

// Security: Ensure only logged-in clients can post projects
if(!isset($_SESSION['user']) || $_SESSION['role'] != "client"){
    header("Location: login.html");
    exit();
}

$message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $budget = mysqli_real_escape_string($conn, $_POST['budget']);
    $client_email = $_SESSION['user'];
    $status = 'open'; // Default status for new projects

    // Prepared statement for security
    $stmt = $conn->prepare("INSERT INTO projects (client_email, title, description, budget, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $client_email, $title, $description, $budget, $status);

    if ($stmt->execute()) {
        $message = "<span style='color:green;'>Project posted successfully!</span>";
    } else {
        $message = "<span style='color:red;'>Error: " . $conn->error . "</span>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Post Project</title>
<link rel="stylesheet" href="css/style.css">
<style>
    .sidebar a.active {
        background: #2196F3;
    }
    input[type="text"], input[type="number"], textarea {
        width: 100%;
        padding: 10px;
        margin: 10px 0;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-sizing: border-box; /* Ensures padding doesn't affect width */
    }
    textarea {
        height: 150px;
        resize: vertical;
    }
    button[type="submit"] {
        background-color: #2196F3;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-weight: bold;
    }
    button[type="submit"]:hover {
        background-color: #1976D2;
    }
</style>
</head>

<body>

<div class="dashboard">

<!-- ✅ SIDEBAR (CONNECTED PROPERLY) -->
<div class="sidebar">
<h2>Client</h2>

<a href="client-dashboard.php">Dashboard</a>
<a href="client-postproject.php" class="active">Post Project</a>
<a href="client-manageprojects.php">Manage Projects</a>
<a href="client-hire.php">Hire Freelancer</a>
<a href="client-profile.php">Profile</a>

<button onclick="window.location.href='logout.php'">Logout</button>
</div>

<!-- ✅ MAIN -->
<div class="main">
<h1>Post Project</h1>

<div class="card">

    <!-- Removed onsubmit JavaScript and replaced with standard PHP POST -->
    <form action="client-postproject.php" method="POST">

        <label>Project Title</label>
        <input type="text" name="title" placeholder="e.g. Website Development" required>

        <label>Description</label>
        <textarea name="description" placeholder="Detailed project requirements..." required></textarea>

        <label>Budget (₹)</label>
        <input type="number" name="budget" placeholder="Amount in INR" required>

        <br><br>
        <button type="submit">Post Project</button>

    </form>

    <p id="msg"><?php echo $message; ?></p>

</div>

</div>

</div>

<script src="js/script.js"></script>

</body>
</html>