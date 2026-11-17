<?php
session_start();
include "config/db.php";

// LOGIN CHECK
if(!isset($_SESSION['user'])){
    header("Location: login.php");
    exit;
}

// USER ID
$email = $_SESSION['user'];

$user_q = mysqli_query($conn,"SELECT id FROM users WHERE email='$email'");
$user = mysqli_fetch_assoc($user_q);

if(!$user){
    die("User not found");
}

$user_id = (int)$user['id'];

// GET WISHLIST ID (wid)
if(!isset($_GET['id'])){
    die("Invalid request");
}

$wid = (int)$_GET['id'];

//  DEBUG (optional - ek baar check kar lena)
// echo "WID: ".$wid." USER: ".$user_id; exit;

// DELETE QUERY (FINAL FIX)
$delete = mysqli_query($conn,"
DELETE FROM wishlist 
WHERE id = $wid AND user_id = $user_id
");

//  CHECK RESULT
if(mysqli_affected_rows($conn) > 0){

    header("Location: wishlist.php?msg=removed");
    exit;

}else{

    //  ye tab aayega jab record mila hi nahi
    header("Location: wishlist.php?msg=notfound");
    exit;
}
?>