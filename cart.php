
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "config/db.php";
include "include/header.php";

$cart = $_SESSION['cart'] ?? [];
$total = 0;
?>

<style>
/* CART PAGE */
.cart-container {
    margin-top: 55px;
    margin-bottom: 80px;
}

.cart-heading {
    font-size: 30px;
    font-weight: 800;
    color: #252942;
}

.cart-subtitle {
    color: #888;
    font-size: 14px;
}

/* CART TABLE */
.cart-box {
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    border: 1px solid #eee;
}

.cart-table {
    margin-bottom: 0;
}

.cart-table thead th {
    background: #f8f9fb;
    color: #555;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    padding: 18px 15px;
    border: none;
}

.cart-table tbody td {
    padding: 20px 15px;
    vertical-align: middle;
    border-color: #f1f1f1;
}

.product-img-cart {
    width: 85px;
    height: 85px;
    object-fit: cover;
    border-radius: 12px;
    background: #f8f8f8;
}

.cart-product-name {
    font-size: 14px;
    font-weight: 700;
    color: #252942;
    text-decoration: none;
}

.cart-product-name:hover {
    color: #ff4d6d;
}

.cart-price {
    font-weight: 600;
    color: #444;
    white-space: nowrap;
}

.qty-input {
    width: 65px;
    height: 38px;
    text-align: center;
    border: 1px solid #ddd;
    border-radius: 7px;
    outline: none;
}

.qty-input:focus {
    border-color: #ff4d6d;
}

.cart-subtotal {
    color: #252942;
    font-weight: 700;
    white-space: nowrap;
}

.btn-remove {
    color: #ff4d6d;
    background: #fff0f3;
    width: 36px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    text-decoration: none;
    transition: .3s;
}

.btn-remove:hover {
    background: #ff4d6d;
    color: #fff;
}

/* SUMMARY */
.summary-card {
    background: #fff;
    border: 1px solid #eee;
    border-radius: 16px;
    padding: 25px;
    position: sticky;
    top: 25px;
}

.summary-title {
    font-size: 19px;
    font-weight: 800;
    color: #252942;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 17px;
    font-size: 14px;
    color: #666;
}

.summary-total {
    font-size: 18px;
    font-weight: 800;
    color: #252942;
}

.checkout-btn {
    background: #2e3254;
    color: #fff;
    border: none;
    padding: 14px;
    width: 100%;
    border-radius: 9px;
    font-size: 13px;
    font-weight: 800;
    letter-spacing: .5px;
    transition: .3s;
    text-decoration: none;
    display: block;
    text-align: center;
}

.checkout-btn:hover {
    background: #ff4d6d;
    color: #fff;
    transform: translateY(-2px);
}

.continue-shopping {
    color: #252942;
    text-decoration: none;
    font-weight: 700;
    font-size: 14px;
}

.continue-shopping:hover {
    color: #ff4d6d;
}

/* EMPTY CART */
.empty-cart {
    padding: 65px 20px;
    text-align: center;
    background: #fff;
    border-radius: 16px;
    border: 1px solid #eee;
}

.empty-cart-icon {
    width: 90px;
    height: 90px;
    background: #fff0f3;
    color: #ff4d6d;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    font-size: 35px;
}

.shop-btn {
    display: inline-block;
    background: #2e3254;
    color: #fff;
    text-decoration: none;
    padding: 12px 25px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 700;
    margin-top: 12px;
}

.shop-btn:hover {
    background: #ff4d6d;
    color: #fff;
}

/* MOBILE RESPONSIVE */
@media (max-width: 768px) {

    .cart-container {
        margin-top: 30px;
    }

    .cart-heading {
        font-size: 24px;
    }

    .cart-table thead {
        display: none;
    }

    .cart-table tbody tr {
        display: block;
        padding: 12px 15px;
        border-bottom: 1px solid #eee;
    }

    .cart-table tbody td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        padding: 9px 0;
        border: none;
        text-align: right;
    }

    .cart-table tbody td:first-child {
        display: block;
        text-align: left;
        border-bottom: 1px solid #eee;
        padding-bottom: 14px;
        margin-bottom: 5px;
    }

    .cart-table tbody td:not(:first-child)::before {
        content: attr(data-label);
        font-size: 12px;
        font-weight: 700;
        color: #777;
        text-transform: uppercase;
    }

    .cart-table tbody td:last-child {
        justify-content: flex-end;
    }

    .product-img-cart {
        width: 70px;
        height: 70px;
    }

    .summary-card {
        position: static;
        padding: 20px;
    }

}
</style>


<div class="container cart-container">

    <!-- PAGE HEADING -->
    <div class="text-center mb-5">
        <h2 class="cart-heading">
            <i class="fa fa-shopping-cart me-2"></i>
            Your Shopping Cart
        </h2>

        <p class="cart-subtitle">
            Manage your pet's favorites before checkout.
        </p>
    </div>

    <?php if (!empty($cart)) { ?>

    <div class="row g-4">

        <!-- CART PRODUCTS -->
        <div class="col-lg-8">

            <div class="cart-box">
                <div class="table-responsive">

                    <table class="table cart-table">

                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Color</th>
                                <th>Size</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php foreach ($cart as $id => $item) {

                                $price = (float)($item['price'] ?? 0);
                                $qty = max(1, (int)($item['qty'] ?? 1));
                                $sub = $price * $qty;
                                $total += $sub;

                                $image = htmlspecialchars($item['image'] ?? '');
                                $name = htmlspecialchars($item['name'] ?? 'Product');
                                $productId = (int)$id;

                                $color = htmlspecialchars($item['color'] ?? '');
                                $size = htmlspecialchars($item['size'] ?? '');
                            ?>

                            <tr>

                                <!-- PRODUCT -->
                                <td data-label="Product">
                                    <div class="d-flex align-items-center">

                                        <img
                                            src="images/<?= $image ?>"
                                            class="product-img-cart me-3"
                                            alt="<?= $name ?>"
                                        >

                                        <div>
                                            <a href="product-details.php?id=<?= $productId ?>"
                                               class="cart-product-name">
                                                <?= $name ?>
                                            </a>

                                            <small class="d-block text-muted mt-1">
                                                SAM'S Pets Store
                                            </small>
                                        </div>

                                    </div>
                                </td>

                                <!-- PRICE -->
                                <td data-label="Price">
                                    <span class="cart-price">
                                        ₹<?= number_format($price, 2) ?>
                                    </span>
                                </td>

                                <!-- COLOR -->
                                <td data-label="Color">
                                    <?php if ($color !== ''): ?>
                                        <span class="cart-variant">
                                            <?= $color ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>

                                <!-- SIZE -->
                                <td data-label="Size">
                                    <?php if ($size !== ''): ?>
                                        <span class="cart-variant">
                                            <?= $size ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>

                                <!-- QUANTITY -->
                                <td data-label="Quantity">
                                    <input
                                        type="number"
                                        value="<?= $qty ?>"
                                        min="1"
                                        class="qty-input"
                                        disabled
                                    >
                                </td>

                                <!-- SUBTOTAL -->
                                <td data-label="Subtotal">
                                    <span class="cart-subtotal">
                                        ₹<?= number_format($sub, 2) ?>
                                    </span>
                                </td>

                                <!-- REMOVE -->
                                <td class="text-end">
                                    <a
                                        href="remove_item.php?id=<?= $productId ?>"
                                        class="btn-remove"
                                        title="Remove Product"
                                    >
                                        <i class="fa fa-trash-can"></i>
                                    </a>
                                </td>

                            </tr>

                            <?php } ?>

                        </tbody>

                    </table>

                </div>
            </div>

            <!-- CONTINUE SHOPPING -->
            <div class="mt-4">
                <a href="shop.php" class="continue-shopping">
                    <i class="fa fa-arrow-left me-2"></i>
                    Continue Shopping
                </a>
            </div>

        </div>

        <!-- ORDER SUMMARY -->
        <div class="col-lg-4">

            <div class="summary-card shadow-sm">

                <h5 class="summary-title mb-4">
                    Order Summary
                </h5>

                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>₹<?= number_format($total, 2) ?></span>
                </div>

                <div class="summary-row">
                    <span>Shipping</span>
                    <span class="text-success fw-bold">FREE</span>
                </div>

                <hr>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <span class="summary-total">Grand Total</span>

                    <span class="summary-total text-danger">
                        ₹<?= number_format($total, 2) ?>
                    </span>
                </div>

                <a href="checkout.php" class="checkout-btn shadow-sm">
                    PROCEED TO CHECKOUT
                    <i class="fa fa-arrow-right ms-2"></i>
                </a>

                <!-- PAYMENT ICONS -->
                <div class="text-center mt-4">
                    <small class="d-block text-muted mb-2">
                        Secure Payment Options
                    </small>

                    <img src="https://img.icons8.com/color/48/000000/visa.png"
                         width="35" alt="Visa">

                    <img src="https://img.icons8.com/color/48/000000/mastercard.png"
                         width="35" alt="Mastercard">

                    <img src="https://img.icons8.com/color/48/000000/upi.png"
                         width="35" alt="UPI">
                </div>

            </div>

        </div>

    </div>

    <?php } else { ?>

    <!-- EMPTY CART -->
    <div class="empty-cart">

        <div class="empty-cart-icon">
            <i class="fa fa-shopping-basket"></i>
        </div>

        <h4 class="fw-bold mb-2">
            Your Cart is Empty!
        </h4>

        <p class="text-muted">
            Looks like you haven't added anything to your cart yet.
        </p>

        <a href="shop.php" class="shop-btn">
            <i class="fa fa-shopping-bag me-2"></i>
            Start Shopping
        </a>

    </div>

    <?php } ?>

</div>

<?php include "include/footer.php"; ?>
