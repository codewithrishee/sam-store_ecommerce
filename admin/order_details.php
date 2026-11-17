<link rel="stylesheet" href="admin-style.css">

<?php
include("../config/db.php");
include "header.php";
include "sidebar.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo "<div class='main-content'><div class='container mt-4'><h3>Invalid Order ID</h3></div></div>";
    exit;
}

$order = mysqli_query($conn, "SELECT * FROM orders WHERE id=$id");
$data = mysqli_fetch_assoc($order);

if (!$data) {
    echo "<div class='main-content'><div class='container mt-4'><h3>Order Not Found</h3></div></div>";
    exit;
}

// Fetch items count
$item_count_query = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM order_items WHERE order_id=$id");
$item_count = mysqli_fetch_assoc($item_count_query)['cnt'] ?? 0;

$status = strtolower($data['order_status'] ?? 'pending');
?>

<style>
    /* ==========================================================
       ORDER DETAILS — PREMIUM STYLES
       ========================================================== */
    body {
        background: #f4f7f6;
        font-family: 'Manrope', sans-serif;
    }

    .main-content {
        margin-left: 290px;
        margin-top: 20px;
        padding: 30px 40px;
        min-height: calc(100vh - 75px);
    }

    /* ================= HERO BANNER ================= */
    .order-hero {
        position: relative;
        background: linear-gradient(135deg, #1f2340 0%, #2e3254 100%);
        border-radius: 24px;
        padding: 32px 40px;
        margin-bottom: 28px;
        overflow: hidden;
        box-shadow: 0 15px 40px rgba(46, 50, 84, 0.15);
    }

    .order-hero::before {
        content: '';
        position: absolute;
        top: -80px;
        right: -80px;
        width: 280px;
        height: 280px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(255, 77, 109, 0.25) 0%, transparent 70%);
        pointer-events: none;
    }

    .order-hero::after {
        content: '';
        position: absolute;
        bottom: -120px;
        left: 40%;
        width: 350px;
        height: 350px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(14, 165, 164, 0.1) 0%, transparent 70%);
        pointer-events: none;
    }

    .order-hero-content {
        position: relative;
        z-index: 2;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
    }

    .order-hero-label {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: rgba(255, 255, 255, 0.6);
        font-family: 'Outfit', sans-serif;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        margin-bottom: 8px;
    }

    .order-hero-title {
        font-family: 'Outfit', sans-serif;
        font-weight: 900;
        color: #fff;
        font-size: 1.8rem;
        letter-spacing: -0.02em;
        margin: 0;
    }

    .order-hero-title span {
        color: #ff4d6d;
    }

    .order-hero-meta {
        display: flex;
        gap: 24px;
        margin-top: 12px;
        flex-wrap: wrap;
    }

    .order-hero-meta div {
        color: rgba(255, 255, 255, 0.75);
        font-size: 0.85rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .order-hero-meta i {
        color: #ff4d6d;
    }

    .hero-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .btn-hero {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 11px 22px;
        border-radius: 999px;
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        font-size: 0.82rem;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        text-decoration: none;
        transition: all 0.3s ease;
        cursor: pointer;
        border: none;
    }

    .btn-hero-light {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: #fff;
    }

    .btn-hero-light:hover {
        background: rgba(255, 255, 255, 0.2);
        color: #fff;
    }

    .btn-hero-primary {
        background: linear-gradient(135deg, #ff4d6d, #e63950);
        color: #fff;
        box-shadow: 0 8px 20px rgba(255, 77, 109, 0.3);
    }

    .btn-hero-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 25px rgba(255, 77, 109, 0.4);
        color: #fff;
    }

    /* ================= CARDS ================= */
    .page-card {
        background: #fff;
        border-radius: 20px;
        padding: 28px;
        border: 1px solid #efe6ec;
        box-shadow: 0 4px 15px rgba(46, 50, 84, 0.03);
    }

    .card-title {
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        color: #2e3254;
        font-size: 1.1rem;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        padding-bottom: 14px;
        border-bottom: 2px dashed #efe6ec;
    }

    .card-title i {
        color: #ff4d6d;
    }

    /* Customer Info List */
    .customer-info-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .customer-info-list li {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        padding: 14px 0;
        border-bottom: 1px dashed #f1f5f9;
    }

    .customer-info-list li:last-child {
        border-bottom: 0;
    }

    .customer-info-list .info-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: #fff2f5;
        color: #ff4d6d;
        display: grid;
        place-items: center;
        font-size: 0.9rem;
        flex: 0 0 38px;
    }

    .customer-info-list .info-body small {
        display: block;
        font-family: 'Outfit', sans-serif;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #8a8fa3;
        margin-bottom: 3px;
    }

    .customer-info-list .info-body strong {
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        color: #2e3254;
        font-size: 0.92rem;
        word-break: break-word;
    }

    /* Status Form */
    .status-form-box {
        background: #fafbfc;
        border: 1px solid #efe6ec;
        border-radius: 14px;
        padding: 18px;
        margin-top: 20px;
    }

    .status-form-box label {
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        color: #2e3254;
        font-size: 0.8rem;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        margin-bottom: 10px;
        display: block;
    }

    .form-select {
        border-radius: 10px;
        border: 1.5px solid #efe6ec;
        padding: 10px 14px;
        font-family: 'Manrope', sans-serif;
        font-size: 0.9rem;
        color: #2e3254;
        background-color: #fff;
        box-shadow: none;
        transition: all 0.3s ease;
    }

    .form-select:focus {
        border-color: #ff4d6d;
        box-shadow: 0 0 0 4px #fff2f5;
    }

    .btn-admin {
        background: linear-gradient(135deg, #ff4d6d, #e63950);
        color: #fff;
        border: none;
        padding: 10px 22px;
        border-radius: 999px;
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        font-size: 0.8rem;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        box-shadow: 0 8px 20px rgba(255, 77, 109, 0.25);
        transition: all 0.3s ease;
        cursor: pointer;
        white-space: nowrap;
    }

    .btn-admin:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 25px rgba(255, 77, 109, 0.35);
    }

    /* Items Table */
    .items-table thead th {
        background: #fafbfc;
        border-bottom: 1px solid #efe6ec;
        padding: 14px 20px;
        font-size: 0.72rem;
        text-transform: uppercase;
        color: #8a8fa3;
        font-weight: 800;
        letter-spacing: 0.05em;
    }

    .items-table tbody td {
        padding: 16px 20px;
        border-bottom: 1px solid #f8f9fa;
        color: #56607a;
        font-size: 0.9rem;
        vertical-align: middle;
    }

    .items-table tbody tr:hover {
        background: #fafbfc;
    }

    .items-table .product-name {
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        color: #2e3254;
    }

    .items-table .item-total {
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        color: #16a34a;
    }

    .items-table tfoot th {
        padding: 18px 20px;
        background: #fafbfc;
        border-top: 2px solid #efe6ec;
    }

    .grand-total-label {
        font-family: 'Outfit', sans-serif;
        font-size: 0.95rem;
        font-weight: 700;
        color: #2e3254;
    }

    .grand-total-value {
        font-family: 'Outfit', sans-serif;
        font-weight: 900;
        color: #ff4d6d;
        font-size: 1.3rem;
    }

    /* Empty state */
    .empty-items {
        text-align: center;
        padding: 40px;
        color: #8a8fa3;
    }

    .empty-items i {
        font-size: 2rem;
        color: #efe6ec;
        margin-bottom: 10px;
    }

    @media (max-width: 991px) {
        .main-content {
            margin-left: 85px;
            padding: 20px;
            margin-top: 65px;
        }

        .order-hero {
            padding: 24px 22px;
        }

        .order-hero-title {
            font-size: 1.4rem;
        }

        .hero-actions {
            width: 100%;
        }

        .btn-hero {
            flex: 1;
            justify-content: center;
        }
    }
</style>

<div class="main-content">

    <!-- ================= HERO ================= -->
    <div class="order-hero">
        <div class="order-hero-content">
            <div>
                <div class="order-hero-label"><i class="fa-solid fa-receipt"></i> Order Details</div>
                <h1 class="order-hero-title">Order <span>#<?= $data['id']; ?></span></h1>
                <div class="order-hero-meta">
                    <div>
                        <i class="fa-solid fa-calendar"></i>
                        <?= date("d M Y, h:i A", strtotime($data['created_at'])); ?>
                    </div>
                    <div>
                        <i class="fa-solid fa-box"></i>
                        <?= $item_count; ?> Item<?= $item_count != 1 ? 's' : ''; ?>
                    </div>
                    <div>
                        <i class="fa-solid fa-indian-rupee-sign"></i>
                        <?= number_format($data['total_amount'], 2); ?>
                    </div>
                </div>
            </div>
            <div class="hero-actions">
                <button onclick="window.print()" class="btn-hero btn-hero-light">
                    <i class="fa-solid fa-print"></i> Print
                </button>
                <a href="orders.php" class="btn-hero btn-hero-primary">
                    <i class="fa-solid fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>

    <div class="row g-4">

        <!-- ================= CUSTOMER INFO ================= -->
        <div class="col-lg-4">
            <div class="page-card h-100">
                <h5 class="card-title"><i class="fa-solid fa-user"></i> Customer Info</h5>

                <ul class="customer-info-list">
                    <li>
                        <div class="info-icon"><i class="fa-solid fa-user"></i></div>
                        <div class="info-body">
                            <small>Customer Name</small>
                            <strong><?= htmlspecialchars($data['name']); ?></strong>
                        </div>
                    </li>
                    <li>
                        <div class="info-icon"><i class="fa-solid fa-envelope"></i></div>
                        <div class="info-body">
                            <small>Email Address</small>
                            <strong><?= htmlspecialchars($data['email']); ?></strong>
                        </div>
                    </li>
                    <li>
                        <div class="info-icon"><i class="fa-solid fa-phone"></i></div>
                        <div class="info-body">
                            <small>Phone Number</small>
                            <strong><?= htmlspecialchars($data['phone']); ?></strong>
                        </div>
                    </li>
                    <li>
                        <div class="info-icon"><i class="fa-solid fa-location-dot"></i></div>
                        <div class="info-body">
                            <small>Shipping Address</small>
                            <strong><?= htmlspecialchars($data['address']); ?></strong>
                        </div>
                    </li>
                </ul>

                <!-- STATUS UPDATE FORM -->
                <div class="status-form-box">
                    <form action="update_order_status.php" method="POST">
                        <input type="hidden" name="order_id" value="<?= $id ?>">

                        <label><i class="fa-solid fa-gear me-1"></i> Update Order Status</label>

                        <div class="d-flex gap-2 align-items-center flex-wrap">
                            <select name="status" class="form-select" style="flex:1; min-width:150px;">
                                <?php
                                $statuses = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
                                foreach ($statuses as $s) {
                                    $selected = ($data['order_status'] == $s) ? 'selected' : '';
                                    echo "<option value='$s' $selected>$s</option>";
                                }
                                ?>
                            </select>
                            <button type="submit" class="btn-admin">
                                <i class="fa-solid fa-check me-1"></i> Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ================= ORDER ITEMS ================= -->
        <div class="col-lg-8">
            <div class="page-card h-100">
                <h5 class="card-title"><i class="fa-solid fa-boxes-stacked"></i> Order Items</h5>

                <div class="table-responsive">
                    <table class="table items-table mb-0">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th class="text-center">Color</th>
                                <th class="text-center">Size</th>
                                <th class="text-center">Price</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $items = mysqli_query($conn, "SELECT * FROM order_items WHERE order_id=$id");

                            if (mysqli_num_rows($items) > 0) {
                                while ($item = mysqli_fetch_assoc($items)) {

                                    $color = trim($item['color'] ?? '');
                                    $size = trim($item['size'] ?? '');
                                    ?>
                                    <tr>
                                        <td class="product-name">
                                            <?= htmlspecialchars($item['product_name']); ?>
                                        </td>

                                        <td class="text-center">
                                            <?php if ($color !== ''): ?>
                                                <span class="badge bg-light text-dark border px-3 py-2">
                                                    <?= htmlspecialchars($color); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>

                                        <td class="text-center">
                                            <?php if ($size !== ''): ?>
                                                <span class="badge bg-light text-dark border px-3 py-2">
                                                    <?= htmlspecialchars($size); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>

                                        <td class="text-center">
                                            ₹<?= number_format((float) $item['price'], 2); ?>
                                        </td>

                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border px-3 py-2">
                                                ×<?= (int) $item['qty']; ?>
                                            </span>
                                        </td>

                                        <td class="text-end item-total">
                                            ₹<?= number_format((float) $item['total'], 2); ?>
                                        </td>
                                    </tr>

                                    <?php
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="6">
                                        <div class="empty-items">
                                            <i class="fa-solid fa-box-open"></i>
                                            <p class="mb-0">No items found in this order.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="5" class="text-end grand-total-label">
                                    Grand Total:
                                </th>
                                <th class="text-end grand-total-value">
                                    ₹<?= number_format($data['total_amount'], 2); ?>
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

    </div>

</div>