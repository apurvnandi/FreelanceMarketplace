<?php
include "db.php";

$title = $_POST['title'];
$description = $_POST['description'];
$budget = $_POST['budget'];
$email = $_POST['email'];

$sql = "INSERT INTO projects (client_email, title, description, budget)
VALUES ('$email','$title','$description','$budget')";

if($conn->query($sql)){
echo "success";
}else{
echo "error";
}
?>