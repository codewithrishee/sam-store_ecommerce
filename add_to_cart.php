
<?php
session_start();
include("config/db.php");

// ================= HELPER =================

function cleanValue($value) {
    return trim(strip_tags((string)($value ?? '')));
}

function matchOption($selected, $available) {
    $selected = trim($selected);

    if ($selected === '') {
        return '';
    }

    $options = array_map('trim', explode(',', (string)$available));

    foreach ($options as $option) {
        if (strcasecmp($option, $selected) === 0) {
            return $option;
        }
    }

    return false;
}

// ================= PRODUCT ID =================

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    exit("error");
}

// ================= FETCH PRODUCT =================

$stmt = $conn->prepare("
    SELECT id, product_name, new_price, image1, stock, color, size
    FROM products
    WHERE id = ?
    AND status = 1
    LIMIT 1
");

$stmt->bind_param("i", $id);
$stmt->execute();

$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    exit("error");
}

// ================= QUANTITY =================

$qty = filter_input(INPUT_POST, 'qty', FILTER_VALIDATE_INT);

$qty = ($qty && $qty > 0) ? $qty : 1;

$stock = (int)($product['stock'] ?? 0);

if ($stock <= 0) {
    exit("out_of_stock");
}

// ================= COLOR & SIZE =================

$availableColor = trim($product['color'] ?? '');
$availableSize  = trim($product['size'] ?? '');

$selectedColor = cleanValue($_POST['color'] ?? '');
$selectedSize  = cleanValue($_POST['size'] ?? '');

if ($availableColor !== '') {
    $selectedColor = matchOption($selectedColor, $availableColor);

    if ($selectedColor === false) {
        exit("select_color");
    }
}

if ($availableSize !== '') {
    $selectedSize = matchOption($selectedSize, $availableSize);

    if ($selectedSize === false) {
        exit("select_size");
    }
}

// ================= LOGIN CHECK =================

if (!isset($_SESSION['user'])) {

    $_SESSION['pending_cart_products'] ??= [];
    $_SESSION['pending_cart_selections'] ??= [];

    $_SESSION['pending_cart_products'][$id] =
        ($_SESSION['pending_cart_products'][$id] ?? 0) + $qty;

    $_SESSION['pending_cart_selections'][$id] = [
        'color' => $selectedColor,
        'size'  => $selectedSize
    ];

    $_SESSION['cart_login_required'] = true;
    $_SESSION['redirect_after_login'] = 'cart.php';

    exit("login_required");
}

// ================= CREATE CART =================

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// ================= CART ITEM =================

if (isset($_SESSION['cart'][$id])) {

    $newQty = (int)$_SESSION['cart'][$id]['qty'] + $qty;

    if ($newQty > $stock) {
        exit("stock_limit");
    }

    $_SESSION['cart'][$id]['qty'] = $newQty;

    // Update selected variants
    $_SESSION['cart'][$id]['color'] = $selectedColor;
    $_SESSION['cart'][$id]['size']  = $selectedSize;

} else {

    if ($qty > $stock) {
        exit("stock_limit");
    }

    $_SESSION['cart'][$id] = [
        "name"  => $product['product_name'],
        "price" => (float)$product['new_price'],
        "image" => $product['image1'],
        "qty"   => $qty,
        "color" => $selectedColor,
        "size"  => $selectedSize
    ];
}

echo "added";
