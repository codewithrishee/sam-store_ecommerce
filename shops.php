<?php
include "config/db.php";
include "include/header.php";
?>

<?php
function shortProductDescription($description, $limit = 14) {

    $description = trim(strip_tags((string)$description));

    if ($description === '') {
        return '';
    }

    $words = preg_split('/\s+/u', $description, -1, PREG_SPLIT_NO_EMPTY);

    $short = implode(' ', array_slice($words, 0, $limit));

    if (count($words) > $limit) {
        $short .= '...';
    }

    return htmlspecialchars($short, ENT_QUOTES, 'UTF-8');
}
?>
<!--external css-->
<link rel="stylesheet" href="style.css">

<style>
    /* ==========================================================================
   SAM'S PETS — MASTER STYLESHEET
   Covers: Home (banner, sliders, promos) + Shop (category filter + grid)
   ========================================================================== */

@import url('https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700;800;900&display=swap');

/* ------------------------------ TOKENS ---------------------------------- */
:root {
    --ink: #0f172a;
    --ink-soft: #2b3446;
    --muted: #7a8194;
    --brand: #ff5a3c;
    --brand-2: #ff2d55;
    --brand-soft: #fff1ec;
    --accent: #0ea5a4;
    --cream: #fbf7f2;
    --card: #ffffff;
    --line: #efe8e1;
    --radius: 18px;
    --radius-lg: 26px;
    --shadow-sm: 0 4px 14px rgba(15, 23, 42, .06);
    --shadow-md: 0 16px 44px rgba(15, 23, 42, .12);
    --shadow-lg: 0 26px 70px rgba(15, 23, 42, .18);
    --ease: cubic-bezier(.2, .8, .2, 1);
}

/* ------------------------------ RESET ----------------------------------- */
*,
*::before,
*::after {
    box-sizing: border-box;
}

html {
    scroll-behavior: smooth;
    -webkit-text-size-adjust: 100%;
}

body {
    font-family: 'Manrope', system-ui, -apple-system, 'Segoe UI', sans-serif;
    color: var(--ink);
    background: #fff;
    line-height: 1.6;
    overflow-x: hidden;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

h1, h2, h3, h4, h5, h6,
.feature-title {
    font-family: 'Outfit', 'Manrope', sans-serif;
    letter-spacing: -.02em;
    color: var(--ink);
}

img { max-width: 100%; }

a { transition: color .25s var(--ease); }

::selection { background: var(--brand); color: #fff; }

::-webkit-scrollbar { width: 10px; }
::-webkit-scrollbar-track { background: #f4f1ee; }
::-webkit-scrollbar-thumb { background: #d8d0c8; border-radius: 99px; }
::-webkit-scrollbar-thumb:hover { background: var(--brand); }

/* ==========================================================================
   1. FLOATING ACTION BUTTONS
   ========================================================================== */
.floating-btns {
    position: fixed;
    right: 18px;
    bottom: 26px;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.floating-btns .floating-btns {
    position: static;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.floating-btns .box,
.floating-btns .box2 {
    display: flex;
    align-items: center;
    gap: 10px;
    height: 54px;
    padding: 0 20px 0 16px;
    border-radius: 999px;
    color: #fff;
    font-weight: 700;
    font-size: .84rem;
    letter-spacing: .01em;
    white-space: nowrap;
    cursor: pointer;
    user-select: none;
    transition: transform .35s var(--ease), box-shadow .35s var(--ease), filter .3s;
}

.floating-btns .box img,
.floating-btns .box2 img {
    width: 24px;
    height: 24px;
    object-fit: contain;
    flex: 0 0 auto;
    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, .25));
}

.floating-btns .box {
    background: linear-gradient(135deg, #ff8459, var(--brand));
    box-shadow: 0 14px 30px rgba(255, 90, 60, .38);
}

.floating-btns .box2 {
    background: linear-gradient(135deg, #26314a, #0b1220);
    box-shadow: 0 14px 30px rgba(11, 18, 32, .35);
}

.floating-btns .box:hover,
.floating-btns .box2:hover {
    transform: translateX(-5px) scale(1.02);
    filter: brightness(1.06);
}

.floating-btns .text-white { color: #fff; }

/* ==========================================================================
   2. OWL GLOBAL OVERRIDES
   ========================================================================== */
.owl-carousel .owl-nav button span { display: none; }
.owl-carousel .owl-nav button.owl-prev::after { content: '\2039'; }
.owl-carousel .owl-nav button.owl-next::after { content: '\203A'; }
.owl-carousel .owl-nav button::after {
    font-family: 'Outfit', sans-serif;
    font-size: 2rem;
    font-weight: 700;
    line-height: 1;
    display: block;
    margin-top: -6px;
}
.owl-carousel .owl-item img { display: block; width: 100%; }

/* ==========================================================================
   3. HERO / BANNER SLIDER  (+ PEEK / OVERFLOW EFFECT)
   ========================================================================== */
.banner-slider {
    position: relative;
    max-width: 1480px;
    margin: 22px auto 0;
    padding: 10px clamp(14px, 6vw, 96px) 56px;
    overflow: visible;
}

.banner-slider .owl-stage-outer { overflow: visible; }

.banner-slider .item {
    position: relative;
    height: clamp(300px, 50vw, 600px);
    border-radius: var(--radius-lg);
    overflow: hidden;
    background: #0b1220;
    transform: scale(.94);
    opacity: .45;
    box-shadow: 0 20px 60px rgba(15, 23, 42, .22);
    transition: transform .65s var(--ease), opacity .65s var(--ease), box-shadow .65s var(--ease);
}

.banner-slider .owl-item.active .item {
    transform: scale(1);
    opacity: 1;
    box-shadow: 0 30px 70px rgba(15, 23, 42, .28);
}

.banner-slider .item > img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transform: scale(1.07);
    transition: transform 7s linear;
}

.banner-slider .owl-item.active .item > img { transform: scale(1); }

.banner-slider .item::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(100deg,
            rgba(6, 10, 20, .82) 0%,
            rgba(6, 10, 20, .55) 34%,
            rgba(6, 10, 20, .15) 62%,
            rgba(6, 10, 20, 0) 100%);
    z-index: 1;
    pointer-events: none;
}

.slide-content {
    position: absolute;
    z-index: 2;
    top: 50%;
    left: clamp(22px, 5vw, 72px);
    transform: translateY(-50%);
    max-width: min(580px, 62%);
    color: #fff;
}

.slide-content h1 {
    font-size: clamp(1.5rem, 3.6vw, 3.4rem);
    font-weight: 900;
    line-height: 1.06;
    letter-spacing: -.035em;
    margin: 0 0 .6rem;
    color: #fff;
    text-shadow: 0 8px 30px rgba(0, 0, 0, .35);
}

.slide-content p {
    font-size: clamp(.88rem, 1.15vw, 1.06rem);
    line-height: 1.6;
    color: rgba(255, 255, 255, .88);
    max-width: 46ch;
    margin: 0;
}

.slide-content .btn {
    border: 0;
    border-radius: 999px;
    padding: 13px 30px;
    font-family: 'Outfit', sans-serif;
    font-weight: 700;
    font-size: .88rem;
    letter-spacing: .03em;
    text-transform: uppercase;
    box-shadow: 0 14px 32px rgba(0, 0, 0, .28);
    transition: transform .35s var(--ease), box-shadow .35s var(--ease), filter .3s;
}

.slide-content .btn:hover {
    transform: translateY(-3px);
    filter: brightness(1.08);
    box-shadow: 0 20px 42px rgba(0, 0, 0, .35);
}

.banner-slider .owl-nav {
    position: absolute;
    inset: 0;
    margin: 0;
    pointer-events: none;
}

.banner-slider .owl-nav button.owl-prev,
.banner-slider .owl-nav button.owl-next {
    position: absolute;
    top: calc(50% - 23px);
    transform: translateY(-50%);
    width: 52px;
    height: 52px;
    display: grid;
    place-items: center;
    border-radius: 50%;
    border: 1px solid rgba(255, 255, 255, .38);
    background: rgba(255, 255, 255, .14);
    -webkit-backdrop-filter: blur(12px);
    backdrop-filter: blur(12px);
    color: #fff;
    pointer-events: auto;
    transition: background .3s, transform .3s, border-color .3s;
}

.banner-slider .owl-nav button.owl-prev { left: 26px; }
.banner-slider .owl-nav button.owl-next { right: 26px; }

.banner-slider .owl-nav button::after { font-size: 2.2rem; color: #fff; }

.banner-slider .owl-nav button:hover {
    background: var(--brand);
    border-color: var(--brand);
    transform: translateY(-50%) scale(1.08);
}

.banner-slider .owl-dots {
    position: absolute;
    left: 50%;
    bottom: 8px;
    transform: translateX(-50%);
    display: flex;
    align-items: center;
    gap: 9px;
    margin: 0;
}

.banner-slider .owl-dots .owl-dot { outline: none; }

.banner-slider .owl-dots .owl-dot span {
    display: block;
    width: 9px;
    height: 9px;
    border-radius: 99px;
    background: #d9d2cb;
    transition: width .4s var(--ease), background .4s var(--ease);
}

.banner-slider .owl-dots .owl-dot.active span {
    width: 34px;
    background: linear-gradient(90deg, var(--brand), #ff9468);
}

/* ==========================================================================
   4. PRODUCT SLIDERS — OVERFLOW EFFECT
   ========================================================================== */
.product-slider,
.prod-slider {
    position: relative;
    padding: 8px 0 12px;
    overflow: visible;
}

.product-slider .owl-stage-outer,
.prod-slider .owl-stage-outer { overflow: visible; }

.product-slider .owl-item > .product-item,
.prod-slider .owl-item > .product-item {
    height: 100%;
    padding: 6px 0 12px;
}

.product-slider .owl-nav,
.prod-slider .owl-nav {
    position: absolute;
    inset: 0;
    margin: 0;
    pointer-events: none;
}

.product-slider .owl-nav button,
.prod-slider .owl-nav button {
    position: absolute;
    top: 40%;
    transform: translateY(-50%);
    width: 48px;
    height: 48px;
    display: grid;
    place-items: center;
    border-radius: 50%;
    background: #fff;
    color: var(--ink);
    border: 1px solid var(--line);
    box-shadow: var(--shadow-sm);
    pointer-events: auto;
    opacity: 0;
    transition: opacity .35s var(--ease), background .3s, color .3s, transform .3s;
}

.product-slider .owl-nav button::after,
.prod-slider .owl-nav button::after {
    font-size: 1.9rem;
    margin-top: -4px;
}

.product-slider .owl-nav button.owl-prev,
.prod-slider .owl-nav button.owl-prev { left: -16px; }

.product-slider .owl-nav button.owl-next,
.prod-slider .owl-nav button.owl-next { right: -16px; }

.product-slider:hover .owl-nav button,
.prod-slider:hover .owl-nav button { opacity: 1; }

.product-slider .owl-nav button:hover,
.prod-slider .owl-nav button:hover {
    background: var(--ink);
    color: #fff;
    border-color: var(--ink);
}

/* ==========================================================================
   5. SECTION HEADINGS
   ========================================================================== */
.feature-title {
    position: relative;
    font-size: clamp(1.35rem, 2.6vw, 2.05rem);
    font-weight: 800;
    letter-spacing: -.03em;
    margin: 0;
    padding-bottom: 16px;
}

.feature-title::after {
    content: '';
    position: absolute;
    left: 0;
    bottom: 0;
    width: 58px;
    height: 4px;
    border-radius: 99px;
    background: linear-gradient(90deg, var(--brand), #ffb199);
}

h1.text-center {
    font-weight: 800;
    font-size: clamp(1.4rem, 2.8vw, 2.1rem);
    letter-spacing: -.03em;
    margin-bottom: 2rem !important;
    position: relative;
}

h1.text-center::after {
    content: '';
    position: absolute;
    left: 50%;
    bottom: -2px;
    transform: translateX(-50%);
    width: 58px;
    height: 4px;
    border-radius: 99px;
    background: linear-gradient(90deg, var(--brand), #ffb199);
}

.view-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 22px;
    border-radius: 999px;
    border: 1.5px solid var(--line);
    background: #fff;
    color: var(--ink) !important;
    font-family: 'Outfit', sans-serif;
    font-size: .82rem;
    font-weight: 700;
    letter-spacing: .04em;
    text-transform: uppercase;
    text-decoration: none;
    white-space: nowrap;
    transition: all .35s var(--ease);
}

.view-btn::after {
    content: '\2192';
    font-size: 1rem;
    transition: transform .35s var(--ease);
}

.view-btn:hover {
    background: var(--ink);
    border-color: var(--ink);
    color: #fff !important;
    box-shadow: var(--shadow-md);
    transform: translateY(-2px);
}

.view-btn:hover::after { transform: translateX(4px); }

/* ==========================================================================
   6. ★ PRODUCT CARD (same card css) ★
   ========================================================================== */
.product-item { height: 100%; }

.product-card {
    position: relative;
    display: flex;
    flex-direction: column;
    height: 100%;
    background: var(--card);
    border: 1px solid var(--line) !important;
    border-radius: var(--radius);
    overflow: hidden;
    box-shadow: 0 2px 6px rgba(15, 23, 42, .04);
    transition: transform .45s var(--ease), box-shadow .45s var(--ease), border-color .45s var(--ease);
}

.product-card:hover {
    transform: translateY(-7px);
    box-shadow: var(--shadow-md);
    border-color: #ffe2d8 !important;
}

/* ---------- image area ---------- */
.product-img {
    position: relative;
    aspect-ratio: 1 / 1;
    overflow: hidden;
    background: linear-gradient(160deg, #f8f3ee, #f1ebe4);
}

.product-img > a {
    display: block;
    width: 100%;
    height: 100%;
}

.product-img img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: opacity .5s var(--ease), transform .9s var(--ease);
}

.product-img .img1 { z-index: 1; opacity: 1; }
.product-img .img2 { z-index: 2; opacity: 0; }

.product-card:hover .img1 { opacity: 0; }
.product-card:hover .img2 { opacity: 1; }
.product-card:hover .product-img img { transform: scale(1.06); }

/* ---------- discount badge ---------- */
.product-img .discount {
    position: absolute;
    top: 12px;
    left: 12px;
    z-index: 5;
    padding: 5px 11px;
    border-radius: 999px;
    background: linear-gradient(135deg, var(--brand), var(--brand-2));
    color: #fff;
    font-family: 'Outfit', sans-serif;
    font-size: .7rem;
    font-weight: 800;
    letter-spacing: .03em;
    line-height: 1.4;
    box-shadow: 0 8px 20px rgba(255, 45, 85, .35);
}

/* ---------- hover icons (SIRF HEART) ---------- */
.hover-icons {
    position: absolute;
    top: 12px;
    right: 12px;
    z-index: 6;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.hover-icons a {
    width: 38px;
    height: 38px;
    display: grid;
    place-items: center;
    border-radius: 50%;
    background: #fff;
    color: var(--ink);
    font-size: .85rem;
    text-decoration: none;
    box-shadow: 0 8px 20px rgba(15, 23, 42, .14);
    opacity: 0;
    transform: translateX(16px);
    transition: opacity .35s var(--ease), transform .35s var(--ease), background .3s, color .3s;
}

.product-card:hover .hover-icons a {
    opacity: 1;
    transform: translateX(0);
}

.hover-icons a:hover {
    background: var(--brand);
    color: #fff;
    transform: scale(1.1) !important;
}

.hover-icons .wishlist-btn i.fa-solid { color: var(--brand-2); }

/* ---------- body ---------- */
.product-card .card-body {
    flex: 1 1 auto;
    padding: 16px 16px 18px;
}

.product-card .card-body > p.text-muted {
    font-size: .68rem !important;
    font-weight: 800;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: var(--muted) !important;
    margin-bottom: 6px !important;
}

.product-card h6 {
    font-family: 'Outfit', sans-serif;
    font-size: .95rem;
    font-weight: 700;
    line-height: 1.35;
    margin-bottom: 7px;
    color: var(--ink);
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    min-height: 2.6em;
    transition: color .3s var(--ease);
}

.product-card h6.small { font-size: .9rem; }

.product-card:hover h6 { color: var(--brand); }

.product-card h6 a {
    color: inherit;
    text-decoration: none;
}

.product-description {
    font-size: .78rem;
    line-height: 1.55;
    color: var(--muted);
    margin-bottom: 12px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    min-height: 2.4em;
}

/* ---------- price row ---------- */
.product-card .card-body > div.d-flex {
    padding-right: 50px;
    gap: 8px;
    flex-wrap: wrap;
    align-items: baseline !important;
}

.product-card .text-danger {
    color: var(--brand) !important;
    font-family: 'Outfit', sans-serif;
    font-size: 1.06rem;
    font-weight: 800;
    letter-spacing: -.01em;
}

.product-card .card-body small.text-muted {
    font-size: .8rem;
    color: #a3a9b8 !important;
}

/* ---------- cart button ---------- */
.cart-btn {
    position: absolute;
    right: 14px;
    bottom: 14px;
    width: 44px;
    height: 44px;
    display: grid;
    place-items: center;
    border: 0;
    border-radius: 50%;
    background: var(--ink);
    color: #fff;
    font-size: .95rem;
    cursor: pointer;
    box-shadow: 0 10px 24px rgba(15, 23, 42, .24);
    opacity: 0;
    transform: translateY(10px) scale(.86);
    transition: opacity .35s var(--ease), transform .35s var(--ease), background .3s;
    z-index: 7;
}

.product-card:hover .cart-btn {
    opacity: 1;
    transform: translateY(0) scale(1);
}

.cart-btn:hover {
    background: var(--brand);
    transform: translateY(-3px) rotate(-8deg) scale(1.05);
}

.cart-btn:active { transform: scale(.95); }

/* ==========================================================================
   7. PROMO / SMALL BANNERS
   ========================================================================== */
.promo-box {
    position: relative;
    display: flex;
    align-items: center;
    min-height: 250px;
    border-radius: var(--radius-lg);
    overflow: hidden;
    background: #0b1220;
    box-shadow: var(--shadow-sm);
    transition: box-shadow .45s var(--ease), transform .45s var(--ease);
}

.promo-box:hover {
    box-shadow: var(--shadow-lg);
    transform: translateY(-4px);
}

.promo-box img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 1s var(--ease);
}

.promo-box:hover img { transform: scale(1.08); }

.promo-box::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(95deg,
            rgba(8, 12, 22, .80) 0%,
            rgba(8, 12, 22, .45) 45%,
            rgba(8, 12, 22, 0) 78%);
    pointer-events: none;
}

.promo-content {
    position: relative;
    z-index: 2;
    padding: clamp(22px, 4vw, 42px);
    max-width: 66%;
    color: #fff;
}

.promo-content h4 {
    font-size: clamp(1.05rem, 1.9vw, 1.55rem);
    font-weight: 800;
    letter-spacing: -.02em;
    color: #fff;
    margin-bottom: 8px;
}

.promo-content p {
    font-size: .87rem;
    line-height: 1.55;
    color: rgba(255, 255, 255, .85);
    margin-bottom: 16px;
}

.promo-btn {
    display: inline-block;
    padding: 11px 24px;
    border-radius: 999px;
    color: #fff !important;
    font-family: 'Outfit', sans-serif;
    font-size: .8rem;
    font-weight: 700;
    letter-spacing: .05em;
    text-transform: uppercase;
    text-decoration: none;
    box-shadow: 0 12px 26px rgba(0, 0, 0, .25);
    transition: transform .35s var(--ease), box-shadow .35s var(--ease), filter .3s;
}

.promo-btn:hover {
    transform: translateY(-3px);
    filter: brightness(1.08);
    box-shadow: 0 18px 34px rgba(0, 0, 0, .32);
}

/* ==========================================================================
   8. ★ SHOP PAGE — CATEGORY SIDEBAR ★
   ========================================================================== */
.product-area { row-gap: 1.1rem; }

.category-list {
    position: sticky;
    top: 96px;
    list-style: none;
    margin: 0;
    padding: 26px 18px 20px;
    border-radius: var(--radius-lg);
    background:
        radial-gradient(120% 90% at 0% 0%, rgba(255, 90, 60, .22) 0%, transparent 55%),
        radial-gradient(100% 80% at 100% 100%, rgba(14, 165, 164, .18) 0%, transparent 60%),
        linear-gradient(160deg, #1a2338 0%, #0b1220 100%);
    box-shadow: var(--shadow-md);
    overflow: hidden;
}

.category-list::before {
    content: '';
    position: absolute;
    top: 0;
    left: 22px;
    right: 22px;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .35), transparent);
}

.category-list h2 {
    font-size: 1.12rem !important;
    font-weight: 800 !important;
    letter-spacing: .02em;
    color: #fff !important;
    text-align: left !important;
    margin: 0 0 18px !important;
    padding: 0 10px 14px;
    border-bottom: 1px solid rgba(255, 255, 255, .10);
    position: relative;
}

.category-list h2::after {
    content: '';
    position: absolute;
    left: 10px;
    bottom: -1px;
    width: 44px;
    height: 2px;
    border-radius: 99px;
    background: linear-gradient(90deg, var(--brand), #ffb199);
}

.category-list li {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 11px 16px 11px 18px;
    margin-bottom: 6px;
    border-radius: 12px;
    color: rgba(255, 255, 255, .70);
    font-family: 'Outfit', sans-serif;
    font-size: .88rem;
    font-weight: 600;
    letter-spacing: .01em;
    cursor: pointer;
    user-select: none;
    transition: color .3s var(--ease), background .3s var(--ease),
                transform .3s var(--ease), padding-left .3s var(--ease);
}

.category-list li::before {
    content: '';
    position: absolute;
    left: 6px;
    top: 50%;
    transform: translateY(-50%) scaleY(0);
    width: 3px;
    height: 18px;
    border-radius: 99px;
    background: var(--brand);
    transition: transform .3s var(--ease);
}

.category-list li::after {
    content: '\203A';
    font-family: 'Outfit', sans-serif;
    font-size: 1.05rem;
    line-height: 1;
    opacity: 0;
    transform: translateX(-6px);
    transition: opacity .3s var(--ease), transform .3s var(--ease);
}

.category-list li:hover {
    color: #fff;
    background: rgba(255, 255, 255, .08);
    padding-left: 22px;
}

.category-list li:hover::before { transform: translateY(-50%) scaleY(1); }
.category-list li:hover::after { opacity: .75; transform: translateX(0); }

.category-list li.active {
    color: #fff;
    font-weight: 800;
    background: linear-gradient(135deg, var(--brand), var(--brand-2));
    box-shadow: 0 12px 26px rgba(255, 45, 85, .34);
    padding-left: 22px;
}

.category-list li.active::before {
    background: #fff;
    transform: translateY(-50%) scaleY(1);
}

.category-list li.active::after { opacity: 1; transform: translateX(0); }

.category-list li:active { transform: scale(.98); }

/* ==========================================================================
   9. SECTION SPACING
   ========================================================================== */
.container.my-5 {
    margin-top: 4rem !important;
    margin-bottom: 4rem !important;
}

.container.bg-light {
    padding: 1.5rem;
    border-radius: var(--radius-lg);
    background: var(--cream) !important;
}

/* ==========================================================================
   10. RESPONSIVE
   ========================================================================== */
@media (max-width: 1199.98px) {
    .product-slider .owl-nav button.owl-prev,
    .prod-slider .owl-nav button.owl-prev { left: -8px; }

    .product-slider .owl-nav button.owl-next,
    .prod-slider .owl-nav button.owl-next { right: -8px; }
}

@media (max-width: 991.98px) {
    .category-list {
        position: static;
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px;
        margin-bottom: 18px;
        border-radius: 16px;
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }

    .category-list::-webkit-scrollbar { display: none; }

    .category-list h2 { display: none; }

    .category-list li {
        flex: 0 0 auto;
        margin: 0;
        padding: 9px 18px;
        border-radius: 999px;
        font-size: .82rem;
        white-space: nowrap;
        background: rgba(255, 255, 255, .07);
        border: 1px solid rgba(255, 255, 255, .10);
    }

    .category-list li::before,
    .category-list li::after { display: none; }

    .category-list li:hover { padding-left: 18px; }

    .category-list li.active {
        padding-left: 18px;
        border-color: transparent;
    }

    .banner-slider { padding-bottom: 48px; }
    .slide-content { max-width: 78%; }
    .promo-box { min-height: 220px; }
}

@media (max-width: 767.98px) {
    .product-slider .owl-nav,
    .prod-slider .owl-nav,
    .banner-slider .owl-nav { display: none; }

    .banner-slider { padding: 8px 14px 44px; }

    .banner-slider .item {
        height: clamp(260px, 62vw, 380px);
        border-radius: 20px;
        transform: scale(.97);
    }

    .slide-content { left: 20px; max-width: 86%; }

    .slide-content .btn {
        padding: 10px 22px;
        font-size: .76rem;
    }

    .floating-btns { right: 14px; bottom: 16px; gap: 10px; }

    .floating-btns .box,
    .floating-btns .box2 {
        width: 52px;
        height: 52px;
        padding: 0;
        justify-content: center;
        gap: 0;
    }

    .floating-btns .box span,
    .floating-btns .box2 span { display: none; }

    .hover-icons a { width: 32px; height: 32px; font-size: .75rem; }

    .product-card .card-body { padding: 12px 12px 14px; }

    .product-card h6,
    .product-card h6.small { font-size: .84rem; }

    .product-description { display: none; }

    .product-card .text-danger { font-size: .94rem; }

    .cart-btn {
        width: 36px;
        height: 36px;
        font-size: .8rem;
        right: 10px;
        bottom: 10px;
    }

    .product-card .card-body > div.d-flex { padding-right: 40px; }

    .promo-content { max-width: 80%; }

    .container.my-5 {
        margin-top: 2.6rem !important;
        margin-bottom: 2.6rem !important;
    }
}

@media (hover: none) {
    .hover-icons a { opacity: 1; transform: none; }
    .cart-btn { opacity: 1; transform: none; }
}

@media (max-width: 575.98px) {
    .feature-title { font-size: 1.15rem; padding-bottom: 12px; }
    .view-btn { padding: 8px 16px; font-size: .72rem; }
    .discount { font-size: .64rem; padding: 4px 9px; }
    .category-list li { padding: 8px 15px; font-size: .78rem; }
}

@media (prefers-reduced-motion: reduce) {
    *, *::before, *::after {
        animation-duration: .001ms !important;
        transition-duration: .001ms !important;
        scroll-behavior: auto !important;
    }
}

/* SHOP PAGE ONLY - BOOTSTRAP GRID FIX */

@media (min-width: 992px) {

    .container.my-5 > .row > .col-lg-3 {
        flex: 0 0 25% !important;
        max-width: 25% !important;
    }

    .container.my-5 > .row > .col-lg-9 {
        flex: 0 0 75% !important;
        max-width: 75% !important;
    }

    .product-area > .product-item {
    flex: 0 0 25% !important;
    max-width: 25% !important;
    width: 25% !important;
}
}


</style>


<!--sticky boxes-->
<div class="floating-btns">
    <div class="floating-btns">
        <div class="box">
            <img src="images/buy.png" alt="">
            <span class="text-white">Buy Now</span>
        </div>
        <div class="box2">
            <img src="images/23.png" alt="">
            <span class="text-white">Support</span>
        </div>
    </div>
</div>

<div class="container my-5">
<div class="row">

<!-- LEFT CATEGORY -->
<?php
$cats = mysqli_query($conn,"
SELECT * FROM categories 
ORDER BY id DESC
");
?>
<div class="col-lg-3 mb-3">
<ul class="category-list">
    <h2 class="text-center text-white mb-4 fw-bold">All Categories</h2>
    <li class="active" data-cat="all">All</li>
<?php while($cat = mysqli_fetch_assoc($cats)) { ?>
    <li data-cat="<?= $cat['id']; ?>">
        <?= $cat['name']; ?>
    </li>
<?php } ?>
</ul>
</div>

<!--  RIGHT PRODUCTS -->
<div class="col-lg-9">
<div class="row g-3 product-area">
<?php
$products = mysqli_query($conn,"
SELECT p.*, 
GROUP_CONCAT(pc.category_id) as cat_ids
FROM products p
LEFT JOIN product_categories pc ON p.id = pc.product_id
GROUP BY p.id
ORDER BY p.id DESC
");
while($row = mysqli_fetch_assoc($products)){
$new_price = !empty($row['new_price']) ? $row['new_price'] : 0;
$old_price = !empty($row['old_price']) ? $row['old_price'] : 0;
$discount = 0;
if($old_price > 0 && $new_price > 0){
    $discount = round((($old_price - $new_price) / $old_price) * 100);
}
?>
<!--  2 PER ROW MOBILE -->
<div class="col-lg-4 col-md-6 col-6 product-item" data-cat="<?= $row['cat_ids']; ?>">
<div class="card product-card border-0 h-100">

    <!-- image + hover icons -->
    <div class="product-img">
        <?php if($discount > 0){ ?>
        <span class="discount">-<?= $discount; ?>%</span>
        <?php } ?>

        <a href="product-details.php?id=<?= $row['id']; ?>">
            <img src="images/<?= $row['image1']; ?>" class="img-fluid img1" alt="">
            <img src="images/<?= $row['image2']; ?>" class="img-fluid img2" alt="">
        </a>

        <!-- SIRF HEART ICON -->
        <div class="hover-icons">
            <a href="javascript:void(0)" class="wishlist-btn" data-id="<?= $row['id']; ?>" title="Add to Wishlist">
                <i class="fa fa-heart"></i>
            </a>
            <a href="product-details.php?id=<?= $row['id']; ?>">
                                        <i class="fa fa-eye"></i>
                                    </a>
        </div>
    </div>

    <!-- body -->
    <div class="card-body position-relative">
        <p class="text-muted mb-1">SAM'S Pets</p>

        <h6 class="small">
            <a href="product-details.php?id=<?= $row['id']; ?>">
                <?= $row['product_name']; ?>
            </a>
        </h6>

        <p class="product-description">
            <?= shortProductDescription($row['description'] ?? '', 10); ?>
        </p>

        <div class="d-flex align-items-center flex-wrap">
            <span class="text-danger fw-bold me-2">
                Rs.<?= number_format($new_price, 2); ?>
            </span>
            <?php if($old_price > 0){ ?>
            <small class="text-muted text-decoration-line-through">
                Rs.<?= number_format($old_price, 2); ?>
            </small>
            <?php } ?>
        </div>

        <button class="cart-btn" title="Add to Cart">
            <i class="fa fa-shopping-cart"></i>
        </button>
    </div>

</div>
</div>
<?php } ?>
</div>
</div>
</div>
</div>


<script>
document.querySelectorAll(".category-list li").forEach(btn => {
    btn.addEventListener("click", function(){

        document.querySelectorAll(".category-list li").forEach(li=>li.classList.remove("active"));
        this.classList.add("active");

        let cat = this.getAttribute("data-cat");

        document.querySelectorAll(".product-item").forEach(item => {

            let itemCats = item.getAttribute("data-cat") || "";

            if(cat === "all"){
                item.style.display = "block";
            } else if(itemCats.includes(cat)){
                item.style.display = "block";
            } else {
                item.style.display = "none";
            }

        });

    });
});
</script>


<?php
include "include/footer.php";
?>