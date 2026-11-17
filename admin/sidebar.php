<?php
require_once __DIR__ . "/../config/db.php";

/* ============================================================
   CURRENT PAGE DETECTION
   ============================================================ */
$currentPage = basename($_SERVER['PHP_SELF']);

/* Map page → parent dropdown key (edit_product.php lives under products) */
$pageParentMap = [
    'add-Category.php' => 'categories',
    'view-Category.php' => 'categories',
    'add-SubCategory.php' => 'subcategories',
    'view-SubCategory.php' => 'subcategories',
    'add_product.php' => 'products',
    'view_product.php' => 'products',
    'edit_product.php' => 'products',
    'add_banner.php' => 'banner',
    'view_banner.php' => 'banner',
    'add-service.php' => 'services',
    'view-service.php' => 'services',
    'add-why-choose.php' => 'why',
    'view-why-choose.php' => 'why',
    'add-blog.php' => 'blog',
    'view-blog.php' => 'blog',
];
$activeParent = $pageParentMap[$currentPage] ?? '';

/* ============================================================
   ADMIN IDENTITY (session → DB fallback)
   ============================================================ */
$admin_name = 'Admin';

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

if (!empty($_SESSION['admin_name'])) {
    $admin_name = $_SESSION['admin_name'];
} elseif (!empty($_SESSION['admin_id']) && isset($conn) && $conn instanceof mysqli) {
    $stmt = $conn->prepare("SELECT name FROM admins WHERE id = ? LIMIT 1");
    if ($stmt) {
        $aid = (int) $_SESSION['admin_id'];
        $stmt->bind_param("i", $aid);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!empty($res['name'])) {
            $admin_name = $res['name'];
        }
    }
} elseif (isset($conn) && $conn instanceof mysqli) {
    $q = @$conn->query("SELECT name FROM admins ORDER BY id DESC LIMIT 1");
    if ($q && ($row = $q->fetch_assoc()) && !empty($row['name'])) {
        $admin_name = $row['name'];
    }
}
$adminInitial = strtoupper(mb_substr($admin_name, 0, 1));
?>

<!-- ===== ASSETS ===== -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">
<link
    href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700;800;900&display=swap"
    rel="stylesheet">
   

<style>
    /* ==========================================================
       PREMIUM SIDEBAR — DEEP NAVY + PINK/CORAL  (design preserved)
       ========================================================== */
    :root {
        --ink-1: #1f2340;
        --ink-2: #14172b;
        --ink-3: #0b1220;
        --brand: #ff4d6d;
        --brand-2: #e63950;
        --brand-soft: rgba(255, 77, 109, .12);
        --brand-line: rgba(255, 77, 109, .35);
        --line: rgba(255, 255, 255, .07);
        --muted: #8a90a6;
        --text: #e8eaf2;
        --ease: cubic-bezier(.22, .61, .36, 1);
    }

    .sb * {
        box-sizing: border-box;
    }

    .sb {
        width: 290px;
        height: 100vh;
        position: fixed;
        inset: 0 auto 0 0;
        display: flex;
        flex-direction: column;
        background:
            radial-gradient(1200px 300px at -20% -10%, rgba(255, 77, 109, .18), transparent 60%),
            radial-gradient(800px 400px at 120% 100%, rgba(255, 77, 109, .08), transparent 60%),
            linear-gradient(180deg, var(--ink-1) 0%, var(--ink-2) 55%, var(--ink-3) 100%);
        border-right: 1px solid var(--line);
        box-shadow: 12px 0 40px rgba(0, 0, 0, .35);
        z-index: 1000;
        transition: transform .35s var(--ease);
    }

    .sb::-webkit-scrollbar {
        width: 6px;
    }

    .sb::-webkit-scrollbar-thumb {
        background: var(--brand);
        border-radius: 10px;
    }

    .sb::-webkit-scrollbar-track {
        background: transparent;
    }

    .sb-scroll {
        flex: 1;
        overflow-y: auto;
        overflow-x: hidden;
        padding: 20px 16px 24px;
    }

    /* BRAND */
    .sb-brand {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 6px 8px 6px;
        margin-bottom: 20px;
        text-decoration: none;
    }

    .sb-brand-icon {
        width: 50px;
        height: 50px;
        border-radius: 15px;
        background: linear-gradient(135deg, var(--brand), var(--brand-2));
        display: grid;
        place-items: center;
        color: #fff;
        font-size: 22px;
        box-shadow: 0 10px 24px rgba(255, 77, 109, .35);
        flex: 0 0 50px;
        position: relative;
    }

    .sb-brand-icon::after {
        content: '';
        position: absolute;
        inset: -2px;
        border-radius: 17px;
        background: linear-gradient(135deg, var(--brand), var(--brand-2));
        opacity: .35;
        filter: blur(10px);
        z-index: -1;
    }

    .sb-brand-text h3 {
        margin: 0;
        font-family: 'Outfit', sans-serif;
        font-size: 1.35rem;
        font-weight: 800;
        color: #fff;
        letter-spacing: -.02em;
        line-height: 1.1;
    }

    .sb-brand-text p {
        margin: 2px 0 0;
        color: white;
        font-size: .68rem;
        font-weight: 700;
        letter-spacing: .14em;
        text-transform: uppercase;
    }

    /* ADMIN PROFILE */
    .sb-profile {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 14px;
        border-radius: 16px;
        background: linear-gradient(180deg, rgba(255, 255, 255, .05), rgba(255, 255, 255, .02));
        border: 1px solid var(--line);
        margin-bottom: 22px;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .04);
    }

    .sb-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--brand), var(--brand-2));
        color: #fff;
        display: grid;
        place-items: center;
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        font-size: 1rem;
        flex: 0 0 40px;
        box-shadow: 0 6px 16px rgba(255, 77, 109, .3);
    }

    .sb-profile-text span {
        display: block;
        font-size: .65rem;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: .08em;
        font-weight: 700;
    }

    .sb-profile-text h6 {
        margin: 2px 0 0;
        font-family: 'Outfit', sans-serif;
        font-size: .92rem;
        font-weight: 700;
        color: #fff;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 160px;
    }

    /* GROUP LABELS */
    .sb-group {
        margin: 18px 0 8px;
        padding: 0 10px;
        font-family: 'Outfit', sans-serif;
        font-size: .65rem;
        font-weight: 800;
        letter-spacing: .16em;
        text-transform: uppercase;
        color: #6b7189;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .sb-group::after {
        content: '';
        flex: 1;
        height: 1px;
        background: linear-gradient(90deg, var(--line), transparent);
    }

    /* NAV LINKS */
    .sb-menu {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .sb .nav-link {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        padding: 11px 14px;
        margin-bottom: 4px;
        border-radius: 12px;
        color: #a4abc1;
        text-decoration: none;
        font-family: 'Outfit', sans-serif;
        font-size: .88rem;
        font-weight: 600;
        transition: all .25s var(--ease);
        position: relative;
        border: 1px solid transparent;
        cursor: pointer;
    }

    .sb .nav-link>div {
        display: flex;
        align-items: center;
        gap: 13px;
        min-width: 0;
    }

    .sb .nav-link i {
        width: 20px;
        text-align: center;
        font-size: 1.05rem;
        color: #8a90a6;
        transition: color .25s var(--ease), transform .25s var(--ease);
        flex: 0 0 20px;
    }

    .sb .nav-link span {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .sb .nav-link:hover {
        background: rgba(255, 255, 255, .05);
        border-color: var(--line);
        color: #fff;
    }

    .sb .nav-link:hover i {
        color: var(--brand);
        transform: translateX(2px);
    }

    .sb .nav-link.active {
        background: linear-gradient(135deg, var(--brand), var(--brand-2));
        color: #fff;
        border-color: transparent;
        box-shadow: 0 10px 24px rgba(255, 77, 109, .35);
    }

    .sb .nav-link.active i {
        color: #fff;
    }

    .sb .nav-link.active::before {
        content: '';
        position: absolute;
        left: -16px;
        top: 50%;
        transform: translateY(-50%);
        width: 4px;
        height: 22px;
        border-radius: 0 4px 4px 0;
        background: var(--brand);
    }

    .sb .nav-link[aria-expanded="true"] {
        background: rgba(255, 255, 255, .04);
        color: #fff;
        border-color: var(--line);
    }

    .sb .nav-link[aria-expanded="true"] i {
        color: var(--brand);
    }

    .sb-chevron {
        font-size: .7rem !important;
        width: auto !important;
        opacity: .55;
        transition: transform .3s var(--ease), opacity .3s;
    }

    .sb .nav-link[aria-expanded="true"] .sb-chevron {
        transform: rotate(180deg);
        opacity: 1;
    }

    /* SUBMENU */
    .sb-submenu {
        list-style: none;
        margin: 4px 0 10px 18px;
        padding: 0 0 0 14px;
        border-left: 1px dashed rgba(255, 255, 255, .14);
        position: relative;
    }

    .sb-submenu .nav-item {
        margin: 0;
    }

    .sb-submenu .nav-link {
        padding: 8px 12px;
        font-size: .82rem;
        font-weight: 500;
        color: var(--muted);
        border-radius: 9px;
    }

    .sb-submenu .sub-dot {
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: rgba(255, 255, 255, .18);
        transition: background .25s;
        flex: 0 0 5px;
    }

    .sb-submenu .nav-link:hover {
        color: var(--brand);
    }

    .sb-submenu .nav-link:hover .sub-dot {
        background: var(--brand);
    }

    .sb-submenu .nav-link.active {
        background: var(--brand-soft);
        color: var(--brand);
        box-shadow: none;
    }

    .sb-submenu .nav-link.active::before {
        display: none;
    }

    .sb-submenu .nav-link.active .sub-dot {
        background: var(--brand);
    }

    .sb-submenu .nav-link.active i {
        color: var(--brand);
    }

    .sb .collapse {
        transition: height .3s var(--ease);
    }

    /* LOGOUT FOOTER */
    .sb-footer {
        padding: 14px 16px 20px;
        border-top: 1px solid var(--line);
        background: linear-gradient(180deg, transparent, rgba(0, 0, 0, .15));
    }

    .sb-footer .nav-link {
        margin: 0;
        color: var(--brand);
        background: var(--brand-soft);
        border: 1px solid var(--brand-line);
    }

    .sb-footer .nav-link i {
        color: var(--brand);
    }

    .sb-footer .nav-link:hover {
        background: linear-gradient(135deg, var(--brand), var(--brand-2));
        color: #fff;
        border-color: transparent;
        box-shadow: 0 10px 24px rgba(255, 77, 109, .35);
    }

    .sb-footer .nav-link:hover i {
        color: #fff;
    }

    /* HAMBURGER + OVERLAY */
    .sb-toggle {
        display: none;
        position: fixed;
        top: 16px;
        left: 16px;
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--brand), var(--brand-2));
        color: #fff;
        border: none;
        z-index: 1100;
        box-shadow: 0 8px 22px rgba(255, 77, 109, .35);
        font-size: 1.1rem;
    }

    .sb-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(4, 7, 16, .6);
        backdrop-filter: blur(3px);
        z-index: 999;
        opacity: 0;
        transition: opacity .3s var(--ease);
    }

    .sb-overlay.show {
        display: block;
        opacity: 1;
    }

    /* RESPONSIVE */
    @media (max-width: 1199px) {
        .sb {
            width: 260px;
        }
    }

    @media (max-width: 991px) {
        .sb {
            transform: translateX(-100%);
            width: 285px;
            box-shadow: 12px 0 40px rgba(0, 0, 0, .5);
        }

        .sb.open {
            transform: translateX(0);
        }

        .sb-toggle {
            display: grid;
            place-items: center;
        }

        .main-content {
            margin-left: 0 !important;
            padding-top: 80px !important;
        }
    }

    @media (max-width: 575px) {
        .sb {
            width: 88%;
            max-width: 320px;
        }

        .sb-brand-text h3 {
            font-size: 1.2rem;
        }

        .sb-profile-text h6 {
            max-width: 140px;
        }
    }
    .admin-logo {
    width: 130px;
    height: 40px;
    object-fit: contain;
    display: block;
}
</style>

<!-- ================= HAMBURGER (mobile only) ================= -->
<button class="sb-toggle" id="sbToggle" aria-label="Open menu" aria-controls="adminSidebar" aria-expanded="false">
    <i class="fa-solid fa-bars"></i>
</button>

<!-- ================= SIDEBAR ================= -->
<aside class="sb" id="adminSidebar" role="navigation" aria-label="Admin navigation">

    <div class="sb-scroll">

        <!-- BRAND -->
<a href="Dashboard.php" class="sb-brand">
    <!-- <div class="sb-brand-icon">
        <i class="fa-solid fa-paw"></i>
    </div> -->

    <div class="sb-brand-text">
        <img src="../images/logo.png" alt="SAM'S STORE" class="admin-logo">
        <p>Admin Panel</p>
    </div>
</a>

        <!-- ADMIN PROFILE -->
        <div class="sb-profile">
            <div class="sb-avatar"><?= htmlspecialchars($adminInitial); ?></div>
            <div class="sb-profile-text">
                <span>Welcome back</span>
                <h6><?= htmlspecialchars($admin_name); ?></h6>
            </div>
        </div>

        <!-- ================= MENU ================= -->
        <ul class="sb-menu" id="sidebarMenu">

            <!-- ---------- DASHBOARD ---------- -->
            <div class="sb-group">Dashboard</div>
            <li class="nav-item">
                <a href="Dashboard.php" class="nav-link <?= $currentPage === 'Dashboard.php' ? 'active' : ''; ?>">
                    <div><i class="fa-solid fa-house"></i><span>Dashboard</span></div>
                </a>
            </li>

            <!-- ---------- STORE MANAGEMENT ---------- -->
            <div class="sb-group">Store Management</div>

            <!-- Categories -->
            <li class="nav-item">
                <a class="nav-link <?= $activeParent === 'categories' ? 'active' : ''; ?>" data-bs-toggle="collapse"
                    href="#menu-categories" role="button"
                    aria-expanded="<?= $activeParent === 'categories' ? 'true' : 'false'; ?>"
                    aria-controls="menu-categories">
                    <div><i class="fa-solid fa-layer-group"></i><span>Categories</span></div>
                    <i class="fa-solid fa-chevron-down sb-chevron"></i>
                </a>
                <div class="collapse <?= $activeParent === 'categories' ? 'show' : ''; ?>" id="menu-categories"
                    data-bs-parent="#sidebarMenu">
                    <ul class="sb-submenu">
                        <li class="nav-item">
                            <a href="add-Category.php"
                                class="nav-link <?= $currentPage === 'add-Category.php' ? 'active' : ''; ?>">
                                <div><span class="sub-dot"></span><span>Add Category</span></div>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="view-Category.php"
                                class="nav-link <?= $currentPage === 'view-Category.php' ? 'active' : ''; ?>">
                                <div><span class="sub-dot"></span><span>View Categories</span></div>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Sub Categories -->
            <li class="nav-item">
                <a class="nav-link <?= $activeParent === 'subcategories' ? 'active' : ''; ?>" data-bs-toggle="collapse"
                    href="#menu-subcategories" role="button"
                    aria-expanded="<?= $activeParent === 'subcategories' ? 'true' : 'false'; ?>"
                    aria-controls="menu-subcategories">
                    <div><i class="fa-solid fa-boxes-stacked"></i><span>Sub Categories</span></div>
                    <i class="fa-solid fa-chevron-down sb-chevron"></i>
                </a>
                <div class="collapse <?= $activeParent === 'subcategories' ? 'show' : ''; ?>" id="menu-subcategories"
                    data-bs-parent="#sidebarMenu">
                    <ul class="sb-submenu">
                        <li class="nav-item">
                            <a href="add-SubCategory.php"
                                class="nav-link <?= $currentPage === 'add-SubCategory.php' ? 'active' : ''; ?>">
                                <div><span class="sub-dot"></span><span>Add Sub Category</span></div>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="view-SubCategory.php"
                                class="nav-link <?= $currentPage === 'view-SubCategory.php' ? 'active' : ''; ?>">
                                <div><span class="sub-dot"></span><span>View Sub Categories</span></div>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Products -->
            <li class="nav-item">
                <a class="nav-link <?= $activeParent === 'products' ? 'active' : ''; ?>" data-bs-toggle="collapse"
                    href="#menu-products" role="button"
                    aria-expanded="<?= $activeParent === 'products' ? 'true' : 'false'; ?>"
                    aria-controls="menu-products">
                    <div><i class="fa-solid fa-box-open"></i><span>Products</span></div>
                    <i class="fa-solid fa-chevron-down sb-chevron"></i>
                </a>
                <div class="collapse <?= $activeParent === 'products' ? 'show' : ''; ?>" id="menu-products"
                    data-bs-parent="#sidebarMenu">
                    <ul class="sb-submenu">
                        <li class="nav-item">
                            <a href="add_product.php"
                                class="nav-link <?= $currentPage === 'add_product.php' ? 'active' : ''; ?>">
                                <div><span class="sub-dot"></span><span>Add Product</span></div>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="view_product.php"
                                class="nav-link <?= $currentPage === 'view_product.php' ? 'active' : ''; ?>">
                                <div><span class="sub-dot"></span><span>View Products</span></div>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- ---------- WEBSITE MANAGEMENT ---------- -->
            <div class="sb-group">Website Management</div>

            <!-- Banner -->
            <li class="nav-item">
                <a class="nav-link <?= $activeParent === 'banner' ? 'active' : ''; ?>" data-bs-toggle="collapse"
                    href="#menu-banner" role="button"
                    aria-expanded="<?= $activeParent === 'banner' ? 'true' : 'false'; ?>" aria-controls="menu-banner">
                    <div><i class="fa-solid fa-image"></i><span>Banner</span></div>
                    <i class="fa-solid fa-chevron-down sb-chevron"></i>
                </a>
                <div class="collapse <?= $activeParent === 'banner' ? 'show' : ''; ?>" id="menu-banner"
                    data-bs-parent="#sidebarMenu">
                    <ul class="sb-submenu">
                        <li class="nav-item">
                            <a href="add_banner.php"
                                class="nav-link <?= $currentPage === 'add_banner.php' ? 'active' : ''; ?>">
                                <div><span class="sub-dot"></span><span>Add Banner</span></div>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="view_banner.php"
                                class="nav-link <?= $currentPage === 'view_banner.php' ? 'active' : ''; ?>">
                                <div><span class="sub-dot"></span><span>View Banner</span></div>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Services -->
            <li class="nav-item">
                <a class="nav-link <?= $activeParent === 'services' ? 'active' : ''; ?>" data-bs-toggle="collapse"
                    href="#menu-services" role="button"
                    aria-expanded="<?= $activeParent === 'services' ? 'true' : 'false'; ?>"
                    aria-controls="menu-services">
                    <div><i class="fa-solid fa-gears"></i><span>Services</span></div>
                    <i class="fa-solid fa-chevron-down sb-chevron"></i>
                </a>
                <div class="collapse <?= $activeParent === 'services' ? 'show' : ''; ?>" id="menu-services"
                    data-bs-parent="#sidebarMenu">
                    <ul class="sb-submenu">
                        <li class="nav-item">
                            <a href="add-service.php"
                                class="nav-link <?= $currentPage === 'add-service.php' ? 'active' : ''; ?>">
                                <div><span class="sub-dot"></span><span>Add Service</span></div>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="view-service.php"
                                class="nav-link <?= $currentPage === 'view-service.php' ? 'active' : ''; ?>">
                                <div><span class="sub-dot"></span><span>View Services</span></div>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Why Choose Us -->
            <li class="nav-item">
                <a class="nav-link <?= $activeParent === 'why' ? 'active' : ''; ?>" data-bs-toggle="collapse"
                    href="#menu-why" role="button" aria-expanded="<?= $activeParent === 'why' ? 'true' : 'false'; ?>"
                    aria-controls="menu-why">
                    <div><i class="fa-solid fa-star"></i><span>Why Choose Us</span></div>
                    <i class="fa-solid fa-chevron-down sb-chevron"></i>
                </a>
                <div class="collapse <?= $activeParent === 'why' ? 'show' : ''; ?>" id="menu-why"
                    data-bs-parent="#sidebarMenu">
                    <ul class="sb-submenu">
                        <li class="nav-item">
                            <a href="add-why-choose.php"
                                class="nav-link <?= $currentPage === 'add-why-choose.php' ? 'active' : ''; ?>">
                                <div><span class="sub-dot"></span><span>Add</span></div>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="view-why-choose.php"
                                class="nav-link <?= $currentPage === 'view-why-choose.php' ? 'active' : ''; ?>">
                                <div><span class="sub-dot"></span><span>View</span></div>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Blog -->
            <li class="nav-item">
                <a class="nav-link <?= $activeParent === 'blog' ? 'active' : ''; ?>" data-bs-toggle="collapse"
                    href="#menu-blog" role="button" aria-expanded="<?= $activeParent === 'blog' ? 'true' : 'false'; ?>"
                    aria-controls="menu-blog">
                    <div><i class="fa-solid fa-blog"></i><span>Blog</span></div>
                    <i class="fa-solid fa-chevron-down sb-chevron"></i>
                </a>
                <div class="collapse <?= $activeParent === 'blog' ? 'show' : ''; ?>" id="menu-blog"
                    data-bs-parent="#sidebarMenu">
                    <ul class="sb-submenu">
                        <li class="nav-item">
                            <a href="add-blog.php"
                                class="nav-link <?= $currentPage === 'add-blog.php' ? 'active' : ''; ?>">
                                <div><span class="sub-dot"></span><span>Add Blog</span></div>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="view-blog.php"
                                class="nav-link <?= $currentPage === 'view-blog.php' ? 'active' : ''; ?>">
                                <div><span class="sub-dot"></span><span>View Blog</span></div>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- ---------- SALES MANAGEMENT ---------- -->
            <div class="sb-group">Sales Management</div>
            <li class="nav-item">
                <a href="orders.php" class="nav-link <?= $currentPage === 'orders.php' ? 'active' : ''; ?>">
                    <div><i class="fa-solid fa-cart-shopping"></i><span>Orders</span></div>
                </a>
            </li>
            <li class="nav-item">
                <a href="users.php"
                    class="nav-link <?= $currentPage === 'users.php' || $currentPage === 'view-user.php' ? 'active' : ''; ?>">
                    <div>
                        <i class="fa-solid fa-users"></i>
                        <span>User Management</span>
                    </div>
                    
                </a>
            </li>

            <!-- ---------- CUSTOMER & MARKETING ---------- -->
            <div class="sb-group">Customer &amp; Marketing</div>

            <li class="nav-item">
                <a href="contact-messages.php"
                   class="nav-link <?= in_array($currentPage, ['contact-messages.php', 'view-message.php'], true) ? 'active' : ''; ?>">
                    <div><i class="fa-solid fa-envelope"></i><span>Contact Messages</span></div>
                </a>
            </li>

            <li class="nav-item">
                <a href="product-reviews.php"
                   class="nav-link <?= $currentPage === 'product-reviews.php' ? 'active' : ''; ?>">
                    <div><i class="fa-solid fa-star-half-stroke"></i><span>Product Reviews</span></div>
                </a>
            </li>

            <li class="nav-item">
                <a href="coupons.php"
                   class="nav-link <?= $currentPage === 'coupons.php' ? 'active' : ''; ?>">
                    <div><i class="fa-solid fa-ticket"></i><span>Coupons &amp; Discounts</span></div>
                </a>
            </li>

            <li class="nav-item">
                <a href="newsletter.php"
                   class="nav-link <?= $currentPage === 'newsletter.php' ? 'active' : ''; ?>">
                    <div><i class="fa-solid fa-paper-plane"></i><span>Newsletter Subscribers</span></div>
                </a>
            </li>

            <li class="nav-item">
                <a href="offers.php"
                   class="nav-link <?= $currentPage === 'offers.php' ? 'active' : ''; ?>">
                    <div><i class="fa-solid fa-tags"></i><span>Offers &amp; Promotions</span></div>
                </a>
            </li>
        </ul>
    </div>

    <!-- FOOTER: LOGOUT -->
    <div class="sb-footer">
        <ul class="sb-menu">
            <li class="nav-item">
                <a href="logout.php" class="nav-link">
                    <div><i class="fa-solid fa-right-from-bracket"></i><span>Logout</span></div>
                </a>
            </li>
        </ul>
    </div>

</aside>

<!-- Overlay for mobile drawer -->
<div class="sb-overlay" id="sbOverlay"></div>


<script>
    if (!window.bootstrap || !window.bootstrap.Collapse) {
        document.write('<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"><\/script>');
    }
</script>


<script>
    (function () {
        if (window.__sbDrawerInit) { return; }
        window.__sbDrawerInit = true;

        var sidebar = document.getElementById('adminSidebar');
        var toggle = document.getElementById('sbToggle');
        var overlay = document.getElementById('sbOverlay');
        if (!sidebar || !toggle || !overlay) return;

        function openDrawer() {
            sidebar.classList.add('open');
            overlay.classList.add('show');
            toggle.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden';
        }
        function closeDrawer() {
            sidebar.classList.remove('open');
            overlay.classList.remove('show');
            toggle.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        }

        toggle.addEventListener('click', function () {
            sidebar.classList.contains('open') ? closeDrawer() : openDrawer();
        });

        overlay.addEventListener('click', closeDrawer);

        /* Only close the drawer for REAL navigation links.
           Dropdown toggles use [data-bs-toggle="collapse"], so we skip them. */
        sidebar.querySelectorAll('a.nav-link').forEach(function (link) {
            if (link.matches('[data-bs-toggle="collapse"]')) return; // dropdown toggle → do NOT close
            link.addEventListener('click', function () {
                if (window.innerWidth <= 991) closeDrawer();
            });
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && sidebar.classList.contains('open')) closeDrawer();
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth > 991 && sidebar.classList.contains('open')) closeDrawer();
        });
    })();
</script>