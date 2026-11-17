<?php 
require_once "config/db.php";
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
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

<style>
    /* ==========================================================
       SEARCH PAGE — PREMIUM STYLES (Navy + Pink)
       ========================================================== */
    :root {
        --ink: #2e3254;
        --ink-2: #252a4a;
        --ink-3: #1f2340;
        --brand: #ff4d6d;
        --brand-2: #e63950;
        --brand-soft: #fff2f5;
        --brand-glow: rgba(255, 77, 109, .35);
        --line: #efe6ec;
        --muted: #8a8fa3;
        --ease: cubic-bezier(.2, .8, .2, 1);
    }

    /* --- HERO SEARCH AREA --- */
    .search-hero {
        background: linear-gradient(180deg, var(--ink-3) 0%, var(--ink-2) 100%);
        padding: 60px 0 80px;
        border-bottom: 1px solid rgba(255, 255, 255, .05);
        position: relative;
        overflow: hidden;
    }

    /* Subtle warm glow in background */
    .search-hero::before {
        content: '';
        position: absolute;
        top: -100px;
        right: -50px;
        width: 400px;
        height: 400px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(255, 77, 109, .15) 0%, transparent 60%);
        pointer-events: none;
    }

    .search-title {
        font-family: 'Outfit', sans-serif;
        color: #fff;
        font-weight: 800;
        font-size: clamp(1.6rem, 3vw, 2.2rem);
        letter-spacing: -.02em;
        text-align: center;
        margin-bottom: 30px;
    }

    .search-title span {
        color: var(--brand);
    }

    /* Search Box Design */
    .search-box-wrap {
        max-width: 680px;
        margin: 0 auto;
        position: relative;
        z-index: 2;
    }

    .search-box {
        display: flex;
        align-items: center;
        background: #fff;
        border-radius: 999px;
        padding: 8px 8px 8px 24px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, .25), 0 0 0 1px rgba(255, 255, 255, .1);
        transition: box-shadow .3s var(--ease), transform .3s var(--ease);
    }

    .search-box:focus-within {
        transform: translateY(-2px);
        box-shadow: 0 24px 50px rgba(0, 0, 0, .3), 0 0 0 3px rgba(255, 77, 109, .3);
    }

    .search-box i {
        color: var(--muted);
        font-size: 1.1rem;
        margin-right: 12px;
    }

    .search-box input {
        border: none !important;
        background: transparent !important;
        box-shadow: none !important;
        font-family: 'Manrope', sans-serif;
        font-size: 1rem;
        color: var(--ink);
        padding: 10px 0;
        flex: 1;
    }

    .search-box input::placeholder {
        color: #a0a5b8;
        font-weight: 500;
    }

    .search-box button {
        background: linear-gradient(135deg, var(--brand), var(--brand-2));
        color: #fff;
        border: none;
        border-radius: 999px;
        padding: 14px 32px;
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        font-size: .85rem;
        letter-spacing: .05em;
        text-transform: uppercase;
        cursor: pointer;
        transition: all .3s var(--ease);
        box-shadow: 0 8px 20px rgba(255, 77, 109, .3);
    }

    .search-box button:hover {
        transform: scale(1.02);
        filter: brightness(1.05);
        box-shadow: 0 12px 25px rgba(255, 77, 109, .4);
    }

    /* --- PRODUCT GRID WRAPPER --- */
    .products-wrapper {
        margin-top: -40px; /* Pull cards up over the hero edge */
        position: relative;
        z-index: 10;
        padding-bottom: 5rem;
    }

    /* Empty State Design */
    .empty-state {
        text-align: center;
        padding: 80px 20px;
        background: #fff;
        border-radius: 24px;
        border: 1px dashed #d4d4d4;
        max-width: 600px;
        margin: 0 auto;
    }
    
    .empty-state i {
        font-size: 4rem;
        color: var(--brand);
        margin-bottom: 20px;
        opacity: 0.8;
    }
    
    .empty-state h4 {
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        color: var(--ink);
        font-size: 1.4rem;
        margin-bottom: 10px;
    }
    
    .empty-state p {
        color: var(--muted);
        font-size: 0.95rem;
        margin-bottom: 0;
    }
</style>

<!-- SEARCH HERO SECTION -->
<div class="search-hero">
    <div class="container">
        <h2 class="search-title">Find Your Pet's <span>Favorite</span></h2>
        
        <div class="search-box-wrap">
            <form method="GET">
                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input 
                        type="text" 
                        name="search" 
                        placeholder="Search for food, toys, accessories..."
                        value="<?php if(isset($_GET['search'])) echo htmlspecialchars($_GET['search']); ?>"
                        autocomplete="off"
                    >
                    <button type="submit">Search</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- PRODUCTS SECTION -->
<div class="container products-wrapper">
    <div class="row g-4 justify-content-center">
    
    <?php 
    if(isset($_GET['search']) && $_GET['search'] != ""){
    
        $search = mysqli_real_escape_string($conn,$_GET['search']);
    
        $query = "
        SELECT p.*, 
        GROUP_CONCAT(pc.category_id) as cat_ids
        FROM products p
        LEFT JOIN product_categories pc ON p.id = pc.product_id
        WHERE p.product_name LIKE '%$search%' 
        OR p.description LIKE '%$search%' 
        GROUP BY p.id
        ORDER BY p.id DESC
        ";
    
        $result = mysqli_query($conn,$query);
    
        if(mysqli_num_rows($result) > 0){
            while($p = mysqli_fetch_assoc($result)){
    
                $new_price = !empty($p['new_price']) ? $p['new_price'] : 0;
                $old_price = !empty($p['old_price']) ? $p['old_price'] : 0;
    
                $discount = 0;
                if($old_price > 0 && $new_price > 0){
                    $discount = round((($old_price - $new_price) / $old_price) * 100);
                }
    ?>
    
            <!-- PRODUCT CARD -->
            <div class="col-lg-3 col-md-6 col-6 product-item">
                <div class="card product-card border-0 h-100">
    
                    <div class="product-img">
                        <?php if($discount > 0){ ?>
                        <span class="discount">-<?= $discount ?>%</span>
                        <?php } ?>
    
                        <a href="product-details.php?id=<?= $p['id']; ?>">
                            <img src="images/<?= $p['image1']; ?>" class="img-fluid img1" alt="<?= $p['product_name']; ?>">
                            <img src="images/<?= !empty($p['image2']) ? $p['image2'] : $p['image1']; ?>" class="img-fluid img2" alt="<?= $p['product_name']; ?>">
                        </a>
    
                        <!-- SIRF HEART ICON (Clean UI) -->
                        <div class="hover-icons">
                            <a href="javascript:void(0)" class="wishlist-btn" data-id="<?= $p['id']; ?>" title="Add to Wishlist">
                                <i class="fa fa-heart"></i>
                            </a>
                        </div> 
                    </div>
    
                    <div class="card-body position-relative">
                        <p class="text-muted mb-1">SAM'S Pets</p>
                        
                        <h6>
                            <a href="product-details.php?id=<?= $p['id']; ?>">
                                <?= htmlspecialchars($p['product_name']); ?>
                            </a>
                        </h6>

                        <p class="product-description">
                                    <?= shortProductDescription($row['description'] ?? '', 10); ?>
                                </p>
                        
                        <div class="d-flex align-items-center flex-wrap mt-2">
                            <span class="text-danger fw-bold me-2">
                                ₹<?= number_format($new_price,2); ?>
                            </span>
                            <?php if($old_price > 0){ ?>
                            <small class="text-muted text-decoration-line-through">
                                ₹<?= number_format($old_price,2); ?>
                            </small>
                            <?php } ?>
                        </div>
                        
                        <button class="cart-btn" title="Add to Cart">
                            <i class="fa fa-shopping-cart"></i>
                        </button>
                    </div>
    
                </div>
            </div>
    
    <?php 
            }
        } else {
            // No Results Found
            echo '<div class="col-12">
                <div class="empty-state">
                    <i class="fa-solid fa-box-open"></i>
                    <h4>No products found</h4>
                    <p>We couldn\'t find any results for "<b>'.htmlspecialchars($_GET['search']).'</b>". Try checking your spelling or use more general terms.</p>
                </div>
            </div>';
        }
    
    } else {
        // Initial State (Nothing searched)
        echo '<div class="col-12">
            <div class="empty-state">
                <i class="fa-solid fa-magnifying-glass"></i>
                <h4>Search for something!</h4>
                <p>Type a product name in the search bar above to find what you need.</p>
            </div>
        </div>';
    }
    ?>
    
    </div>
</div>

<?php include "include/footer.php"; ?>