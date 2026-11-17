<?php
session_start();
include("../config/db.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Data get aur sanitize karein
    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
    $status   = isset($_POST['status']) ? mysqli_real_escape_string($conn, $_POST['status']) : '';

    // Basic Validation
    if ($order_id > 0 && !empty($status)) {
        
        // Status update karne ki query
        $query = "UPDATE orders SET order_status = '$status' WHERE id = $order_id";
        
        if (mysqli_query($conn, $query)) {
            // Success message session mein save karein
            $_SESSION['success'] = "Order #$order_id status updated to $status successfully!";
        } else {
            // Error message
            $_SESSION['error'] = "Something went wrong: " . mysqli_error($conn);
        }
    } else {
        $_SESSION['error'] = "Invalid Request!";
    }

    // Wapas usi details page par bhej dein
    header("Location: order_details.php?id=" . $order_id);
    exit();
} else {
    // Agar koi direct access kare toh orders list pe bhej do
    header("Location: orders.php");
    exit();
}
?>