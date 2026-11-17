<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once __DIR__ . "/../config/db.php";

$isLoggedIn = isset($_SESSION['user']);
$userData = null;
if ($isLoggedIn) {
  $email = mysqli_real_escape_string($conn, $_SESSION['user']);
  $q = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
  $userData = mysqli_fetch_assoc($q);
  $userImg = !empty($userData['image']) ? "images/" . $userData['image'] : "https://via.placeholder.com/70";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SAM'S - Store</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
   <link rel="icon" type="image/png" href="images/fab.png">

<style>
    /* ==========================================================
       TOKENS  — DARK THEME + ORIGINAL COLORS
       ========================================================== */
    :root {
      /* navy (original dark theme) */
      --ink: #2e3254;
      --ink-2: #252a4a;
      --ink-3: #1f2340;

      /* pink (original brand) */
      --brand: #ff4d6d;
      --brand-2: #e63950;
      --brand-soft: rgba(255, 77, 109, .12);
      --brand-glow: rgba(255, 77, 109, .40);

      --line: rgba(255, 255, 255, .08);
      --muted: #8a8fa3;
      --ease: cubic-bezier(.2, .8, .2, 1);
      --max-w: 1400px;
    }

    * { box-sizing: border-box; }

    body {
      font-family: 'Manrope', system-ui, -apple-system, 'Segoe UI', sans-serif;
      padding-top: 132px;
      color: #0f172a;
      background: #fff;
      -webkit-font-smoothing: antialiased;
    }

    .offcanvas { z-index: 1100 !important; }

    /* ==========================================================
       HEADER SHELL
       ========================================================== */
    .header-container {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      width: 100%;
      z-index: 1050;
    }

    /* ==========================================================
       ANNOUNCEMENT BAR
       ========================================================== */
    .announcement-bar {
      height: 36px;
      background: linear-gradient(120deg, var(--ink-3) 0%, var(--ink-2) 55%, #2a2e4d 100%);
      overflow: hidden;
      display: flex;
      align-items: center;
      position: relative;
      border-bottom: 1px solid rgba(255, 255, 255, .04);
    }

    .announcement-bar::before,
    .announcement-bar::after {
      content: '';
      position: absolute;
      top: 0;
      bottom: 0;
      width: 60px;
      z-index: 2;
      pointer-events: none;
    }

    .announcement-bar::before {
      left: 0;
      background: linear-gradient(90deg, var(--ink-3) 0%, transparent 100%);
    }

    .announcement-bar::after {
      right: 0;
      background: linear-gradient(270deg, var(--ink-3) 0%, transparent 100%);
    }

    .scrolling-text {
      display: inline-block;
      white-space: nowrap;
      animation: scroll-left 22s linear infinite;
      font-family: 'Outfit', sans-serif;
      font-size: .78rem;
      letter-spacing: .1em;
      text-transform: uppercase;
      font-weight: 600;
      color: rgba(255, 255, 255, .9);
    }

    @keyframes scroll-left {
      0%   { transform: translateX(100%); }
      100% { transform: translateX(-100%); }
    }

    /* ==========================================================
       MAIN NAVBAR — DARK, contained, proper spacing
       ========================================================== */
    .main-nav {
      background: linear-gradient(180deg, var(--ink-3) 0%, #0d1329 100%);
      border-bottom: 1px solid rgba(255, 255, 255, .06);
      position: relative;
      box-shadow: 0 6px 24px rgba(0, 0, 0, .20);
    }

    /* pink gradient bottom line */
    .main-nav::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      height: 1px;
      background: linear-gradient(90deg, transparent 0%, var(--brand) 25%, var(--brand-2) 50%, var(--brand) 75%, transparent 100%);
      opacity: .55;
    }

    .nav-container {
      max-width: var(--max-w);
      margin: 0 auto;
      padding: 0 32px;
      height: 82px;
      display: flex;
      align-items: center;
      gap: 40px;
    }

    /* ==========================================================
       LOGO — with breathing space
       ========================================================== */
    .nav-logo {
      flex: 0 0 auto;
      display: flex;
      align-items: center;
      padding-right: 8px;
      text-decoration: none;
    }

    .nav-logo img {
      height: 52px;
      transition: transform .4s var(--ease);
      filter: drop-shadow(0 4px 12px rgba(0, 0, 0, .35));
    }

    .nav-logo:hover img { transform: scale(1.04); }

    /* ==========================================================
       NAV MENU — centered, white text on dark
       ========================================================== */
    .nav-menu {
      flex: 1 1 auto;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 2px;
      list-style: none;
      margin: 0;
      padding: 0;
    }

    .nav-menu-item { position: relative; }

    .nav-menu-item > a {
      display: flex;
      align-items: center;
      gap: 6px;
      padding: 10px 20px;
      border-radius: 10px;
      color: rgba(255, 255, 255, .82);
      font-family: 'Outfit', sans-serif;
      font-size: .8rem;
      font-weight: 700;
      letter-spacing: .05em;
      text-transform: uppercase;
      text-decoration: none;
      transition: color .3s var(--ease);
      white-space: nowrap;
      position: relative;
    }

    .nav-menu-item > a::after {
      content: '';
      position: absolute;
      left: 50%;
      bottom: 2px;
      width: 0;
      height: 2px;
      background: linear-gradient(90deg, var(--brand), #ff8fa0);
      border-radius: 99px;
      transform: translateX(-50%);
      transition: width .35s var(--ease);
    }

    .nav-menu-item:hover > a {
      color: #fff;
    }

    .nav-menu-item:hover > a::after {
      width: calc(100% - 40px);
    }

    /* ==========================================================
       MEGA MENU — white card (contrast against dark navbar)
       ========================================================== */
    .nav-menu-item.mega { position: static; }

    .mega-menu {
      visibility: hidden;
      opacity: 0;
      pointer-events: none;
      position: absolute;
      top: calc(100% + 6px);
      left: 50%;
      transform: translate(-50%, 12px);
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 30px 70px rgba(0, 0, 0, .30), 0 0 0 1px rgba(255, 255, 255, .05);
      padding: 22px;
      width: max-content;
      max-width: 92vw;
      z-index: 1000;
      transition: opacity .3s var(--ease), transform .3s var(--ease), visibility .3s;
    }

    .mega-menu::before {
      content: '';
      position: absolute;
      top: -6px;
      left: 50%;
      transform: translateX(-50%) rotate(45deg);
      width: 12px;
      height: 12px;
      background: #fff;
      border-radius: 2px;
    }

    .nav-menu-item.mega:hover .mega-menu {
      visibility: visible;
      opacity: 1;
      pointer-events: auto;
      transform: translate(-50%, 0);
    }

    .sub-cat-col {
      min-width: 180px;
      padding: 0 16px;
      border-right: 1px solid #efe6ec;
    }

    .sub-cat-col:first-child { padding-left: 4px; }
    .sub-cat-col:last-child { border-right: none; padding-right: 4px; }

    .sub-title {
      display: inline-block;
      color: var(--ink);
      font-family: 'Outfit', sans-serif;
      font-size: .7rem;
      font-weight: 800;
      letter-spacing: .1em;
      text-transform: uppercase;
      padding-bottom: 8px;
      margin-bottom: 10px;
      background-image: linear-gradient(90deg, var(--brand), #ffb3bf);
      background-repeat: no-repeat;
      background-size: 100% 2px;
      background-position: 0 100%;
      text-decoration: none;
    }

    .sub-title:hover { color: var(--brand); }

    .product-link {
      display: block;
      font-size: .82rem;
      color: #56607a;
      text-decoration: none;
      padding: 5px 0;
      transition: color .25s var(--ease), padding-left .25s var(--ease);
    }

    .product-link:hover {
      color: var(--brand);
      padding-left: 8px;
    }

    .product-link.view-all {
      margin-top: 8px;
      font-size: .7rem;
      letter-spacing: .06em;
      text-transform: uppercase;
      font-weight: 800;
      color: var(--brand);
    }

    /* ==========================================================
       NAV ACTIONS — icon + label on dark
       ========================================================== */
    .nav-actions {
      flex: 0 0 auto;
      display: flex;
      align-items: center;
      gap: 4px;
    }

    .action-btn {
      position: relative;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 4px;
      padding: 8px 12px;
      border-radius: 12px;
      background: transparent;
      color: rgba(255, 255, 255, .82);
      text-decoration: none;
      transition: all .3s var(--ease);
      min-width: 62px;
    }

    .action-btn:hover {
      color: #fff;
      background: rgba(255, 77, 109, .14);
    }

    .action-btn:hover i { color: var(--brand); }

    .action-btn .icon-wrap {
      position: relative;
      display: flex;
      align-items: center;
      justify-content: center;
      width: 26px;
      height: 26px;
    }

    .action-btn i {
      font-size: 1.25rem;
      line-height: 1;
      transition: transform .3s var(--ease), color .3s var(--ease);
    }

    .action-btn:hover i { transform: translateY(-2px) scale(1.06); }

    .action-btn .label {
      font-family: 'Outfit', sans-serif;
      font-size: .64rem;
      font-weight: 700;
      letter-spacing: .06em;
      text-transform: uppercase;
      line-height: 1;
      color: inherit;
    }

    /* cart badge */
    .badge-cart {
      position: absolute;
      top: -7px;
      right: -9px;
      min-width: 18px;
      height: 18px;
      padding: 0 5px;
      display: grid;
      place-items: center;
      background: linear-gradient(135deg, var(--brand), var(--brand-2));
      color: #fff;
      font-family: 'Outfit', sans-serif;
      font-size: .6rem;
      font-weight: 800;
      line-height: 1;
      border-radius: 999px;
      border: 2px solid var(--ink-3);
      box-shadow: 0 4px 10px var(--brand-glow);
    }

    /* ==========================================================
       USER DROPDOWN
       ========================================================== */
    .user-dropdown { position: relative; }

    .user-dropdown .dropdown-menu {
      position: absolute;
      top: calc(100% + 10px);
      right: -10px;
      border: 1px solid #efe6ec;
      border-radius: 16px;
      box-shadow: 0 20px 50px rgba(0, 0, 0, .25);
      padding: 14px 0;
      min-width: 260px;
      display: none;
      z-index: 1000;
      background: #fff;
    }

    .user-dropdown:hover .dropdown-menu {
      display: block;
      animation: fadeDown .25s ease;
    }

    @keyframes fadeDown {
      from { opacity: 0; transform: translateY(6px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    .user-dropdown .dropdown-menu::before {
      content: '';
      position: absolute;
      top: -6px;
      right: 26px;
      width: 12px;
      height: 12px;
      background: #fff;
      transform: rotate(45deg);
      border-top: 1px solid #efe6ec;
      border-left: 1px solid #efe6ec;
    }

    .user-dropdown .user-head {
      padding: 4px 20px 14px;
      text-align: center;
      border-bottom: 1px solid #efe6ec;
      margin-bottom: 8px;
    }

    .user-dropdown .user-head img {
      width: 58px;
      height: 58px;
      border-radius: 50%;
      border: 2px solid var(--brand);
      padding: 2px;
      object-fit: cover;
      margin-bottom: 8px;
    }

    .user-dropdown .user-head h6 {
      font-family: 'Outfit', sans-serif;
      font-weight: 700;
      font-size: .9rem;
      margin: 0 0 2px;
      color: var(--ink);
    }

    .user-dropdown .user-head small {
      font-size: .72rem;
      color: #8a8fa3;
    }

    .user-dropdown .drop-item {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 9px 20px;
      font-family: 'Outfit', sans-serif;
      font-size: .82rem;
      font-weight: 600;
      color: var(--ink);
      text-decoration: none;
      transition: all .25s var(--ease);
    }

    .user-dropdown .drop-item:hover {
      background: #fff2f5;
      color: var(--brand);
      padding-left: 24px;
    }

    .user-dropdown .drop-item i {
      width: 16px;
      font-size: .9rem;
      text-align: center;
    }

    .user-dropdown .drop-item.danger:hover {
      background: #fff0f0;
      color: #d92c4c;
    }

    /* ==========================================================
       USP BAR
       ========================================================== */
    .usp-section {
      background: linear-gradient(180deg, #faf7f9 0%, #fff 100%);
      border-bottom: 1px solid #efe6ec;
      padding: 14px 0;
    }

    .usp-inner {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      padding: 8px 4px;
      border-radius: 12px;
      transition: background .3s var(--ease), transform .3s var(--ease);
    }

    .usp-inner:hover {
      background: rgba(255, 77, 109, .06);
      transform: translateY(-2px);
    }

    .usp-inner i {
      font-size: .92rem;
      color: var(--brand);
      width: 32px;
      height: 32px;
      display: grid;
      place-items: center;
      background: #fff;
      border-radius: 50%;
      box-shadow: 0 4px 12px rgba(255, 77, 109, .16);
    }

    .usp-inner span {
      font-family: 'Outfit', sans-serif;
      font-size: .74rem;
      color: var(--ink);
      font-weight: 700;
      letter-spacing: .02em;
    }

    /* ==========================================================
       MOBILE BOTTOM NAV — dark navy
       ========================================================== */
    .mobile-bottom-nav {
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      background: linear-gradient(180deg, var(--ink-2) 0%, var(--ink-3) 100%);
      border-top: 1px solid rgba(255, 255, 255, .08);
      display: flex;
      justify-content: space-around;
      align-items: center;
      padding: 8px 6px calc(8px + env(safe-area-inset-bottom, 0px));
      z-index: 999;
      box-shadow: 0 -10px 30px rgba(0, 0, 0, .20);
    }

    .mobile-bottom-nav a {
      flex: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 4px;
      padding: 8px 2px; /* Padding fix for mobile text */
      border-radius: 12px;
      color: rgba(255, 255, 255, .55);
      text-decoration: none;
      transition: color .3s var(--ease), background .3s var(--ease);
    }

    .mobile-bottom-nav a i {
      font-size: 1.05rem;
      transition: transform .35s var(--ease);
    }

    .mobile-bottom-nav a span {
      font-family: 'Outfit', sans-serif;
      font-size: .62rem;
      font-weight: 700;
      letter-spacing: .04em;
      text-transform: uppercase;
      white-space: nowrap; /* Text cut-off fix */
    }

    .mobile-bottom-nav a.active {
      color: #fff;
      background: rgba(255, 255, 255, .04);
    }

    .mobile-bottom-nav a.active i {
      color: var(--brand);
      transform: translateY(-1px);
    }

    /* ==========================================================
       OFFCANVAS
       ========================================================== */
    .offcanvas {
      width: 320px !important;
      border: 0 !important;
    }

    .offcanvas-header {
      background: linear-gradient(135deg, var(--ink-3) 0%, #252a4a 100%);
      padding: 22px 20px !important;
      border-bottom: 1px solid rgba(255, 255, 255, .08);
      position: relative;
      overflow: hidden;
    }

    .offcanvas-header::before {
      content: '';
      position: absolute;
      top: -40px;
      right: -40px;
      width: 160px;
      height: 160px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(255, 77, 109, .3), transparent 70%);
      pointer-events: none;
    }

    .offcanvas-header .user-avatar {
      width: 46px;
      height: 46px;
      border-radius: 50%;
      border: 2px solid var(--brand);
      padding: 2px;
      object-fit: cover;
    }

    .offcanvas-header .icon-avatar {
      width: 46px;
      height: 46px;
      display: grid;
      place-items: center;
      border-radius: 50%;
      background: rgba(255, 255, 255, .08);
      border: 1px solid rgba(255, 255, 255, .12);
      color: #fff;
      font-size: 1.15rem;
    }

    .offcanvas-header .greet {
      font-family: 'Outfit', sans-serif;
      font-size: .95rem;
      font-weight: 700;
      color: #fff;
      line-height: 1.2;
    }

    .offcanvas-header .greet small {
      display: block;
      font-family: 'Manrope', sans-serif;
      font-size: .7rem;
      font-weight: 500;
      color: rgba(255, 255, 255, .55);
      letter-spacing: .06em;
      text-transform: uppercase;
      margin-bottom: 2px;
    }

    .offcanvas-header .btn-close {
      filter: brightness(0) invert(1) opacity(.7);
      opacity: 1;
    }

    .offcanvas-body {
      padding: 16px 14px 24px !important;
      background: #fff;
    }

    .offcanvas-body .accordion-button {
      background: transparent !important;
      color: var(--ink) !important;
      font-family: 'Outfit', sans-serif;
      font-size: .88rem;
      font-weight: 700;
      padding: 12px 14px !important;
      border-radius: 10px !important;
      box-shadow: none !important;
    }

    .offcanvas-body .accordion-button:not(.collapsed) {
      background: #fff2f5 !important;
      color: var(--brand) !important;
    }

    .offcanvas-body .accordion-item {
      border-radius: 10px !important;
      overflow: hidden;
      background: transparent;
    }

    .offcanvas-body .accordion-body {
      background: #faf7f9;
      border-radius: 10px;
      margin: 0 6px 8px;
    }

    .offcanvas-body .accordion-body a {
      display: block;
      font-size: .82rem;
      padding: 6px 4px;
      color: #56607a !important;
      border-radius: 6px;
      text-decoration: none;
      transition: color .25s, padding-left .25s, background .25s;
    }

    .offcanvas-body .accordion-body a:hover {
      color: var(--brand) !important;
      padding-left: 8px;
      background: rgba(255, 77, 109, .06);
    }

    .offcanvas-body ul.list-unstyled {
      border-top: 1px solid #efe6ec;
      padding-top: 12px !important;
      margin-top: 12px;
    }

    .offcanvas-body ul.list-unstyled a {
      font-family: 'Outfit', sans-serif;
      font-size: .85rem;
      font-weight: 600;
      padding: 11px 12px !important;
      border-radius: 10px;
      color: var(--ink) !important;
      transition: background .3s, color .3s, padding-left .3s;
    }

    .offcanvas-body ul.list-unstyled a:hover {
      background: #fff2f5;
      color: var(--brand) !important;
      padding-left: 18px !important;
    }

    .offcanvas-body ul.list-unstyled a.text-danger {
      color: #d92c4c !important;
    }

    /* ==========================================================
       RESPONSIVE
       ========================================================== */
    @media (max-width: 1199.98px) {
      .nav-container { padding: 0 24px; gap: 24px; }
      .nav-menu-item > a { padding: 10px 12px; font-size: .74rem; }
      .action-btn { min-width: 56px; padding: 8px 8px; }
      .action-btn .label { font-size: .6rem; }
    }

    @media (max-width: 991.98px) {
      body { padding-top: 104px; padding-bottom: 74px; }

      .nav-container {
        padding: 0 16px;
        height: 68px;
        gap: 12px;
        justify-content: space-between;
      }

      .nav-menu { display: none; }
      .nav-logo img { height: 42px; }

      /* ==========================================================
         🔥 MOBILE HEADER FIX: Sirf Cart dikhega, baaki hide
         ========================================================== */
      .nav-actions .action-btn:not([href="cart.php"]),
      .nav-actions .user-dropdown {
        display: none !important;
      }

      .nav-actions { gap: 0; }
      .action-btn { min-width: 42px; padding: 6px; }
      .action-btn .label { display: none; }
      .action-btn i { font-size: 1.15rem; }

      .usp-section { display: none; }
    }

    @media (max-width: 575.98px) {
      .scrolling-text { font-size: .7rem; }
      .offcanvas { width: 88vw !important; }
    }

    @media (prefers-reduced-motion: reduce) {
      *, *::before, *::after {
        animation-duration: .001ms !important;
        transition-duration: .001ms !important;
      }
    }
    .site-logo {
    width: 160px;
    height: 60px;
    object-fit: contain;
    display: block;
}
  </style>
</head>

<body>

  <!-- ==================== MOBILE OFFCANVAS ==================== -->
  <div class="offcanvas offcanvas-start" tabindex="-1" id="mobileMenu">
    <div class="offcanvas-header">
      <div class="d-flex align-items-center gap-3">
        <?php if ($isLoggedIn) { ?>
          <img src="<?= $userImg ?>" class="user-avatar" alt="user">
          <div class="greet">
            <small>Welcome back</small>
            <?= htmlspecialchars($userData['name']) ?>
          </div>
        <?php } else { ?>
          <div class="icon-avatar"><i class="fa-regular fa-user"></i></div>
          <div class="greet">
            <small>Welcome</small>
            Guest User
          </div>
        <?php } ?>
      </div>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body">
      <div class="accordion" id="mobileAccordion">
        <?php
        $cat_res = mysqli_query($conn, "SELECT * FROM categories WHERE status = 1");
        while ($cat = mysqli_fetch_assoc($cat_res)) {
          $cid = $cat['id'];
          ?>
          <div class="accordion-item mb-2 border-0">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#cat<?= $cid ?>">
                <?= htmlspecialchars($cat['name']) ?>
              </button>
            </h2>
            <div id="cat<?= $cid ?>" class="accordion-collapse collapse" data-bs-parent="#mobileAccordion">
              <div class="accordion-body">
                <?php
                $sub_res = mysqli_query($conn, "SELECT * FROM sub_categories WHERE category_id = $cid");
                if (mysqli_num_rows($sub_res) > 0) {
                  while ($sub = mysqli_fetch_assoc($sub_res)) { ?>
                    <a href="fetch_products.php?sub=<?= $sub['id'] ?>">
                      <i class="fa-solid fa-arrow-right-long me-1" style="font-size:.7rem;color:#ff5a3c;"></i>
                      <?= htmlspecialchars($sub['name']) ?>
                    </a>
                  <?php }
                } else { ?>
                  <a href="shops.php?cat=<?= $cid ?>">
                    <i class="fa-solid fa-arrow-right-long me-1" style="font-size:.7rem;color:#ff5a3c;"></i>
                    View All
                  </a>
                <?php } ?>
              </div>
            </div>
          </div>
        <?php } ?>
      </div>

      <ul class="list-unstyled p-2 mt-2">
        <li><a href="index.php" class="text-decoration-none d-block"><i class="fa-solid fa-house me-2"></i>Home</a></li>
        <?php if ($isLoggedIn) { ?>
          <li><a href="profile.php" class="text-decoration-none d-block"><i class="fa-regular fa-user me-2"></i>My Profile</a></li>
          <li><a href="logout.php" class="text-danger text-decoration-none d-block"><i class="fa-solid fa-arrow-right-from-bracket me-2"></i>Logout</a></li>
        <?php } else { ?>
          <li><a href="login.php" class="text-decoration-none d-block"><i class="fa-solid fa-arrow-right-to-bracket me-2"></i>Login / Register</a></li>
        <?php } ?>
      </ul>
    </div>
  </div>

  <!-- ==================== HEADER ==================== -->
  <div class="header-container">
    <!-- Announcement -->
    <div class="announcement-bar">
      <div class="scrolling-text">
        <span>🔥 Special Sale: 20% Off on Dog Food! &nbsp;•&nbsp; 🚚 Free Shipping Over ₹999 &nbsp;•&nbsp; 🐾 Welcome to SAM'S Store!</span>
      </div>
    </div>

    <!-- Main navbar -->
    <nav class="main-nav">
      <div class="nav-container">

        <!-- Mobile hamburger -->
        <a href="#" class="action-btn d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" style="min-width:auto;padding:8px;">
          <span class="icon-wrap"><i class="fa-solid fa-bars"></i></span>
        </a>

       <a href="/project3/index.php">
    <img src="/project3/images/logo.png"
         alt="Sam Store"
         class="site-logo">
</a>
        <!-- Menu -->
        <ul class="nav-menu">
          <?php
          $cat_res = mysqli_query($conn, "SELECT * FROM categories WHERE status = 1 LIMIT 5");
          while ($cat = mysqli_fetch_assoc($cat_res)) {
            $cid = $cat['id'];
            ?>
            <li class="nav-menu-item mega">
              <a href="fetch_products.php?cat=<?= $cid ?>">
                <?= htmlspecialchars($cat['name']) ?>
                <i class="fa-solid fa-chevron-down" style="font-size:.55rem;opacity:.55;"></i>
              </a>
              <div class="mega-menu">
                <div class="d-flex">
                  <?php
                  $sub_res = mysqli_query($conn, "SELECT * FROM sub_categories WHERE category_id = $cid");
                  while ($sub = mysqli_fetch_assoc($sub_res)) {
                    $sid = $sub['id'];
                    ?>
                    <div class="sub-cat-col">
                      <a href="fetch_products.php?sub=<?= $sid ?>" class="sub-title"><?= htmlspecialchars($sub['name']) ?></a>
                      <?php
                      $prod_res = mysqli_query($conn, "SELECT p.* FROM products p JOIN product_categories pc ON p.id = pc.product_id WHERE pc.subcategory_id = $sid LIMIT 5");
                      while ($p = mysqli_fetch_assoc($prod_res)) { ?>
                        <a href="product-details.php?id=<?= $p['id'] ?>" class="product-link"><?= htmlspecialchars($p['product_name']) ?></a>
                      <?php } ?>
                      <a href="fetch_products.php?sub=<?= $sid ?>" class="product-link view-all">View All →</a>
                    </div>
                  <?php } ?>
                </div>
              </div>
            </li>
          <?php } ?>
          <li class="nav-menu-item">
            <a href="shops.php">Shop All</a>
          </li>
        </ul>

        <!-- Actions -->
        <div class="nav-actions">

          <a href="search.php" class="action-btn" title="Search">
            <span class="icon-wrap"><i class="fa-solid fa-magnifying-glass"></i></span>
            <span class="label">Search</span>
          </a>

          <?php if ($isLoggedIn) { ?>
            <div class="user-dropdown">
              <a href="#" class="action-btn" title="Account">
                <span class="icon-wrap"><i class="fa-regular fa-circle-user"></i></span>
                <span class="label">Account</span>
              </a>
              <div class="dropdown-menu">
                <div class="user-head">
                  <img src="<?= $userImg ?>" alt="user">
                  <h6><?= htmlspecialchars($userData['name']) ?></h6>
                  <small><?= htmlspecialchars($userData['email']) ?></small>
                </div>
                <a href="profile.php" class="drop-item"><i class="fa-regular fa-user"></i> My Profile</a>
                <a href="orders.php" class="drop-item"><i class="fa-solid fa-box"></i> My Orders</a>
                <a href="logout.php" class="drop-item danger"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
              </div>
            </div>
          <?php } else { ?>
            <a href="login.php" class="action-btn" title="Login">
              <span class="icon-wrap"><i class="fa-regular fa-circle-user"></i></span>
              <span class="label">Login</span>
            </a>
          <?php } ?>

          <a href="wishlist.php" class="action-btn" title="Wishlist">
            <span class="icon-wrap"><i class="fa-regular fa-heart"></i></span>
            <span class="label">Wishlist</span>
          </a>

          <a href="cart.php" class="action-btn" title="Cart">
            <span class="icon-wrap">
              <i class="fa-solid fa-basket-shopping"></i>
              <?php
              $cart_count = 0;
              if (isset($_SESSION['cart'])) {
                foreach ($_SESSION['cart'] as $item) {
                  $cart_count += $item['qty'];
                }
              }
              ?>
              <span class="badge-cart"><?= $cart_count ?></span>
            </span>
            <span class="label">Bag</span>
          </a>

        </div>
      </div>
    </nav>
  </div>

  <!-- ==================== USP BAR ==================== -->
  <div class="container-fluid usp-section">
    <div class="container">
      <div class="row text-center align-items-center g-2">
        <div class="col"><div class="usp-inner"><i class="fa-solid fa-truck-fast"></i><span>Safe Delivery</span></div></div>
        <div class="col"><div class="usp-inner"><i class="fa-solid fa-face-smile"></i><span>Friendly Service</span></div></div>
        <div class="col"><div class="usp-inner"><i class="fa-solid fa-shield-halved"></i><span>Secure Payment</span></div></div>
        <div class="col"><div class="usp-inner"><i class="fa-solid fa-headset"></i><span>24/7 Support</span></div></div>
        <div class="col"><div class="usp-inner"><i class="fa-solid fa-rotate-left"></i><span>30 Day Return</span></div></div>
      </div>
    </div>
  </div>

  <!-- ==================== MOBILE BOTTOM NAV ==================== -->
  <div class="mobile-bottom-nav d-lg-none">
    <a href="index.php" class="active">
      <i class="fa-solid fa-house"></i>
      <span>Home</span>
    </a>
    <a href="search.php">
      <i class="fa-solid fa-magnifying-glass"></i>
      <span>Search</span>
    </a>
    <a href="wishlist.php">
      <i class="fa-regular fa-heart"></i>
      <span>Wishlist</span>
    </a>
    <?php if ($isLoggedIn) { ?>
      <a href="profile.php">
        <i class="fa-solid fa-user"></i>
        <span>Account</span>
      </a>
    <?php } else { ?>
      <a href="login.php">
        <i class="fa-regular fa-user"></i>
        <span>Account</span>
      </a>
    <?php } ?>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    // auto-active bottom nav
    document.querySelectorAll('.mobile-bottom-nav a').forEach(link => {
      const current = location.pathname.split('/').pop() || 'index.php';
      if (link.getAttribute('href') === current) {
        document.querySelectorAll('.mobile-bottom-nav a').forEach(l => l.classList.remove('active'));
        link.classList.add('active');
      }
    });
  </script>
</body>
</html>