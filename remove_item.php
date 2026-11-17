
<?php
session_start();

// Check cart
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

// Get cart item key
$key = $_GET['key'] ?? '';

if ($key !== '' && isset($_SESSION['cart'][$key])) {

    unset($_SESSION['cart'][$key]);

} else {

    // Backward compatibility with old product ID links
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if ($id && isset($_SESSION['cart'][$id])) {
        unset($_SESSION['cart'][$id]);
    }
}

// Redirect
header("Location: cart.php");
exit;
