<?php
include "db.php";

$client_email = $_GET['email'];

$sql = "SELECT applications.*, projects.title 
FROM applications 
JOIN projects ON applications.project_id = projects.id
WHERE projects.client_email='$client_email'";

$result = $conn->query($sql);

$data = [];

while($row = $result->fetch_assoc()){
    $data[] = $row;
}

echo json_encode($data);
?>