
<?php
session_start();
include "config/db.php";

// LOGIN CHECK
if(!isset($_SESSION['user'])){
    echo "login";
    exit;
}

// PRODUCT ID CHECK
if(!isset($_POST['product_id'])){
    echo "no_id";
    exit;
}

$email = $_SESSION['user'];
$product_id = intval($_POST['product_id']);

// GET USER
$stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();

$user = $stmt->get_result()->fetch_assoc();

if(!$user){
    echo "login";
    exit;
}

$user_id = $user['id'];

// CHECK EXISTING
$stmt = $conn->prepare("
    SELECT id FROM wishlist
    WHERE user_id=? AND product_id=?
");

$stmt->bind_param("ii", $user_id, $product_id);
$stmt->execute();

$check = $stmt->get_result();

if($check->num_rows > 0){
    echo "exists";
    exit;
}

// INSERT WISHLIST
$stmt = $conn->prepare("
    INSERT INTO wishlist (user_id, product_id)
    VALUES (?,?)
");

$stmt->bind_param("ii", $user_id, $product_id);

if($stmt->execute()){
    echo "added";
}else{
    echo "error";
}
?>
