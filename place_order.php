
<?php
session_start();
include("config/db.php");

// Check cart
if (empty($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    header("Location: shop.php");
    exit;
}

// Validate customer details
$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$phone   = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');
$payment = $_POST['payment'] ?? '';

if (
    $name === '' ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    $phone === '' ||
    $address === '' ||
    $payment !== 'cod'
) {
    die("Please enter valid checkout details.");
}

$cart = $_SESSION['cart'];
$total = 0;

// Calculate total
foreach ($cart as $item) {
    $price = (float)($item['price'] ?? 0);
    $qty   = (int)($item['qty'] ?? 0);

    if ($price < 0 || $qty <= 0) {
        die("Invalid cart item.");
    }

    $total += $price * $qty;
}

try {
    $conn->begin_transaction();

    // Insert order
    $orderStmt = $conn->prepare("
        INSERT INTO orders
        (name, email, phone, address, payment_method, total_amount)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    if (!$orderStmt) {
        throw new Exception($conn->error);
    }

    $orderStmt->bind_param(
        "sssssd",
        $name,
        $email,
        $phone,
        $address,
        $payment,
        $total
    );

    if (!$orderStmt->execute()) {
        throw new Exception($orderStmt->error);
    }

    $order_id = $conn->insert_id;
    $orderStmt->close();

    // Insert order items with variants
    $itemStmt = $conn->prepare("
        INSERT INTO order_items
        (order_id, product_id, product_name, price, qty, total, color, size)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$itemStmt) {
        throw new Exception($conn->error);
    }

    foreach ($cart as $id => $item) {

        $product_id = (int)$id;
        $product_name = trim($item['name'] ?? '');
        $price = (float)$item['price'];
        $qty = (int)$item['qty'];
        $sub = $price * $qty;

        $color = trim($item['color'] ?? '');
        $size  = trim($item['size'] ?? '');

        $itemStmt->bind_param(
            "iisdidss",
            $order_id,
            $product_id,
            $product_name,
            $price,
            $qty,
            $sub,
            $color,
            $size
        );

        if (!$itemStmt->execute()) {
            throw new Exception($itemStmt->error);
        }
    }

    $itemStmt->close();

    $conn->commit();

    // Clear cart after successful order
    unset($_SESSION['cart']);

    header("Location: success.php");
    exit;

} catch (Throwable $e) {

    $conn->rollback();

    error_log("Order placement error: " . $e->getMessage());

    die("Unable to place your order. Please try again.");
}
