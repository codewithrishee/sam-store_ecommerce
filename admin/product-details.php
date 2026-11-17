<?php
include("config/db.php");

if(!isset($_GET['id'])){
    header("Location: shop.php");
    exit;
}

$id = (int)$_GET['id'];
$result = mysqli_query($conn, "SELECT * FROM products WHERE id='$id'");
$data = mysqli_fetch_assoc($result);

if(!$data){
    echo "Product not found";
    exit;
}

$discount = 0;
if($data['old_price'] > 0 && $data['new_price'] > 0){
    $discount = round((($data['old_price'] - $data['new_price']) / $data['old_price']) * 100);
}
?>

<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

<style>
    /* ==========================================================
       PRODUCT DETAILS — PREMIUM STYLES (Navy + Pink)
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
        font-family: 'Manrope', system-ui, sans-serif;
        background: #faf7f9;
        color: var(--ink);
    }

    .product-page {
        padding: 40px 0 60px;
    }

    /* ================= LEFT: IMAGE GALLERY ================= */
    .gallery-wrap {
        position: sticky;
        top: 100px;
    }

    .main-img {
        position: relative;
        background: linear-gradient(160deg, #faf6f1, #f3ece4);
        border-radius: 24px;
        padding: 20px;
        border: 1px solid var(--line);
        box-shadow: 0 15px 40px rgba(46, 50, 84, 0.08);
        overflow: hidden;
    }

    .main-img img {
        width: 100%;
        height: clamp(320px, 42vw, 520px);
        object-fit: contain;
        border-radius: 16px;
        transition: transform .6s var(--ease);
        display: block;
    }

    .main-img:hover img {
        transform: scale(1.08);
    }

    /* Discount Badge */
    .discount-badge {
        position: absolute;
        top: 20px;
        left: 20px;
        z-index: 5;
        background: linear-gradient(135deg, var(--brand), var(--brand-2));
        color: #fff;
        padding: 8px 18px;
        border-radius: 999px;
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        font-size: 0.8rem;
        letter-spacing: 0.03em;
        box-shadow: 0 10px 24px rgba(255, 77, 109, 0.4);
        animation: pulseBadge 2s infinite;
    }

    @keyframes pulseBadge {
        0%, 100% { box-shadow: 0 10px 24px rgba(255, 77, 109, 0.4); }
        50% { box-shadow: 0 10px 24px rgba(255, 77, 109, 0.4), 0 0 0 8px rgba(255, 77, 109, 0.1); }
    }

    /* Thumbnails */
    .thumbs-row {
        display: flex;
        gap: 12px;
        margin-top: 16px;
        justify-content: center;
    }

    .thumb {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 14px;
        border: 2px solid var(--line);
        cursor: pointer;
        background: #fff;
        padding: 4px;
        transition: all .3s var(--ease);
    }

    .thumb:hover {
        border-color: #ffc7b8;
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(255, 77, 109, 0.15);
    }

    .thumb.active {
        border-color: var(--brand);
        box-shadow: 0 8px 20px rgba(255, 77, 109, 0.25);
        transform: translateY(-3px);
    }

    /* ================= RIGHT: PRODUCT INFO ================= */
    .product-info {
        padding-left: 20px;
    }

    .brand-label {
        font-family: 'Outfit', sans-serif;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--brand);
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .brand-label::before {
        content: '';
        width: 20px;
        height: 2px;
        background: var(--brand);
        border-radius: 99px;
    }

    .product-title {
        font-family: 'Outfit', sans-serif;
        font-weight: 900;
        font-size: clamp(1.5rem, 2.8vw, 2.2rem);
        color: var(--ink);
        letter-spacing: -0.02em;
        line-height: 1.2;
        margin-bottom: 14px;
    }

    /* Product Label Badge */
    .product-label {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: var(--brand-soft);
        color: var(--brand);
        padding: 5px 14px;
        border-radius: 999px;
        font-family: 'Outfit', sans-serif;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        margin-bottom: 14px;
    }

    /* Rating */
    .rating-wrap {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 18px;
    }

    .stars {
        display: flex;
        gap: 3px;
        color: #fbbf24;
        font-size: 1.05rem;
    }

    .rating-text {
        font-family: 'Outfit', sans-serif;
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--muted);
    }

    /* Price Box */
    .price-box {
        background: linear-gradient(135deg, #fff5f1 0%, #ffffff 100%);
        border: 1px solid #ffe2d8;
        border-radius: 16px;
        padding: 18px 24px;
        margin-bottom: 20px;
        display: flex;
        align-items: baseline;
        gap: 14px;
        flex-wrap: wrap;
    }

    .new-price {
        font-family: 'Outfit', sans-serif;
        font-size: 2rem;
        font-weight: 900;
        color: var(--brand);
        letter-spacing: -0.02em;
        line-height: 1;
    }

    .old-price {
        font-size: 1rem;
        color: var(--muted);
        text-decoration: line-through;
    }

    .save-badge {
        background: #dcfce7;
        color: #16a34a;
        padding: 5px 12px;
        border-radius: 8px;
        font-family: 'Outfit', sans-serif;
        font-size: 0.75rem;
        font-weight: 800;
        letter-spacing: 0.03em;
    }

    /* Stock Indicator */
    .stock-indicator {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-family: 'Outfit', sans-serif;
        font-size: 0.82rem;
        font-weight: 700;
        margin-bottom: 20px;
    }

    .stock-indicator .dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #22c55e;
        box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.18);
        animation: pulseDot 2s infinite;
    }

    @keyframes pulseDot {
        0%, 100% { box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.18); }
        50% { box-shadow: 0 0 0 7px rgba(34, 197, 94, 0.08); }
    }

    .stock-indicator.in-stock { color: #16a34a; }
    .stock-indicator.out-stock { color: #dc2626; }
    .stock-indicator.out-stock .dot { background: #dc2626; box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.18); }

    /* Description */
    .desc-box {
        font-size: 0.95rem;
        line-height: 1.8;
        color: #56607a;
        margin-bottom: 24px;
        padding: 18px 22px;
        background: #fafbfc;
        border-left: 4px solid var(--brand);
        border-radius: 0 14px 14px 0;
    }

    /* Divider */
    .divider {
        border: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, var(--line) 20%, var(--line) 80%, transparent);
        margin: 24px 0;
    }

    /* ================= QUANTITY + ACTIONS ================= */
    .qty-label {
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        font-size: 0.78rem;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: var(--ink);
        margin-bottom: 10px;
        display: block;
    }

    .qty-selector {
        display: inline-flex;
        align-items: center;
        padding: 6px;
        background: #fff;
        border: 1.5px solid var(--line);
        border-radius: 999px;
        box-shadow: 0 2px 8px rgba(46, 50, 84, 0.04);
        margin-bottom: 24px;
        transition: border-color .3s;
    }

    .qty-selector:hover { border-color: #d8d0c8; }

    .qty-selector button {
        width: 42px;
        height: 42px;
        border: 0;
        background: transparent;
        border-radius: 50%;
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--ink);
        cursor: pointer;
        display: grid;
        place-items: center;
        transition: all .25s var(--ease);
    }

    .qty-selector button:hover {
        background: var(--ink);
        color: #fff;
        transform: scale(1.06);
    }

    .qty-selector input {
        width: 50px;
        border: 0;
        background: transparent;
        text-align: center;
        font-family: 'Outfit', sans-serif;
        font-size: 1.1rem;
        font-weight: 800;
        color: var(--ink);
        outline: none;
    }

    /* Action Buttons */
    .action-row {
        display: flex;
        gap: 12px;
        margin-bottom: 20px;
    }

    .btn-cart {
        flex: 2;
        background: linear-gradient(135deg, var(--brand), var(--brand-2));
        color: #fff;
        border: 0;
        padding: 16px 32px;
        border-radius: 14px;
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        font-size: 0.92rem;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        box-shadow: 0 14px 30px rgba(255, 77, 109, 0.35);
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: all .35s var(--ease);
        position: relative;
        overflow: hidden;
    }

    .btn-cart::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, transparent 30%, rgba(255, 255, 255, 0.3) 50%, transparent 70%);
        transform: translateX(-120%);
        transition: transform .7s var(--ease);
    }

    .btn-cart:hover::before {
        transform: translateX(120%);
    }

    .btn-cart:hover {
        transform: translateY(-3px);
        box-shadow: 0 20px 40px rgba(255, 77, 109, 0.42);
    }

    .btn-wishlist {
        flex: 1;
        background: #fff;
        color: var(--ink);
        border: 1.5px solid var(--line);
        padding: 16px 24px;
        border-radius: 14px;
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        font-size: 0.85rem;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all .35s var(--ease);
    }

    .btn-wishlist i { color: var(--brand); font-size: 1rem; transition: transform .3s; }

    .btn-wishlist:hover {
        border-color: var(--brand);
        background: var(--brand-soft);
        color: var(--brand);
        transform: translateY(-3px);
    }

    .btn-wishlist:hover i { transform: scale(1.2); }

    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--muted);
        font-family: 'Outfit', sans-serif;
        font-size: 0.85rem;
        font-weight: 700;
        text-decoration: none;
        margin-top: 6px;
        transition: color .3s;
    }

    .btn-back:hover { color: var(--brand); }

    /* ================= FEATURES STRIP ================= */
    .features-strip {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        margin-top: 24px;
        padding: 20px;
        background: #fff;
        border-radius: 16px;
        border: 1px solid var(--line);
    }

    .feature-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 8px;
    }

    .feature-item i {
        width: 40px;
        height: 40px;
        display: grid;
        place-items: center;
        background: var(--brand-soft);
        color: var(--brand);
        border-radius: 50%;
        font-size: 1rem;
    }

    .feature-item span {
        font-family: 'Outfit', sans-serif;
        font-size: 0.72rem;
        font-weight: 700;
        color: var(--ink);
        letter-spacing: 0.03em;
        text-transform: uppercase;
    }

    /* ================= RESPONSIVE ================= */
    @media (max-width: 991px) {
        .product-page { padding: 25px 0 40px; }
        .gallery-wrap { position: static; margin-bottom: 30px; }
        .product-info { padding-left: 0; }
        .main-img img { height: 340px; }
        .product-title { font-size: 1.4rem; }
        .new-price { font-size: 1.6rem; }
    }

    @media (max-width: 575.98px) {
        .main-img img { height: 280px; }
        .thumb { width: 65px; height: 65px; }
        .action-row { flex-direction: column; }
        .btn-cart, .btn-wishlist { width: 100%; }
        .features-strip { padding: 16px 10px; }
        .feature-item i { width: 34px; height: 34px; font-size: 0.9rem; }
        .feature-item span { font-size: 0.65rem; }
    }
</style>

<div class="container product-page">
    <div class="row g-4">

        <!-- LEFT SIDE IMAGES -->
        <div class="col-lg-6">
            <div class="gallery-wrap">
                <div class="main-img">
                    <?php if($discount > 0){ ?>
                        <span class="discount-badge">-<?= $discount ?>% OFF</span>
                    <?php } ?>
                    <img id="mainImage" src="images/<?= htmlspecialchars($data['image1']); ?>" alt="<?= htmlspecialchars($data['product_name']); ?>">
                </div>

                <div class="thumbs-row">
                    <?php if(!empty($data['image1'])){ ?>
                    <img src="images/<?= htmlspecialchars($data['image1']); ?>" class="thumb active" onclick="changeImage(this)">
                    <?php } ?>
                    <?php if(!empty($data['image2'])){ ?>
                    <img src="images/<?= htmlspecialchars($data['image2']); ?>" class="thumb" onclick="changeImage(this)">
                    <?php } ?>
                    <?php if(!empty($data['image3'])){ ?>
                    <img src="images/<?= htmlspecialchars($data['image3']); ?>" class="thumb" onclick="changeImage(this)">
                    <?php } ?>
                </div>
            </div>
        </div>

        <!-- RIGHT SIDE DETAILS -->
        <div class="col-lg-6">
            <div class="product-info">

                <!-- BRAND LABEL -->
                <div class="brand-label">SAM'S PETS STORE</div>

                <!-- PRODUCT LABEL -->
                <?php if(!empty($data['label'])){ ?>
                    <div class="product-label">
                        <i class="fa-solid fa-tag"></i> <?= htmlspecialchars($data['label']); ?>
                    </div>
                <?php } ?>

                <!-- TITLE -->
                <h1 class="product-title"><?= htmlspecialchars($data['product_name']); ?></h1>

                <!-- RATING -->
                <div class="rating-wrap">
                    <div class="stars">
                        <?php for($i=1;$i<=5;$i++){ ?>
                            <i class="fa-<?= $i <= $data['rating'] ? 'solid' : 'regular'; ?> fa-star"></i>
                        <?php } ?>
                    </div>
                    <span class="rating-text"><?= $data['rating']; ?>.0 / 5.0</span>
                </div>

                <!-- PRICE BOX -->
                <div class="price-box">
                    <span class="new-price">₹<?= number_format($data['new_price'], 0); ?></span>
                    <?php if($data['old_price'] > 0){ ?>
                        <span class="old-price">₹<?= number_format($data['old_price'], 0); ?></span>
                    <?php } ?>
                    <?php if($discount > 0){ ?>
                        <span class="save-badge">Save <?= $discount ?>%</span>
                    <?php } ?>
                </div>

                <!-- STOCK -->
                <?php if(!empty($data['stock']) && $data['stock'] > 0){ ?>
                    <div class="stock-indicator in-stock">
                        <span class="dot"></span> In Stock (<?= $data['stock']; ?> available)
                    </div>
                <?php } else { ?>
                    <div class="stock-indicator out-stock">
                        <span class="dot"></span> Out of Stock
                    </div>
                <?php } ?>

                <!-- DESCRIPTION -->
                <?php if(!empty($data['description'])){ ?>
                    <div class="desc-box">
                        <?= nl2br(htmlspecialchars($data['description'])); ?>
                    </div>
                <?php } ?>

                <hr class="divider">

                <!-- QUANTITY -->
                <label class="qty-label">Quantity</label>
                <div class="qty-selector">
                    <button type="button" onclick="changeQty(-1)">−</button>
                    <input type="text" id="qty" value="1" readonly>
                    <button type="button" onclick="changeQty(1)">+</button>
                </div>

                <!-- ACTION BUTTONS -->
                <div class="action-row">
                    <button class="btn-cart" onclick="addToCart(<?= $data['id']; ?>)">
                        <i class="fa-solid fa-bag-shopping"></i> Add to Cart
                    </button>
                    <button class="btn-wishlist" onclick="addToWishlist(<?= $data['id']; ?>, this)">
                        <i class="fa-regular fa-heart"></i> Wishlist
                    </button>
                </div>

                <a href="shop.php" class="btn-back">
                    <i class="fa-solid fa-arrow-left"></i> Back to Shop
                </a>

                <!-- FEATURES -->
                <div class="features-strip">
                    <div class="feature-item">
                        <i class="fa-solid fa-truck-fast"></i>
                        <span>Free Delivery</span>
                    </div>
                    <div class="feature-item">
                        <i class="fa-solid fa-shield-halved"></i>
                        <span>Secure Payment</span>
                    </div>
                    <div class="feature-item">
                        <i class="fa-solid fa-rotate-left"></i>
                        <span>Easy Returns</span>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

<script>
    // IMAGE SWITCHING
    function changeImage(el) {
        document.getElementById('mainImage').src = el.src;
        document.querySelectorAll('.thumb').forEach(t => t.classList.remove('active'));
        el.classList.add('active');
    }

    // QUANTITY CHANGE
    function changeQty(val) {
        let qty = document.getElementById('qty');
        let current = parseInt(qty.value) || 1;
        current += val;
        if (current < 1) current = 1;
        qty.value = current;
    }

    // ADD TO CART
    function addToCart(productId) {
        let qty = document.getElementById('qty').value;

        fetch("add_to_cart.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "id=" + productId + "&qty=" + qty
        })
        .then(res => res.text())
        .then(res => {
            if (res.trim() === "added") {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Added to Cart!',
                        text: 'Product added successfully.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    alert("Product added to cart ✅");
                }
            } else {
                alert("Please login first or try again.");
            }
        });
    }

    // ADD TO WISHLIST
    function addToWishlist(productId, btn) {
        fetch("add_to_wishlist.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "product_id=" + productId
        })
        .then(res => res.text())
        .then(res => {
            if (res.trim() === "added") {
                btn.querySelector('i').className = 'fa-solid fa-heart';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Added to Wishlist!',
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    alert("Added to wishlist ❤️");
                }
            } else if (res.trim() === "exists") {
                alert("Already in wishlist");
            } else if (res.trim() === "login") {
                window.location.href = 'login.php';
            } else {
                alert("Something went wrong");
            }
        });
    }
</script>