<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "config/db.php";
include "include/header.php";

$total = 0;
// Agar cart khali hai toh wapas shop par bhej do
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo "<script>window.location.href='shop.php';</script>";
    exit;
}
?>

<style>
    .checkout-container {
        margin-top: 40px;
        margin-bottom: 80px;
    }

    .checkout-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        padding: 30px;
        background: #fff;
    }

    .summary-card {
        border: none;
        border-radius: 15px;
        background: #f8f9fa;
        position: sticky;
        top: 120px;
    }

    .form-floating>.form-control {
        border-radius: 10px;
        border: 1px solid #eee;
    }

    .form-floating>.form-control:focus {
        border-color: #2e3254;
        box-shadow: none;
    }

    .payment-option {
        border: 1px solid #eee;
        padding: 15px;
        border-radius: 10px;
        cursor: pointer;
        transition: 0.3s;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .payment-option:hover {
        border-color: #ff4d6d;
        background: #fff5f6;
    }

    .order-item-img {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 8px;
    }

    .place-order-btn {
        background: #2e3254;
        color: #fff;
        border: none;
        padding: 15px;
        border-radius: 10px;
        font-weight: 600;
        width: 100%;
        transition: 0.3s;
    }

    .place-order-btn:hover {
        background: #ff4d6d;
        transform: translateY(-2px);
    }

    @media (max-width: 768px) {
        .checkout-container {
            margin-top: 20px;
        }

        .summary-card {
            position: static;
            margin-top: 20px;
        }
    }
</style>

<div class="container checkout-container">
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="checkout-card">
                <h4 class="fw-bold mb-4"><i class="fa fa-truck me-2 text-danger"></i> Shipping Details</h4>

                <form action="place_order.php" method="POST">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <div class="form-floating">
                                <input type="text" name="name" class="form-control" id="name" placeholder="Full Name"
                                    required>
                                <label for="name">Full Name</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="email" name="email" class="form-control" id="email"
                                    placeholder="name@example.com" required>
                                <label for="email">Email Address</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" name="phone" class="form-control" id="phone"
                                    placeholder="Phone Number" required>
                                <label for="phone">Phone Number</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-floating">
                                <textarea name="address" class="form-control" placeholder="Address" id="address"
                                    style="height: 100px" required></textarea>
                                <label for="address">Complete Address (House No, Street, City)</label>
                            </div>
                        </div>
                    </div>

                    <h5 class="fw-bold mt-5 mb-3">Payment Method</h5>
                    <div class="payment-option mb-4">
                        <input type="radio" name="payment" value="cod" id="cod" checked>
                        <label for="cod" class="mb-0 flex-grow-1">
                            <i class="fa fa-money-bill-wave me-2 text-success"></i>
                            <strong>Cash on Delivery (COD)</strong>
                            <div class="small text-muted">Pay when your pets items arrive.</div>
                        </label>
                    </div>

                    <button type="submit" class="place-order-btn shadow-sm">
                        PLACE ORDER NOW <i class="fa fa-arrow-right ms-2"></i>
                    </button>
                </form>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card summary-card p-4 shadow-sm">
                <h5 class="fw-bold mb-4">Order Summary</h5>

                <div class="order-items-list mb-3">
                    <?php
                    foreach ($_SESSION['cart'] as $id => $item):
                        $sub = $item['price'] * $item['qty'];
                        $total += $sub;
                        ?>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <img src="images/<?= $item['image'] ?>" class="order-item-img me-3 border">
                                <div>
                                    <h6 class="mb-0 fw-bold" style="font-size: 0.9rem;"><?= $item['name'] ?></h6>
                                    <small class="text-muted d-block">
                                        Qty: <?= (int) $item['qty'] ?>
                                    </small>

                                    <?php if (!empty($item['color'])): ?>
                                        <small class="text-muted d-block">
                                            Color: <?= htmlspecialchars($item['color']) ?>
                                        </small>
                                    <?php endif; ?>

                                    <?php if (!empty($item['size'])): ?>
                                        <small class="text-muted d-block">
                                            Size: <?= htmlspecialchars($item['size']) ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <span class="fw-bold">₹<?= number_format($sub, 2) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Subtotal</span>
                    <span>₹<?= number_format($total, 2) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Shipping</span>
                    <span class="text-success fw-bold">FREE</span>
                </div>
                <div class="d-flex justify-content-between mt-3">
                    <h5 class="fw-bold">Grand Total</h5>
                    <h5 class="fw-bold text-danger">₹<?= number_format($total, 2) ?></h5>
                </div>

                <div class="mt-4 p-3 bg-white rounded-3 border">
                    <small class="text-muted d-block mb-1"><i class="fa fa-shield-alt text-primary me-1"></i> Secure
                        Checkout</small>
                    <p class="mb-0 small" style="font-size: 11px;">Your personal data will be used to process your order
                        and for other purposes described in our privacy policy.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "include/footer.php"; ?>