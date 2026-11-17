<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Panel - Sam Store</title>

<!-- Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Font Awesome -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">

<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

 <link rel="icon" type="image/png" href="../images/fab.png">

<style>
    /* ==========================================================
       TOKENS — Navy + Pink Theme
       ========================================================== */
    :root {
        --ink-1: #1f2340;
        --ink-2: #14172b;
        --ink-3: #0b1220;
        --brand: #ff4d6d;
        --brand-2: #e63950;
        --brand-soft: rgba(255, 77, 109, .15);
        --line: rgba(255, 255, 255, .08);
        --ease: cubic-bezier(.22, .61, .36, 1);

        --sidebar-w: 290px;
        --navbar-h: 75px;
    }

    * { box-sizing: border-box; }

    body {
        background: #f4f7f6;
        font-family: 'Manrope', sans-serif;
        margin: 0;
        -webkit-font-smoothing: antialiased;
    }

    /* ==========================================================
       TOP NAVBAR — fixed, glass, brand glow
       ========================================================== */
    .top-navbar {
        position: fixed;
        top: 0;
        right: 0;
        left: var(--sidebar-w);
        height: var(--navbar-h);
        z-index: 1040;

        /* Layered glass + subtle pink glow behind brand */
        background:
            radial-gradient(500px 120px at 20% 50%, rgba(255, 77, 109, .18), transparent 65%),
            rgba(31, 35, 64, .85);
        backdrop-filter: blur(14px) saturate(140%);
        -webkit-backdrop-filter: blur(14px) saturate(140%);
        border-bottom: 1px solid var(--line);
        box-shadow: 0 6px 24px rgba(0, 0, 0, .22);

        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 28px;
        gap: 20px;
        transition: left .3s var(--ease);
    }

    /* ==========================================================
       BRAND
       ========================================================== */
    .nav-brand {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        color: #fff;
        text-decoration: none;
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        font-size: 1.15rem;
        letter-spacing: -.01em;
        line-height: 1;
        padding: 8px 12px;
        border-radius: 12px;
        transition: background .25s var(--ease), transform .25s var(--ease);
    }
    .nav-brand:hover {
        background: rgba(255, 255, 255, .05);
        color: #fff;
        transform: translateY(-1px);
    }

    .nav-brand i {
        color: var(--brand);
        font-size: 1.35rem;
        width: 36px;
        height: 36px;
        display: inline-grid;
        place-items: center;
        border-radius: 10px;
        background: var(--brand-soft);
        border: 1px solid rgba(255, 77, 109, .3);
        transition: all .25s var(--ease);
    }
    .nav-brand:hover i {
        background: linear-gradient(135deg, var(--brand), var(--brand-2));
        color: #fff;
        border-color: transparent;
        box-shadow: 0 8px 20px rgba(255, 77, 109, .35);
    }

    /* ==========================================================
       ACTIONS
       ========================================================== */
    .nav-actions {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .nav-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 16px;
        border-radius: 999px;
        background: rgba(255, 255, 255, .05);
        border: 1px solid rgba(255, 255, 255, .1);
        color: rgba(255, 255, 255, .82);
        text-decoration: none;
        font-family: 'Outfit', sans-serif;
        font-size: .85rem;
        font-weight: 600;
        letter-spacing: .02em;
        transition: all .25s var(--ease);
        white-space: nowrap;
    }
    .nav-btn i {
        font-size: .95rem;
        transition: transform .25s var(--ease), color .25s;
    }
    .nav-btn:hover {
        color: #fff;
        background: rgba(255, 255, 255, .1);
        border-color: rgba(255, 255, 255, .18);
        transform: translateY(-2px);
    }
    .nav-btn:hover i { transform: scale(1.1); }

    /* View Site — stays neutral on hover */
    .nav-btn.btn-view-site:hover {
        background: rgba(255, 255, 255, .12);
        box-shadow: none;
    }
    .nav-btn.btn-view-site:hover i { color: #fff; }

    /* Profile — pink accent */
    .nav-btn.btn-profile {
        background: var(--brand-soft);
        border-color: rgba(255, 77, 109, .32);
        color: var(--brand);
    }
    .nav-btn.btn-profile i { color: var(--brand); }
    .nav-btn.btn-profile:hover {
        background: linear-gradient(135deg, var(--brand), var(--brand-2));
        border-color: transparent;
        color: #fff;
        box-shadow: 0 10px 24px rgba(255, 77, 109, .35);
    }
    .nav-btn.btn-profile:hover i { color: #fff; }

    /* ==========================================================
       MOBILE TOGGLE
       ========================================================== */
    .mobile-toggle {
        display: none;
        background: rgba(255, 255, 255, .06);
        border: 1px solid var(--line);
        color: #fff;
        width: 40px;
        height: 40px;
        border-radius: 10px;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all .25s var(--ease);
    }
    .mobile-toggle:hover {
        background: var(--brand);
        border-color: var(--brand);
        box-shadow: 0 8px 20px rgba(255, 77, 109, .35);
    }

    /* ==========================================================
       CONTENT WRAPPER
       ========================================================== */
    .main-wrapper {
        display: flex;
        width: 100%;
    }

    .content {
        flex: 1;
        margin-left: var(--sidebar-w);
        padding-top: var(--navbar-h);
        min-height: 100vh;
        transition: margin-left .3s var(--ease);
    }

    .content-inner {
        padding: 30px;
    }

    /* ==========================================================
       RESPONSIVE
       ========================================================== */
    @media (max-width: 1199px) {
        :root {
            --sidebar-w: 260px;
        }
        .top-navbar { padding: 0 22px; }
    }

    @media (max-width: 991px) {
        :root {
            --navbar-h: 68px;
            --sidebar-w: 0px; /* sidebar becomes off-canvas */
        }

        .top-navbar {
            left: 0;
            height: var(--navbar-h);
            padding: 0 16px;
        }

        .content {
            margin-left: 0;
            padding-top: var(--navbar-h);
        }

        .content-inner { padding: 20px; }

        /* Show hamburger */
        .mobile-toggle { display: inline-flex; }

        /* Compact brand on tablet */
        .nav-brand span { font-size: 1rem; }
    }

    @media (max-width: 575px) {
        .nav-brand span { display: none; }
        .nav-brand { padding: 6px; gap: 0; }

        .nav-btn span { display: none; }
        .nav-btn { padding: 9px 12px; }
        .nav-actions { gap: 8px; }
    }
</style>

</head>
<body>

<!-- ==========================================================
     TOP NAVBAR
     ========================================================== -->
<nav class="top-navbar">

    <!-- Left: Brand -->
    <a class="nav-brand" href="dashboard.php">
        <i class="fa-solid fa-user-shield"></i>
        <span>Admin Panel</span>
    </a>

    <!-- Right: Actions -->
    <div class="nav-actions">

        <!-- View Site -->
        <a class="nav-btn btn-view-site" href="../index.php" target="_blank" title="View Live Site">
            <i class="fa-solid fa-globe"></i>
            <span>View Site</span>
        </a>

        <!-- Admin Profile -->
        <a class="nav-btn btn-profile" href="Profile.php" title="Admin Profile">
            <i class="fa-solid fa-circle-user"></i>
            <span>Admin</span>
        </a>

        <!-- Mobile Toggle -->
        <button class="mobile-toggle d-md-none" type="button" aria-label="Toggle menu">
            <i class="fa-solid fa-bars"></i>
        </button>

    </div>
</nav>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>