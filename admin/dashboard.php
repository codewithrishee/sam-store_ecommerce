<?php
include "../config/db.php";
include "header.php";
include "sidebar.php";

// Stats Fetch (Cleaned up duplicates)
$total_products   = $conn->query("SELECT id FROM products")->num_rows ?? 0;
$total_categories = $conn->query("SELECT id FROM categories")->num_rows ?? 0;
$total_banners    = $conn->query("SELECT id FROM banners")->num_rows ?? 0;
$total_orders     = $conn->query("SELECT id FROM orders")->num_rows ?? 0;

// Fetch Admin Data
$adminQuery = $conn->query("SELECT * FROM admins ORDER BY id DESC LIMIT 1");
$admin = $adminQuery->fetch_assoc();
$admin_name = $admin['name'] ?? 'Admin';
$current_date = date("d F Y");
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

<style>
/* ==========================================================
   DASHBOARD — PREMIUM STYLES (Navy + Pink Theme)
   ========================================================== */
:root {
    --ink: #2e3254;
    --ink-2: #1f2340;
    --brand: #ff4d6d;
    --brand-2: #e63950;
    --brand-soft: #fff2f5;
    --line: #efe6ec;
    --muted: #8a8fa3;
    --ease: cubic-bezier(.2, .8, .2, 1);
}

body {
    background: #f4f7f6;
    font-family: 'Manrope', sans-serif;
    overflow-x: hidden;
}

/* MAIN CONTENT WRAPPER */
.main-content {
    margin-left: 290px;
    width: calc(100% - 290px);
    min-height: 100vh;

    /* Fixed navbar ke neeche content */
    padding: 110px 40px 35px;

    transition: margin-left .3s var(--ease),
                width .3s var(--ease);
}
/* ================= TOP HEADER ================= */
.top-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 35px;
    flex-wrap: wrap;
    gap: 20px;
}

.top-header h2 {
    font-family: 'Outfit', sans-serif;
    font-weight: 800;
    color: var(--ink);
    font-size: 1.8rem;
    letter-spacing: -0.02em;
    margin-bottom: 5px;
}

.top-header p {
    color: var(--muted);
    font-size: 0.95rem;
    margin: 0;
}

.date-badge {
    background: #fff;
    padding: 12px 22px;
    border-radius: 14px;
    font-family: 'Outfit', sans-serif;
    font-weight: 700;
    font-size: 0.9rem;
    color: var(--brand);
    box-shadow: 0 4px 15px rgba(46, 50, 84, 0.05);
    border: 1px solid var(--line);
    display: flex;
    align-items: center;
    gap: 8px;
}

/* ================= STAT CARDS ================= */
.stat-card {
    border: 1px solid var(--line);
    border-radius: 20px;
    background: #fff;
    padding: 24px;
    transition: all .35s var(--ease);
    box-shadow: 0 4px 15px rgba(46, 50, 84, 0.03);
    height: 100%;
    position: relative;
    overflow: hidden;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(46, 50, 84, 0.08);
    border-color: rgba(255, 77, 109, 0.2);
}

.icon-box {
    width: 56px;
    height: 56px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    flex-shrink: 0;
}

/* Custom Icon Backgrounds */
.icon-pink { background: var(--brand-soft); color: var(--brand); }
.icon-blue { background: #e0f2fe; color: #0284c7; }
.icon-green { background: #dcfce7; color: #16a34a; }
.icon-orange { background: #ffedd5; color: #ea580c; }
.icon-purple { background: #f3e8ff; color: #9333ea; }

.stat-info p {
    color: var(--muted);
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    margin-bottom: 4px;
}

.stat-info h3 {
    font-family: 'Outfit', sans-serif;
    font-weight: 900;
    color: var(--ink);
    font-size: 1.7rem;
    margin: 0;
    line-height: 1;
}

/* ================= LIST CARDS (Categories, Orders) ================= */
.list-card {
    border: 1px solid var(--line);
    border-radius: 20px;
    background: #fff;
    box-shadow: 0 4px 15px rgba(46, 50, 84, 0.03);
    height: 100%;
    overflow: hidden;
}

.list-card .card-header {
    background: #fff;
    border-bottom: 1px solid var(--line);
    padding: 20px 24px;
}

.list-card .card-header h5 {
    font-family: 'Outfit', sans-serif;
    font-weight: 800;
    color: var(--ink);
    margin: 0;
    font-size: 1.1rem;
}

.list-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 24px;
    border-bottom: 1px solid #f8f9fa;
    transition: background .2s;
}

.list-item:hover {
    background: #fafbfc;
}

.list-item:last-child {
    border-bottom: none;
}

.list-item h6 {
    font-family: 'Outfit', sans-serif;
    font-weight: 700;
    color: var(--ink);
    margin-bottom: 4px;
    font-size: 0.95rem;
}

.list-item small {
    color: var(--muted);
    font-size: 0.75rem;
}

/* Custom Badges */
.badge-soft {
    padding: 6px 12px;
    border-radius: 8px;
    font-family: 'Outfit', sans-serif;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

.badge-soft.primary { background: var(--brand-soft); color: var(--brand); }
.badge-soft.success { background: #dcfce7; color: #16a34a; }
.badge-soft.warning { background: #ffedd5; color: #ea580c; }

/* ================= TABLE CARD ================= */
.table-card {
    border: 1px solid var(--line);
    border-radius: 20px;
    background: #fff;
    box-shadow: 0 4px 15px rgba(46, 50, 84, 0.03);
    overflow: hidden;
}

.table-card .card-header {
    background: #fff;
    border-bottom: 1px solid var(--line);
    padding: 20px 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.table-card .card-header h5 {
    font-family: 'Outfit', sans-serif;
    font-weight: 800;
    color: var(--ink);
    margin: 0;
}

.btn-view-all {
    background: var(--brand-soft);
    color: var(--brand);
    border: none;
    padding: 6px 16px;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 700;
    text-decoration: none;
    transition: all .3s;
}

.btn-view-all:hover {
    background: var(--brand);
    color: #fff;
}

.table thead th {
    background: #fafbfc;
    border-bottom: 1px solid var(--line);
    padding: 16px 24px;
    font-size: 0.72rem;
    text-transform: uppercase;
    color: var(--muted);
    font-weight: 800;
    letter-spacing: 0.05em;
}

.table tbody td {
    padding: 16px 24px;
    vertical-align: middle;
    border-bottom: 1px solid #f8f9fa;
    color: #56607a;
    font-size: 0.9rem;
}

.table tbody tr:hover {
    background: #fafbfc;
}

.product-img-sm {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    object-fit: cover;
    border: 1px solid var(--line);
    background: #f8f9fa;
}

/* ================= QUICK ACTIONS ================= */
.btn-action {
    border-radius: 12px;
    padding: 14px 20px;
    font-family: 'Outfit', sans-serif;
    font-weight: 700;
    font-size: 0.9rem;
    text-align: left;
    transition: all .3s var(--ease);
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
}

.btn-action i {
    font-size: 1.1rem;
}

.btn-primary-grad {
    background: linear-gradient(135deg, var(--brand), var(--brand-2));
    color: #fff;
    border: none;
    box-shadow: 0 8px 20px rgba(255, 77, 109, 0.25);
}

.btn-primary-grad:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 25px rgba(255, 77, 109, 0.35);
    color: #fff;
}

.btn-light-action {
    background: #fff;
    color: var(--ink);
    border: 1.5px solid var(--line);
}

.btn-light-action:hover {
    background: #fafbfc;
    border-color: var(--brand);
    color: var(--brand);
    transform: translateY(-2px);
}

/* Security Widget */
.security-widget {
    background: linear-gradient(145deg, #f8f9fa, #fff);
    border: 1px dashed var(--line);
    border-radius: 16px;
    padding: 24px;
    text-align: center;
    margin-top: 24px;
}

.security-widget i {
    color: #16a34a;
    font-size: 2rem;
    margin-bottom: 12px;
    filter: drop-shadow(0 4px 10px rgba(22, 163, 74, 0.2));
}

/* ================= RESPONSIVE ================= */
@media(max-width: 991px) {
  @media(max-width: 768px) {
    .main-content {
        margin-left: 85px;
        width: calc(100% - 85px);
        padding: 90px 20px 25px;
    }
}

@media(max-width: 768px) {
    .top-header {
        flex-direction: column;
        align-items: flex-start;
    }
    .date-badge {
        width: 100%;
        justify-content: center;
    }
    .table-responsive {
        border-radius: 0 0 20px 20px;
    }
}
</style>

<div class="main-content">
    <!-- TOP HEADER -->
    <div class="top-header">
        <div>
            <h2>Store Analytics</h2>
            <p>Hello <span style="color: var(--brand); font-weight: 700;"><?= htmlspecialchars($admin_name); ?></span> 👋 here's what's happening today.</p>
        </div>
        <div class="date-badge">
            <i class="fa-regular fa-calendar-check"></i>
            <?= $current_date; ?>
        </div>
    </div>

    <!-- STATS ROW -->
    <!-- Used col-xl for perfect 5-column layout on desktop -->
    <div class="row g-4 mb-4">
        <div class="col-md-4 col-xl">
            <div class="stat-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-box icon-pink"><i class="fa-solid fa-box"></i></div>
                    <div class="stat-info">
                        <p>Total Products</p>
                        <h3><?= $total_products ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-xl">
            <div class="stat-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-box icon-blue"><i class="fa-solid fa-layer-group"></i></div>
                    <div class="stat-info">
                        <p>Categories</p>
                        <h3><?= $total_categories ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-xl">
            <div class="stat-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-box icon-orange"><i class="fa-solid fa-images"></i></div>
                    <div class="stat-info">
                        <p>Banners</p>
                        <h3><?= $total_banners ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl">
            <div class="stat-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-box icon-green"><i class="fa-solid fa-cart-shopping"></i></div>
                    <div class="stat-info">
                        <p>Orders</p>
                        <h3><?= $total_orders ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl">
            <div class="stat-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-box icon-purple"><i class="fa-solid fa-indian-rupee-sign"></i></div>
                    <div class="stat-info">
                        <p>Earnings</p>
                        <h3>₹85.4k</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MIDDLE ROW: LISTS -->
    <div class="row g-4 mb-4">
        <!-- Latest Categories -->
        <div class="col-lg-4">
            <div class="list-card">
                <div class="card-header">
                    <h5>Latest Categories</h5>
                </div>
                <div class="card-body p-0">
                    <?php
                    $cats = $conn->query("SELECT * FROM categories ORDER BY id DESC LIMIT 5");
                    if($cats->num_rows > 0){
                        while($cat = $cats->fetch_assoc()){
                    ?>
                    <div class="list-item">
                        <div>
                            <h6><?= htmlspecialchars($cat['name']); ?></h6>
                            <small>Category ID: #<?= $cat['id']; ?></small>
                        </div>
                        <span class="badge-soft primary">Active</span>
                    </div>
                    <?php } } else { echo '<div class="p-4 text-center text-muted small">No categories found</div>'; } ?>
                </div>
            </div>
        </div>

        <!-- Latest Sub Categories -->
        <div class="col-lg-4">
            <div class="list-card">
                <div class="card-header">
                    <h5>Latest Sub Categories</h5>
                </div>
                <div class="card-body p-0">
                    <?php
                    $subs = $conn->query("SELECT * FROM sub_categories ORDER BY id DESC LIMIT 5");
                    if($subs->num_rows > 0){
                        while($sub = $subs->fetch_assoc()){
                    ?>
                    <div class="list-item">
                        <div>
                            <h6><?= htmlspecialchars($sub['name']); ?></h6>
                            <small>Sub ID: #<?= $sub['id']; ?></small>
                        </div>
                        <span class="badge-soft success">Live</span>
                    </div>
                    <?php } } else { echo '<div class="p-4 text-center text-muted small">No sub categories found</div>'; } ?>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="col-lg-4">
            <div class="list-card">
                <div class="card-header">
                    <h5>Recent Orders</h5>
                </div>
                <div class="card-body p-0">
                    <?php
                    $orders = $conn->query("SELECT * FROM orders ORDER BY id DESC LIMIT 5");
                    if($orders->num_rows > 0){
                        while($order = $orders->fetch_assoc()){
                    ?>
                    <div class="list-item">
                        <div>
                            <h6>Order #<?= $order['id']; ?></h6>
                            <small>₹<?= number_format($order['total_amount']); ?></small>
                        </div>
                        <span class="badge-soft warning">Pending</span>
                    </div>
                    <?php } } else { echo '<div class="p-4 text-center text-muted small">No orders found</div>'; } ?>
                </div>
            </div>
        </div>
    </div>

    <!-- BOTTOM ROW: TABLE & ACTIONS -->
    <div class="row g-4">
        <!-- Recent Stock Table -->
        <div class="col-lg-8">
            <div class="table-card h-100">
                <div class="card-header">
                    <h5>Recent Stock</h5>
                    <a href="view-product.php" class="btn-view-all">View All <i class="fa-solid fa-arrow-right ms-1"></i></a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Product</th>
                                    <th>Price</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $recent = $conn->query("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC LIMIT 5");
                                if($recent->num_rows > 0){
                                    while($row = $recent->fetch_assoc()){
                                ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-3">
                                            <!-- Added Image Thumbnail -->
                                            <img src="../images/<?= $row['image1'] ?: 'no-image.png' ?>" class="product-img-sm" alt="img">
                                            <div>
                                                <div class="fw-bold text-dark"><?= htmlspecialchars($row['product_name']) ?></div>
                                                <small class="text-muted">ID: #<?= $row['id'] ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="fw-bold text-dark">₹<?= number_format($row['new_price']) ?></td>
                                    <td><span class="badge bg-light text-secondary rounded-pill px-3"><?= htmlspecialchars($row['cat_name'] ?? 'Uncategorized') ?></span></td>
                                    <td><span class="badge-soft success">In Stock</span></td>
                                </tr>
                                <?php } } else { echo "<tr><td colspan='4' class='text-center p-4 text-muted'>No data found</td></tr>"; } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Launch -->
        <div class="col-lg-4">
            <div class="card border-0 rounded-4 p-4 h-100" style="background: #fff; box-shadow: 0 4px 15px rgba(46, 50, 84, 0.03);">
                <h5 class="fw-bold mb-4" style="font-family: 'Outfit', sans-serif; color: var(--ink);">Quick Launch</h5>
                
                <div class="d-grid gap-3">
                    <a href="add-product.php" class="btn-action btn-primary-grad">
                        <i class="fa-solid fa-plus-circle"></i> Create New Product
                    </a>
                    <a href="add-banner.php" class="btn-action btn-light-action">
                        <i class="fa-solid fa-image"></i> Update Banners
                    </a>
                    <a href="view-category.php" class="btn-action btn-light-action">
                        <i class="fa-solid fa-tags"></i> Category Manager
                    </a>
                </div>
                
                <!-- Security Widget -->
                <div class="security-widget">
                    <i class="fa-solid fa-shield-halved"></i>
                    <h6 class="fw-bold mb-1" style="font-family: 'Outfit', sans-serif; color: var(--ink);">System Security</h6>
                    <p class="small text-muted mb-0">Fully Optimized & Secure</p>
                </div>
            </div>
        </div>
    </div>
</div>