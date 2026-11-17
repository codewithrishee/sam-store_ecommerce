<?php
session_start();
include("include/header.php"); // Navbar dikhega
$order_id = isset($_GET['id']) ? $_GET['id'] : '#000';
?>

<div class="container py-5 mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <div class="mb-4">
                <i class="fas fa-check-circle text-success" style="font-size: 80px;"></i>
            </div>
            
            <h2 class="fw-bold">Thank You for Your Order!</h2>
            <p class="text-muted">Your order has been placed successfully. Our team will contact you shortly for delivery.</p>
            
            <div class="card border-0 bg-light p-4 my-4 rounded-4 shadow-sm">
                <h6 class="text-uppercase text-muted small fw-bold">Order Number</h6>
                <h4 class="text-dark fw-bold">#SAM-ORD-<?= $order_id ?></h4>
                <p class="mb-0 mt-2 text-secondary">A confirmation email has been sent to your registered email.</p>
            </div>

            <div class="d-flex gap-3 justify-content-center">
                <a href="index.php" class="btn btn-dark px-4 py-2 rounded-3">
                    <i class="fa fa-home me-2"></i> Back to Home
                </a>
                <a href="shop.php" class="btn btn-outline-dark px-4 py-2 rounded-3">
                    <i class="fa fa-shopping-bag me-2"></i> Continue Shopping
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    body { background-color: #fcfcfc; }
    .fa-check-circle {
        animation: scaleIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    @keyframes scaleIn {
        from { transform: scale(0); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }
</style>

<?php include("include/footer.php"); ?>