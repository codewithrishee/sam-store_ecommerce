<?php
include "../config/db.php";

$name = "Vijay Kumar";
$email = "vijaygtconnect@gmail.com";
$password = password_hash("admin@414", PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO admins (name, email, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $name, $email, $password);
$stmt->execute();

echo "Admin Created!";
?>