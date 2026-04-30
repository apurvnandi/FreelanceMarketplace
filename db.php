<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$db_host = "127.0.0.1";
$db_user = "root";
$db_pass = "";
$db_name = "freelance_db";
$db_port = 3306;

try {
    $conn = new mysqli($db_host, $db_user, $db_pass, "", $db_port);
    $conn->set_charset("utf8mb4");

    $conn->query("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $conn->select_db($db_name);

    $conn->query("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(150) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role ENUM('client', 'freelancer') NOT NULL,
            skills VARCHAR(255) DEFAULT NULL,
            bio TEXT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB
    ");

    $conn->query("
        CREATE TABLE IF NOT EXISTS clients (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT DEFAULT NULL,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(150) NOT NULL UNIQUE,
            company_name VARCHAR(150) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (user_id),
            CONSTRAINT fk_clients_user
                FOREIGN KEY (user_id) REFERENCES users(id)
                ON DELETE CASCADE
        ) ENGINE=InnoDB
    ");

    $conn->query("
        CREATE TABLE IF NOT EXISTS freelancers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT DEFAULT NULL,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(150) NOT NULL UNIQUE,
            skills VARCHAR(255) DEFAULT NULL,
            bio TEXT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (user_id),
            CONSTRAINT fk_freelancers_user
                FOREIGN KEY (user_id) REFERENCES users(id)
                ON DELETE CASCADE
        ) ENGINE=InnoDB
    ");

    $conn->query("
        CREATE TABLE IF NOT EXISTS projects (
            id INT AUTO_INCREMENT PRIMARY KEY,
            client_email VARCHAR(150) NOT NULL,
            freelancer_email VARCHAR(150) DEFAULT NULL,
            title VARCHAR(200) NOT NULL,
            description TEXT NOT NULL,
            budget DECIMAL(10,2) NOT NULL DEFAULT 0,
            status ENUM('open', 'active', 'completed') NOT NULL DEFAULT 'open',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (client_email),
            INDEX (freelancer_email),
            INDEX (status)
        ) ENGINE=InnoDB
    ");

    $conn->query("
        CREATE TABLE IF NOT EXISTS applications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            project_id INT NOT NULL,
            freelancer_email VARCHAR(150) NOT NULL,
            proposal TEXT NOT NULL,
            status ENUM('pending', 'accepted', 'rejected') NOT NULL DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (project_id),
            INDEX (freelancer_email),
            CONSTRAINT fk_applications_project
                FOREIGN KEY (project_id) REFERENCES projects(id)
                ON DELETE CASCADE
        ) ENGINE=InnoDB
    ");

    $conn->query("
        CREATE TABLE IF NOT EXISTS payments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            project_id INT NOT NULL,
            client_email VARCHAR(150) NOT NULL,
            freelancer_email VARCHAR(150) NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            status ENUM('paid') NOT NULL DEFAULT 'paid',
            paid_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (project_id),
            INDEX (client_email),
            INDEX (freelancer_email),
            CONSTRAINT fk_payments_project
                FOREIGN KEY (project_id) REFERENCES projects(id)
                ON DELETE CASCADE
        ) ENGINE=InnoDB
    ");

    $columns = [];
    $result = $conn->query("SHOW COLUMNS FROM users");
    while ($column = $result->fetch_assoc()) {
        $columns[$column['Field']] = true;
    }

    if (!isset($columns['skills'])) {
        $conn->query("ALTER TABLE users ADD skills VARCHAR(255) DEFAULT NULL");
    }

    if (!isset($columns['bio'])) {
        $conn->query("ALTER TABLE users ADD bio TEXT DEFAULT NULL");
    }

    if (!isset($columns['created_at'])) {
        $conn->query("ALTER TABLE users ADD created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
    }

    $conn->query("
        INSERT IGNORE INTO clients (user_id, name, email, created_at)
        SELECT id, name, email, COALESCE(created_at, CURRENT_TIMESTAMP)
        FROM users
        WHERE role = 'client'
    ");

    $conn->query("
        INSERT IGNORE INTO freelancers (user_id, name, email, skills, bio, created_at)
        SELECT id, name, email, skills, bio, COALESCE(created_at, CURRENT_TIMESTAMP)
        FROM users
        WHERE role = 'freelancer'
    ");
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    die(
        "Database connection failed. Start MySQL from the XAMPP Control Panel, then reload this page. " .
        "Details: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, "UTF-8")
    );
}
?>
