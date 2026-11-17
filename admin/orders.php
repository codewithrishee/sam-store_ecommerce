<link rel="stylesheet" href="admin-style.css">

<?php
include("../config/db.php");
include "header.php";
include "sidebar.php";

// Fetch Stats
$total_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM orders"))['cnt'] ?? 0;
$pending_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM orders WHERE LOWER(order_status)='pending'"))['cnt'] ?? 0;
$delivered_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM orders WHERE LOWER(order_status)='delivered'"))['cnt'] ?? 0;
$total_revenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_amount) as total FROM orders WHERE LOWER(order_status) IN ('delivered','completed')"))['total'] ?? 0;
?>

<style>
    /* ==========================================================
       ORDERS PAGE — PREMIUM STYLES
       ========================================================== */
    body { background: #f4f7f6; font-family: 'Manrope', sans-serif; }
    .main-content { margin-left: 290px; margin-top: 20px; padding: 30px 40px; min-height: calc(100vh - 75px); }

    /* ================= STATS ROW ================= */
    .stat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 18px;
        margin-bottom: 28px;
    }
    .stat-box {
        background: #fff;
        border: 1px solid #efe6ec;
        border-radius: 20px;
        padding: 22px 24px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: 0 4px 15px rgba(46, 50, 84, 0.03);
        transition: all 0.35s ease;
        position: relative;
        overflow: hidden;
    }
    .stat-box::before {
        content: '';
        position: absolute;
        bottom: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, #ff4d6d, #e63950);
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.4s ease;
    }
    .stat-box:hover { transform: translateY(-4px); box-shadow: 0 12px 30px rgba(46, 50, 84, 0.08); }
    .stat-box:hover::before { transform: scaleX(1); }

    .stat-icon {
        width: 52px; height: 52px;
        border-radius: 14px;
        display: grid; place-items: center;
        font-size: 1.3rem;
        flex: 0 0 52px;
    }
    .icon-pink { background: #fff2f5; color: #ff4d6d; }
    .icon-orange { background: #ffedd5; color: #ea580c; }
    .icon-green { background: #dcfce7; color: #16a34a; }
    .icon-blue { background: #e0f2fe; color: #0284c7; }

    .stat-info p {
        font-family: 'Outfit', sans-serif;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #8a8fa3;
        margin: 0 0 4px;
    }
    .stat-info h4 {
        font-family: 'Outfit', sans-serif;
        font-size: 1.5rem;
        font-weight: 900;
        color: #2e3254;
        margin: 0;
        line-height: 1;
    }

    /* ================= TABLE ================= */
    .table-card {
        background: #fff;
        border-radius: 20px;
        overflow: hidden;
        border: 1px solid #efe6ec;
        box-shadow: 0 4px 15px rgba(46, 50, 84, 0.03);
    }
    .page-title {
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        color: #2e3254;
        font-size: 1.5rem;
    }
    .table thead th {
        background: #fafbfc;
        border-bottom: 1px solid #efe6ec;
        padding: 18px 24px;
        font-size: 0.72rem;
        text-transform: uppercase;
        color: #8a8fa3;
        font-weight: 800;
        letter-spacing: 0.05em;
    }
    .table tbody td {
        padding: 18px 24px;
        vertical-align: middle;
        border-bottom: 1px solid #f8f9fa;
        color: #56607a;
        font-size: 0.9rem;
    }
    .table tbody tr { transition: background 0.2s ease; }
    .table tbody tr:hover { background: #fafbfc; }

    /* Customer Cell */
    .customer-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .customer-avatar {
        width: 42px; height: 42px;
        border-radius: 50%;
        background: linear-gradient(135deg, #fff2f5, #ffe0e7);
        color: #ff4d6d;
        display: grid; place-items: center;
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        font-size: 0.95rem;
        flex: 0 0 42px;
        border: 2px solid #fff;
        box-shadow: 0 4px 10px rgba(255, 77, 109, 0.15);
    }
    .customer-cell .info h6 {
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        color: #2e3254;
        font-size: 0.9rem;
        margin: 0 0 2px;
    }
    .customer-cell .info small {
        color: #8a8fa3;
        font-size: 0.75rem;
    }

    /* Status Badges */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 999px;
        font-family: 'Outfit', sans-serif;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }
    .status-badge i { font-size: 0.55rem; }
    .status-pending { background: #ffedd5; color: #ea580c; }
    .status-processing { background: #e0f2fe; color: #0284c7; }
    .status-shipped { background: #f3e8ff; color: #9333ea; }
    .status-completed, .status-delivered { background: #dcfce7; color: #16a34a; }
    .status-cancelled { background: #fee2e2; color: #dc2626; }
    .status-default { background: #f1f5f9; color: #56607a; }

    /* Price */
    .price-cell {
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        color: #16a34a;
        font-size: 0.95rem;
    }

    /* Date */
    .date-cell { font-size: 0.85rem; color: #56607a; font-weight: 600; }
    .date-cell small { display: block; color: #8a8fa3; font-weight: 500; font-size: 0.75rem; margin-top: 2px; }

    /* View Button */
    .btn-view {
        background: #fff2f5;
        color: #ff4d6d;
        border: 1px solid rgba(255, 77, 109, 0.2);
        border-radius: 10px;
        padding: 8px 16px;
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        font-size: 0.78rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.3s ease;
    }
    .btn-view:hover {
        background: linear-gradient(135deg, #ff4d6d, #e63950);
        border-color: transparent;
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(255, 77, 109, 0.3);
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #8a8fa3;
    }
    .empty-state i { font-size: 3rem; color: #efe6ec; margin-bottom: 16px; }
    .empty-state h5 { font-family: 'Outfit', sans-serif; font-weight: 800; color: #2e3254; margin-bottom: 6px; }
    .empty-state p { font-size: 0.9rem; margin: 0; }

    @media (max-width: 991px) {
        .main-content { margin-left: 85px; padding: 20px; margin-top: 65px; }
        .stat-grid { grid-template-columns: repeat(2, 1fr); }
        .stat-icon { width: 44px; height: 44px; font-size: 1.1rem; flex: 0 0 44px; }
        .stat-info h4 { font-size: 1.2rem; }
    }
    @media (max-width: 575.98px) {
        .stat-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="main-content">

    <!-- ================= STATS ROW ================= -->
    <div class="stat-grid">
        <div class="stat-box">
            <div class="stat-icon icon-pink"><i class="fa-solid fa-receipt"></i></div>
            <div class="stat-info">
                <p>Total Orders</p>
                <h4><?= number_format($total_orders); ?></h4>
            </div>
        </div>

        <div class="stat-box">
            <div class="stat-icon icon-orange"><i class="fa-solid fa-clock"></i></div>
            <div class="stat-info">
                <p>Pending</p>
                <h4><?= number_format($pending_orders); ?></h4>
            </div>
        </div>

        <div class="stat-box">
            <div class="stat-icon icon-green"><i class="fa-solid fa-circle-check"></i></div>
            <div class="stat-info">
                <p>Delivered</p>
                <h4><?= number_format($delivered_orders); ?></h4>
            </div>
        </div>

        <div class="stat-box">
            <div class="stat-icon icon-blue"><i class="fa-solid fa-indian-rupee-sign"></i></div>
            <div class="stat-info">
                <p>Revenue</p>
                <h4>₹<?= number_format($total_revenue, 0); ?></h4>
            </div>
        </div>
    </div>

    <!-- ================= TABLE CARD ================= -->
    <div class="table-card">
        <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
            <h3 class="page-title m-0">All Orders</h3>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $q = mysqli_query($conn, "SELECT * FROM orders ORDER BY id DESC");
                    if (mysqli_num_rows($q) > 0) {
                        while ($row = mysqli_fetch_assoc($q)) {
                            $status = strtolower($row['order_status'] ?? '');
                            $initial = strtoupper(substr($row['name'] ?? 'U', 0, 1));

                            $statusClass = 'status-default';
                            $statusIcon = 'fa-circle';
                            $statusLabel = ucfirst($row['order_status']);

                            if ($status == 'pending') { $statusClass = 'status-pending'; $statusIcon = 'fa-clock'; $statusLabel = 'Pending'; }
                            elseif ($status == 'processing') { $statusClass = 'status-processing'; $statusIcon = 'fa-gear'; $statusLabel = 'Processing'; }
                            elseif ($status == 'shipped') { $statusClass = 'status-shipped'; $statusIcon = 'fa-truck-fast'; $statusLabel = 'Shipped'; }
                            elseif ($status == 'delivered' || $status == 'completed') { $statusClass = 'status-completed'; $statusIcon = 'fa-circle-check'; $statusLabel = ucfirst($row['order_status']); }
                            elseif ($status == 'cancelled') { $statusClass = 'status-cancelled'; $statusIcon = 'fa-circle-xmark'; $statusLabel = 'Cancelled'; }
                    ?>
                    <tr>
                        <td><span class="fw-bold text-dark">#<?= $row['id']; ?></span></td>
                        <td>
                            <div class="customer-cell">
                                <div class="customer-avatar"><?= $initial; ?></div>
                                <div class="info">
                                    <h6><?= htmlspecialchars($row['name']); ?></h6>
                                    <small><?= htmlspecialchars($row['email']); ?></small>
                                </div>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($row['phone']); ?></td>
                        <td class="price-cell">₹<?= number_format($row['total_amount'], 2); ?></td>
                        <td>
                            <span class="status-badge <?= $statusClass; ?>">
                                <i class="fa-solid <?= $statusIcon; ?>"></i>
                                <?= htmlspecialchars($statusLabel); ?>
                            </span>
                        </td>
                        <td class="date-cell">
                            <?= date("d M Y", strtotime($row['created_at'])); ?>
                            <small><?= date("h:i A", strtotime($row['created_at'])); ?></small>
                        </td>
                        <td>
                            <a href="order_details.php?id=<?= $row['id']; ?>" class="btn-view">
                                <i class="fa-solid fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    <?php } } else { ?>
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <i class="fa-solid fa-receipt"></i>
                                <h5>No Orders Yet</h5>
                                <p>Orders will appear here once customers start purchasing.</p>
                            </div>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

</div>