<?php
session_start();
include "config/db.php";
include "include/header.php";
?>

<?php
function shortProductDescription($description, $limit = 14)
{

    $description = trim(strip_tags((string) $description));

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

<link rel="stylesheet" href="style.css">

<?php
// Fetch single hero banner for shop page
$heroBanner = mysqli_query($conn, "
    SELECT * FROM banners 
    WHERE type='hero' 
    AND status=1 
    AND (page='shop' OR page='both')
    ORDER BY 
        CASE 
            WHEN page='shop' THEN 1 
            WHEN page='both' THEN 2 
        END,
        id DESC
    LIMIT 1
");

$hero = mysqli_fetch_assoc($heroBanner);
?>

<?php if ($hero) { ?>
<!-- ══════════ SINGLE STATIC HERO BANNER (No carousel) ══════════ -->
<section class="shop-hero">
    <div class="shop-hero-inner">
        <img src="images/<?= htmlspecialchars($hero['image']); ?>" class="shop-hero-bg" alt="<?= htmlspecialchars($hero['heading']); ?>">
        <div class="shop-hero-overlay"></div>

        <div class="shop-hero-content">
            <?php if (!empty($hero['subheading'])) { ?>
                <p class="shop-hero-tag"><?= htmlspecialchars($hero['subheading']); ?></p>
            <?php } ?>

            <h1 class="shop-hero-title"><?= htmlspecialchars($hero['heading']); ?></h1>

            <?php if (!empty($hero['button_text'])) { ?>
                <a href="<?= htmlspecialchars($hero['button_link']); ?>" 
                   class="shop-hero-btn"
                   style="background:<?= !empty($hero['button_color']) ? htmlspecialchars($hero['button_color']) : '#ff4d6d'; ?>;">
                    <?= htmlspecialchars($hero['button_text']); ?>
                </a>
            <?php } ?>
        </div>
    </div>
</section>
<?php } ?>


<!-- ══════════ ALL PRODUCTS SECTION ══════════ -->
<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="feature-title">All Products</h2>
        <span class="products-count">
            <?php
            $countResult = mysqli_query($conn, "SELECT COUNT(*) as total FROM products WHERE status = 1");
            $totalCount = mysqli_fetch_assoc($countResult)['total'] ?? 0;
            echo $totalCount . ' Products';
            ?>
        </span>
    </div>

    <div class="row g-4">
        <?php
        $productsQuery = mysqli_query($conn, "
            SELECT * 
            FROM products
            WHERE status = 1
            ORDER BY sort_order ASC, id DESC
        ");

        if (mysqli_num_rows($productsQuery) > 0) {
            while ($row = mysqli_fetch_assoc($productsQuery)) {
                $new_price = $row['new_price'] ?? 0;
                $old_price = $row['old_price'] ?? 0;
                $discount = 0;
                
                if ($old_price > 0 && $new_price > 0) {
                    $discount = round((($old_price - $new_price) / $old_price) * 100);
                }
        ?>
        <div class="col-lg-3 col-md-6 col-6 product-item">
            <div class="card product-card border-0 h-100">
                <div class="product-img">
                    <?php if ($discount > 0) { ?>
                        <span class="discount">-<?= $discount ?>%</span>
                    <?php } ?>
                    
                    <a href="product-details.php?id=<?= $row['id']; ?>">
                        <img src="images/<?= htmlspecialchars($row['image1']); ?>" class="img-fluid img1" alt="<?= htmlspecialchars($row['product_name']); ?>">
                        <img src="images/<?= htmlspecialchars(!empty($row['image2']) ? $row['image2'] : $row['image1']); ?>" class="img-fluid img2" alt="<?= htmlspecialchars($row['product_name']); ?>">
                    </a>
                    
                    <div class="hover-icons">
                        <a href="javascript:void(0)" class="wishlist-btn" data-id="<?= $row['id']; ?>" title="Add to Wishlist">
                            <i class="fa fa-heart"></i>
                        </a>
                        <a href="product-details.php?id=<?= $row['id']; ?>" title="Quick View">
                            <i class="fa fa-eye"></i>
                        </a>
                    </div>
                </div>
                
                <div class="card-body position-relative">
                    <p class="text-muted mb-1">SAM'S Pets</p>
                    
                    <h6>
                        <a href="product-details.php?id=<?= $row['id']; ?>">
                            <?= htmlspecialchars($row['product_name']); ?>
                        </a>
                    </h6>

                    <p class="product-description">
                                    <?= shortProductDescription($row['description'] ?? '', 10); ?>
                                </p>
                    
                    <div class="d-flex align-items-center flex-wrap mt-2">
                        <span class="text-danger fw-bold me-2">
                            ₹<?= number_format($new_price, 0); ?>
                        </span>
                        
                        <?php if ($old_price > 0) { ?>
                            <small class="text-muted text-decoration-line-through">
                                ₹<?= number_format($old_price, 0); ?>
                            </small>
                        <?php } ?>
                    </div>
                    
                    <button class="cart-btn" title="Add to Cart" onclick="event.preventDefault(); event.stopPropagation();">
                        <i class="fa fa-shopping-cart"></i>
                    </button>
                </div>
            </div>
        </div>
        <?php } } else { ?>
            <div class="col-12 text-center py-5">
                <h4 class="text-muted">No products found.</h4>
                <p>Please check back later.</p>
            </div>
        <?php } ?>
    </div>
</div>


<!-- ══════════ SHOP HERO BANNER CSS ══════════ -->
<style>
    /* ---- SINGLE HERO BANNER (matches homepage aesthetic) ---- */
    .shop-hero {
        max-width: 1480px;
        margin: 22px auto 0;
        padding: 10px clamp(14px, 6vw, 96px);
    }

    .shop-hero-inner {
        position: relative;
        height: clamp(300px, 45vw, 520px);
        border-radius: 26px;
        overflow: hidden;
        background: #0b1220;
        box-shadow: 0 30px 70px rgba(15, 23, 42, 0.22);
    }

    .shop-hero-bg {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transform: scale(1.02);
    }

    /* dark gradient overlay for text readability */
    .shop-hero-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(100deg,
            rgba(6, 10, 20, 0.82) 0%,
            rgba(6, 10, 20, 0.55) 34%,
            rgba(6, 10, 20, 0.15) 62%,
            rgba(6, 10, 20, 0) 100%);
        z-index: 1;
    }

    .shop-hero-content {
        position: absolute;
        z-index: 2;
        top: 50%;
        left: clamp(22px, 5vw, 72px);
        transform: translateY(-50%);
        max-width: min(580px, 62%);
        color: #fff;
    }

    .shop-hero-tag {
        display: inline-block;
        font-family: 'Outfit', sans-serif;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.15em;
        text-transform: uppercase;
        color: #ff4d6d;
        background: rgba(255, 77, 109, 0.15);
        padding: 6px 14px;
        border-radius: 999px;
        margin-bottom: 14px;
        border: 1px solid rgba(255, 77, 109, 0.35);
    }

    .shop-hero-title {
        font-family: 'Outfit', sans-serif;
        font-size: clamp(1.5rem, 3.6vw, 3.4rem);
        font-weight: 900;
        line-height: 1.06;
        letter-spacing: -0.035em;
        color: #fff;
        margin: 0 0 22px;
        text-shadow: 0 8px 30px rgba(0, 0, 0, 0.35);
    }

    .shop-hero-btn {
        display: inline-block;
        padding: 13px 30px;
        border-radius: 999px;
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        font-size: 0.88rem;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        color: #fff;
        text-decoration: none;
        box-shadow: 0 14px 32px rgba(0, 0, 0, 0.28);
        transition: transform 0.35s ease, box-shadow 0.35s ease, filter 0.3s ease;
    }

    .shop-hero-btn:hover {
        transform: translateY(-3px);
        filter: brightness(1.08);
        box-shadow: 0 20px 42px rgba(0, 0, 0, 0.35);
        color: #fff;
    }

    /* ---- Product count pill ---- */
    .products-count {
        display: inline-flex;
        align-items: center;
        padding: 8px 18px;
        background: #fff2f5;
        color: #ff4d6d;
        border: 1px solid rgba(255, 77, 109, 0.2);
        border-radius: 999px;
        font-family: 'Outfit', sans-serif;
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    /* ---- Responsive ---- */
    @media (max-width: 991.98px) {
        .shop-hero-inner {
            height: clamp(260px, 55vw, 400px);
        }
        .shop-hero-content {
            max-width: 82%;
        }
    }

    @media (max-width: 767.98px) {
        .shop-hero {
            padding: 8px 14px;
        }
        .shop-hero-inner {
            height: 280px;
            border-radius: 20px;
        }
        .shop-hero-content {
            left: 20px;
            max-width: 88%;
        }
        .shop-hero-btn {
            padding: 10px 22px;
            font-size: 0.76rem;
        }
        .shop-hero-title {
            margin-bottom: 16px;
        }
        .products-count {
            font-size: 0.68rem;
            padding: 6px 12px;
        }
    }
</style>

<?php include "include/footer.php"; ?>