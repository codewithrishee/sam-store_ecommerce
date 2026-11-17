<?php
include "config/db.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function e($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/* ============ PRODUCT ID ============ */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    include "include/header.php";
    echo "<div class='container py-5 text-center'><h3>Product Not Found</h3></div>";
    include "include/footer.php";
    exit;
}
$id = (int)$_GET['id'];

/* ============ FETCH PRODUCT ============ */
$stmt = $conn->prepare("
    SELECT p.*, GROUP_CONCAT(DISTINCT c.name SEPARATOR ', ') AS category_names
    FROM products p
    LEFT JOIN product_categories pc ON p.id = pc.product_id
    LEFT JOIN categories c ON pc.category_id = c.id
    WHERE p.id = ?
    GROUP BY p.id LIMIT 1
");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    include "include/header.php";
    echo "<div class='container py-5 text-center'><h3>Product Not Found</h3></div>";
    include "include/footer.php";
    exit;
}

/* ============ REVIEW TOKEN ============ */
if (empty($_SESSION['product_review_token'])) {
    $_SESSION['product_review_token'] = bin2hex(random_bytes(32));
}
$reviewMessage = '';
$reviewMessageType = 'success';

/* ============ REVIEW SUBMIT ============ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_product_review'])) {
    $token = $_POST['review_token'] ?? '';
    if (!hash_equals($_SESSION['product_review_token'], $token)) {
        $reviewMessage = 'Your session expired. Please refresh and try again.';
        $reviewMessageType = 'error';
    } else {
        $customerName  = trim($_POST['customer_name'] ?? '');
        $customerEmail = trim($_POST['customer_email'] ?? '');
        $reviewTitle   = trim($_POST['review_title'] ?? '');
        $reviewText    = trim($_POST['review_message'] ?? '');
        $reviewRating  = (int)($_POST['rating'] ?? 0);

        if ($customerName === '' || !filter_var($customerEmail, FILTER_VALIDATE_EMAIL) ||
            $reviewText === '' || $reviewRating < 1 || $reviewRating > 5) {
            $reviewMessage = 'Please enter your name, a valid email, review and rating.';
            $reviewMessageType = 'error';
        } else {
            $rs = $conn->prepare("
                INSERT INTO product_reviews
                (product_id, customer_name, customer_email, rating, review_title, review_message, status)
                VALUES (?, ?, ?, ?, ?, ?, 'pending')
            ");
            if ($rs) {
                $rs->bind_param("ississ", $id, $customerName, $customerEmail, $reviewRating, $reviewTitle, $reviewText);
                if ($rs->execute()) {
                    $reviewMessage = 'Thank you! Your review will appear after approval.';
                    $_SESSION['product_review_token'] = bin2hex(random_bytes(32));
                } else {
                    $reviewMessage = 'Could not submit your review. Please try again.';
                    $reviewMessageType = 'error';
                }
                $rs->close();
            } else {
                $reviewMessage = 'Review form temporarily unavailable.';
                $reviewMessageType = 'error';
            }
        }
    }
}

/* ============ REVIEW STATS ============ */
$reviewStatsStmt = $conn->prepare("
    SELECT COUNT(*) AS total, COALESCE(AVG(rating),0) AS average
    FROM product_reviews WHERE product_id = ? AND status='approved'
");
$reviewStatsStmt->bind_param("i", $id);
$reviewStatsStmt->execute();
$reviewStats = $reviewStatsStmt->get_result()->fetch_assoc() ?: ['total'=>0,'average'=>0];
$reviewStatsStmt->close();

$approvedReviews = [];
$rs = $conn->prepare("
    SELECT customer_name, rating, review_title, review_message, created_at
    FROM product_reviews WHERE product_id = ? AND status='approved'
    ORDER BY created_at DESC, id DESC LIMIT 20
");
$rs->bind_param("i", $id);
$rs->execute();
$res = $rs->get_result();
while ($row = $res->fetch_assoc()) $approvedReviews[] = $row;
$rs->close();

/* ============ IMAGES ============ */
$productImages = [];
$stmt = $conn->prepare("SELECT image_name FROM product_images WHERE product_id = ? ORDER BY id ASC");
$stmt->bind_param("i", $id);
$stmt->execute();
$imgRes = $stmt->get_result();
while ($row = $imgRes->fetch_assoc()) {
    if (!empty($row['image_name']) && !in_array($row['image_name'], $productImages, true)) {
        $productImages[] = $row['image_name'];
    }
}
$stmt->close();

foreach (['image1','image2','image3'] as $col) {
    if (!empty($product[$col]) && !in_array($product[$col], $productImages, true)) {
        $productImages[] = $product[$col];
    }
}
if (empty($productImages)) $productImages[] = 'no-image.png';

/* ============ CATEGORY ID ============ */
$stmt = $conn->prepare("SELECT category_id FROM product_categories WHERE product_id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$catRow = $stmt->get_result()->fetch_assoc();
$stmt->close();
$categoryId = (int)($catRow['category_id'] ?? $product['category_id'] ?? 0);

/* ============ RELATED PRODUCTS ============ */
$brand = trim($product['brand'] ?? '');
$relatedProducts = [];
$stmt = $conn->prepare("
    SELECT p.*,
        EXISTS(SELECT 1 FROM product_categories rpc WHERE rpc.product_id=p.id AND rpc.category_id=?) AS same_category
    FROM products p
    WHERE p.id != ? AND p.status = 1
      AND (
        (? > 0 AND EXISTS(SELECT 1 FROM product_categories rpc WHERE rpc.product_id=p.id AND rpc.category_id=?))
        OR (? != '' AND p.brand = ?)
      )
    ORDER BY same_category DESC, (p.brand = ?) DESC, p.id DESC
    LIMIT 8
");
$stmt->bind_param("iiissss", $categoryId, $id, $categoryId, $categoryId, $brand, $brand, $brand);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $relatedProducts[] = $row;
$stmt->close();

/* ============ VARIANTS ============ */
$colorOptions = [];
$sizeOptions  = [];
if (!empty($product['color'])) {
    $colorOptions = array_values(array_filter(array_map('trim', preg_split('/[,|]+/', (string)$product['color']))));
}
if (!empty($product['size'])) {
    $sizeOptions = array_values(array_filter(array_map('trim', preg_split('/[,|]+/', (string)$product['size']))));
}

/* Map color names -> hex for swatches */
function colorToHex($name) {
    $map = [
        'black'=>'#0f172a','white'=>'#ffffff','red'=>'#ef4444','blue'=>'#3b82f6',
        'navy'=>'#1e3a8a','green'=>'#22c55e','yellow'=>'#eab308','orange'=>'#f97316',
        'pink'=>'#ec4899','purple'=>'#a855f7','grey'=>'#94a3b8','gray'=>'#94a3b8',
        'brown'=>'#92400e','beige'=>'#f5f5dc','cream'=>'#fbf7f2','maroon'=>'#7f1d1d',
        'teal'=>'#0ea5a4','gold'=>'#d4af37','silver'=>'#c0c0c0','multi'=>'linear-gradient(135deg,#ff5a3c,#0ea5a4)',
    ];
    $k = strtolower(trim($name));
    return $map[$k] ?? '#efe8e1';
}

$stock = (int)($product['stock'] ?? 0);
$mainProductImage = $productImages[0];
$discountPct = 0;
if (!empty($product['old_price']) && $product['old_price'] > $product['new_price']) {
    $discountPct = round((($product['old_price'] - $product['new_price']) / $product['old_price']) * 100);
}

include "include/header.php";
?>

<div class="pdp-wrap">

  <!-- ============ BREADCRUMB ============ -->
  <nav class="pdp-breadcrumb container" aria-label="breadcrumb">
    <a href="index.php">Home</a><span>/</span>
    <a href="shop.php">Shop</a>
    <?php if (!empty($product['category_names'])): ?>
      <span>/</span><a href="shop.php?category=<?= (int)$categoryId; ?>"><?= e(explode(',', $product['category_names'])[0]); ?></a>
    <?php endif; ?>
    <span>/</span><span class="current"><?= e($product['product_name']); ?></span>
  </nav>

  <div class="container pdp-main">

    <!-- ============ LEFT: GALLERY ============ -->
    <div class="pdp-gallery">
      <div class="pdp-main-img" id="zoomBox">
        <img id="mainImage" src="images/<?= e($mainProductImage); ?>" alt="<?= e($product['product_name']); ?>">
        <button type="button" class="pdp-nav prev" onclick="stepImage(-1)" aria-label="Previous"><i class="bi bi-chevron-left"></i></button>
        <button type="button" class="pdp-nav next" onclick="stepImage(1)" aria-label="Next"><i class="bi bi-chevron-right"></i></button>
        <span class="pdp-zoom-tag"><i class="bi bi-zoom-in"></i> Hover to zoom</span>
      </div>
      <?php if (count($productImages) > 1): ?>
      <div class="pdp-thumbs">
        <?php foreach ($productImages as $i => $img): ?>
          <button type="button" class="pdp-thumb <?= $i===0?'active':'' ?>" data-src="images/<?= e($img); ?>" onclick="changeImage(this)">
            <img src="images/<?= e($img); ?>" alt="" loading="lazy">
          </button>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <!-- ============ RIGHT: INFO ============ -->
    <div class="pdp-info">

      <?php if (!empty($product['label'])): ?>
        <span class="pdp-label"><?= e($product['label']); ?></span>
      <?php endif; ?>

      <?php if (!empty($product['category_names'])): ?>
        <div class="pdp-category"><?= e($product['category_names']); ?></div>
      <?php endif; ?>

      <h1 class="pdp-title"><?= e($product['product_name']); ?></h1>

      <?php if (!empty($product['brand'])): ?>
        <div class="pdp-brand">by <?= e($product['brand']); ?></div>
      <?php endif; ?>

      <div class="pdp-rating-row">
        <span class="pdp-stars">
          <?php $r = (int)round($reviewStats['average']); for($i=1;$i<=5;$i++) echo $i<=$r?'★':'☆'; ?>
        </span>
        <span class="pdp-rating-count">
          <?= number_format((float)$reviewStats['average'],1); ?> · <?= (int)$reviewStats['total']; ?> review<?= (int)$reviewStats['total']===1?'':'s'; ?>
        </span>
      </div>

      <div class="pdp-price-box">
        <span class="pdp-price-new">₹<?= number_format((float)$product['new_price'],2); ?></span>
        <?php if ($discountPct > 0): ?>
          <span class="pdp-price-old">₹<?= number_format((float)$product['old_price'],2); ?></span>
          <span class="pdp-discount"><?= $discountPct; ?>% OFF</span>
        <?php endif; ?>
      </div>

      <?php if (!empty($product['short_description'])): ?>
        <p class="pdp-short-desc"><?= nl2br(e($product['short_description'])); ?></p>
      <?php endif; ?>

      <div class="pdp-stock <?= $stock>0?'in':'out'; ?>">
        <i class="bi <?= $stock>0?'bi-check-circle-fill':'bi-x-circle-fill'; ?>"></i>
        <?= $stock>0 ? "In Stock" : "Out of Stock"; ?>
        <?php if ($stock>0): ?><span class="pdp-stock-count">(<?= $stock; ?> available)</span><?php endif; ?>
      </div>

      <?php if (!empty($colorOptions)): ?>
      <div class="pdp-variant">
        <label>Color: <strong id="colorLabel">Choose</strong></label>
        <div class="pdp-colors" id="colorSwatches">
          <?php foreach ($colorOptions as $c): ?>
            <button type="button" class="pdp-swatch" data-color="<?= e($c); ?>"
                    style="--sw:<?= colorToHex($c); ?>" title="<?= e($c); ?>"
                    onclick="selectColor(this)"></button>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <?php if (!empty($sizeOptions)): ?>
      <div class="pdp-variant">
        <label>Size: <strong id="sizeLabel">Choose</strong></label>
        <div class="pdp-sizes" id="sizeButtons">
          <?php foreach ($sizeOptions as $s): ?>
            <button type="button" class="pdp-size" data-size="<?= e($s); ?>" onclick="selectSize(this)"><?= e($s); ?></button>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <div class="pdp-qty-row">
        <label>Quantity</label>
        <div class="pdp-qty">
          <button type="button" onclick="changeQty(-1)" aria-label="Decrease">−</button>
          <input type="number" id="qty" value="1" min="1" max="<?= max(1,$stock); ?>">
          <button type="button" onclick="changeQty(1)" aria-label="Increase">+</button>
        </div>
      </div>

      <div class="pdp-actions">
        <button type="button" class="pdp-btn pdp-btn-cart add-to-cart" data-id="<?= (int)$product['id']; ?>" <?= $stock<=0?'disabled':''; ?>>
          <i class="bi bi-bag-plus"></i> Add to Cart
        </button>
        <button type="button" class="pdp-btn pdp-btn-buy buy-now" data-id="<?= (int)$product['id']; ?>" <?= $stock<=0?'disabled':''; ?>>
          <i class="bi bi-lightning-charge-fill"></i> Buy Now
        </button>
      </div>

      <button type="button" class="pdp-wishlist wishlist-btn" data-id="<?= (int)$product['id']; ?>">
        <i class="bi bi-heart"></i> Add to Wishlist
      </button>

      <?php if ($stock<=0): ?>
        <p class="pdp-oos-msg">This product is currently unavailable.</p>
      <?php endif; ?>

      <!-- ============ DELIVERY ============ -->
      <div class="pdp-delivery">
        <div class="pdp-del-item">
          <i class="bi bi-truck"></i>
          <div><strong>Free Shipping</strong><small>On orders above ₹499</small></div>
        </div>
        <div class="pdp-del-item">
          <i class="bi bi-arrow-counterclockwise"></i>
          <div><strong>Easy Returns</strong><small>7-day return policy</small></div>
        </div>
        <div class="pdp-del-item">
          <i class="bi bi-shield-check"></i>
          <div><strong>Secure Payment</strong><small>100% protected checkout</small></div>
        </div>
      </div>

    </div>
  </div>

  <!-- ============ DESCRIPTION ============ -->
  <section class="pdp-section container">
    <div class="pdp-section-head">
      <span>PRODUCT DETAILS</span>
      <h2>About This Product</h2>
    </div>
    <div class="pdp-desc-card">
      <?php if (!empty($product['description'])): ?>
        <div class="pdp-desc"><?= nl2br(e($product['description'])); ?></div>
      <?php else: ?>
        <p class="text-muted">No detailed description available.</p>
      <?php endif; ?>
    </div>

    <!-- ============ SPECS ============ -->
    <div class="pdp-specs">
      <h3>Additional Information</h3>
      <div class="pdp-spec-grid">
        <?php if (!empty($product['sku'])): ?><div><span>SKU</span><strong><?= e($product['sku']); ?></strong></div><?php endif; ?>
        <?php if (!empty($product['brand'])): ?><div><span>Brand</span><strong><?= e($product['brand']); ?></strong></div><?php endif; ?>
        <?php if (!empty($product['category_names'])): ?><div><span>Category</span><strong><?= e($product['category_names']); ?></strong></div><?php endif; ?>
        <?php if (!empty($product['material'])): ?><div><span>Material</span><strong><?= e($product['material']); ?></strong></div><?php endif; ?>
        <?php if (!empty($colorOptions)): ?><div><span>Colors</span><strong><?= e(implode(', ', $colorOptions)); ?></strong></div><?php endif; ?>
        <?php if (!empty($sizeOptions)): ?><div><span>Sizes</span><strong><?= e(implode(', ', $sizeOptions)); ?></strong></div><?php endif; ?>
      </div>
    </div>

    <?php if (!empty($product['tags'])): ?>
      <div class="pdp-tags">
        <strong>Tags:</strong>
        <?php foreach (explode(',', $product['tags']) as $tag): $tag=trim($tag); if($tag==='')continue; ?>
          <span><?= e($tag); ?></span>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

  <!-- ============ REVIEWS ============ -->
  <section class="pdp-reviews">
    <div class="container">
      <div class="pdp-section-head center">
        <span>CUSTOMER FEEDBACK</span>
        <h2>Reviews & Ratings</h2>
      </div>

      <div class="pdp-reviews-layout">
        <div class="pdp-reviews-list">
          <?php if ($reviewMessage): ?>
            <div class="pdp-alert <?= $reviewMessageType==='error'?'err':'ok'; ?>"><?= e($reviewMessage); ?></div>
          <?php endif; ?>

          <?php if ($approvedReviews): foreach ($approvedReviews as $rv): ?>
            <article class="pdp-review-card">
              <div class="pdp-review-top">
                <div class="pdp-avatar"><?= e(strtoupper(substr($rv['customer_name'],0,1))); ?></div>
                <div><strong><?= e($rv['customer_name']); ?></strong><small><?= e(date('d M Y', strtotime($rv['created_at']))); ?></small></div>
                <div class="pdp-review-stars"><?php for($s=1;$s<=5;$s++) echo $s<=(int)$rv['rating']?'★':'☆'; ?></div>
              </div>
              <?php if (!empty($rv['review_title'])): ?><h4><?= e($rv['review_title']); ?></h4><?php endif; ?>
              <p><?= nl2br(e($rv['review_message'])); ?></p>
            </article>
          <?php endforeach; else: ?>
            <div class="pdp-no-reviews">
              <i class="bi bi-chat-square-heart"></i>
              <h3>Be the first to review</h3>
              <p>No approved reviews yet. Share your experience!</p>
            </div>
          <?php endif; ?>
        </div>

        <aside class="pdp-review-form">
          <h3>Write a Review</h3>
          <p>Your review appears after admin approval.</p>
          <form method="POST">
            <input type="hidden" name="review_token" value="<?= e($_SESSION['product_review_token']); ?>">
            <div class="pdp-form-group">
              <label>Your Rating *</label>
              <div class="pdp-rating-picker">
                <?php for($s=5;$s>=1;$s--): ?>
                  <input type="radio" id="rs-<?=$s?>" name="rating" value="<?=$s?>" required>
                  <label for="rs-<?=$s?>">★</label>
                <?php endfor; ?>
              </div>
            </div>
            <div class="pdp-form-group"><label>Full Name *</label><input type="text" name="customer_name" required maxlength="150"></div>
            <div class="pdp-form-group"><label>Email *</label><input type="email" name="customer_email" required maxlength="190"></div>
            <div class="pdp-form-group"><label>Review Title</label><input type="text" name="review_title" maxlength="255"></div>
            <div class="pdp-form-group"><label>Your Review *</label><textarea name="review_message" rows="5" required></textarea></div>
            <button type="submit" name="submit_product_review" value="1" class="pdp-btn pdp-btn-cart">Submit Review <i class="bi bi-arrow-right"></i></button>
          </form>
        </aside>
      </div>
    </div>
  </section>

  <!-- ============ RELATED ============ -->
  <?php if (!empty($relatedProducts)): ?>
  <section class="pdp-related">
    <div class="container">
      <div class="pdp-section-head center">
        <span>YOU MAY ALSO LIKE</span>
        <h2>Related Products</h2>
      </div>
      <div class="pdp-related-grid">
        <?php foreach ($relatedProducts as $rp): ?>
          <a href="product-details.php?id=<?= (int)$rp['id']; ?>" class="pdp-rel-card">
            <div class="pdp-rel-img">
              <img src="images/<?= e($rp['image1'] ?: 'no-image.png'); ?>" alt="<?= e($rp['product_name']); ?>" loading="lazy">
              <?php if (!empty($rp['label'])): ?><span class="pdp-rel-label"><?= e($rp['label']); ?></span><?php endif; ?>
            </div>
            <div class="pdp-rel-body">
              <?php if (!empty($rp['brand'])): ?><div class="pdp-rel-brand"><?= e($rp['brand']); ?></div><?php endif; ?>
              <h4><?= e($rp['product_name']); ?></h4>
              <div class="pdp-rel-price">
                <strong>₹<?= number_format((float)$rp['new_price'],2); ?></strong>
                <?php if (!empty($rp['old_price']) && $rp['old_price']>$rp['new_price']): ?><del>₹<?= number_format((float)$rp['old_price'],2); ?></del><?php endif; ?>
              </div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

</div>

<!-- ============ JAVASCRIPT ============ -->
<script>
const MAX_STOCK = <?= (int)$stock; ?>;
let currentImgIndex = 0;
const images = <?= json_encode(array_map(fn($i)=>'images/'.$i, $productImages)); ?>;
let selectedColor = '';
let selectedSize  = '';

/* -------- Gallery -------- */
function changeImage(el){
  const src = el.dataset.src;
  currentImgIndex = images.indexOf(src);
  swapMain(src);
  document.querySelectorAll('.pdp-thumb').forEach(t=>t.classList.remove('active'));
  el.classList.add('active');
}
function stepImage(dir){
  currentImgIndex = (currentImgIndex + dir + images.length) % images.length;
  swapMain(images[currentImgIndex]);
  document.querySelectorAll('.pdp-thumb').forEach((t,i)=>{
    t.classList.toggle('active', i===currentImgIndex);
  });
}
function swapMain(src){
  const img = document.getElementById('mainImage');
  img.style.opacity = 0;
  setTimeout(()=>{ img.src = src; img.style.opacity = 1; },150);
}

/* -------- Zoom -------- */
const zoomBox = document.getElementById('zoomBox');
const mainImg = document.getElementById('mainImage');
zoomBox.addEventListener('mousemove', e=>{
  const r = zoomBox.getBoundingClientRect();
  const x = ((e.clientX-r.left)/r.width)*100;
  const y = ((e.clientY-r.top)/r.height)*100;
  mainImg.style.transformOrigin = `${x}% ${y}%`;
  mainImg.style.transform = 'scale(1.8)';
});
zoomBox.addEventListener('mouseleave', ()=>{
  mainImg.style.transform = 'scale(1)';
  mainImg.style.transformOrigin = 'center';
});

/* -------- Variants -------- */
function selectColor(el){
  document.querySelectorAll('.pdp-swatch').forEach(s=>s.classList.remove('active'));
  el.classList.add('active');
  selectedColor = el.dataset.color;
  document.getElementById('colorLabel').textContent = selectedColor;
}
function selectSize(el){
  document.querySelectorAll('.pdp-size').forEach(s=>s.classList.remove('active'));
  el.classList.add('active');
  selectedSize = el.dataset.size;
  document.getElementById('sizeLabel').textContent = selectedSize;
}

/* -------- Qty -------- */
function changeQty(d){
  const q = document.getElementById('qty');
  let v = parseInt(q.value)||1;
  v += d;
  if (v<1) v=1;
  if (MAX_STOCK>0 && v>MAX_STOCK) v=MAX_STOCK;
  q.value = v;
}
document.getElementById('qty').addEventListener('change', function(){
  let v = parseInt(this.value)||1;
  if (v<1) v=1;
  if (MAX_STOCK>0 && v>MAX_STOCK) v=MAX_STOCK;
  this.value = v;
});

/* -------- Cart / Buy Now -------- */
function validateVariants(){
  const hasColors = document.querySelectorAll('.pdp-swatch').length>0;
  const hasSizes  = document.querySelectorAll('.pdp-size').length>0;
  if (hasColors && !selectedColor){ alert('Please select a color.'); return false; }
  if (hasSizes  && !selectedSize ){ alert('Please select a size.');  return false; }
  if (MAX_STOCK<=0){ alert('This product is out of stock.'); return false; }
  return true;
}

async function addToCart(buyNow=false){
  if (!validateVariants()) return;
  const id  = document.querySelector('.add-to-cart').dataset.id;
  const qty = parseInt(document.getElementById('qty').value)||1;
  const btn = buyNow ? document.querySelector('.buy-now') : document.querySelector('.add-to-cart');
  const orig = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = buyNow ? 'Processing...' : 'Adding...';

  try {
    const res = await fetch('add_to_cart.php',{
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body:new URLSearchParams({id, qty, color:selectedColor, size:selectedSize, buy_now: buyNow?1:0})
    });
    const text = (await res.text()).trim();

    if (text==='added' || text==='ok'){
      window.location.href = buyNow ? 'checkout.php' : 'cart.php';
    } else if (text==='login_required'){
      window.location.href = 'login.php';
    } else if (text==='out_of_stock')   alert('Product is out of stock.');
    else if (text==='stock_limit')      alert('Requested quantity exceeds stock.');
    else if (text==='select_color')     alert('Please select a valid color.');
    else if (text==='select_size')      alert('Please select a valid size.');
    else {
      /* Fallback if buy_now flag isn't honoured — go to cart */
      if (buyNow && text==='added') window.location.href='checkout.php';
      else alert('Unable to add product. Please try again.');
    }
  } catch(err){
    alert('Something went wrong. Please try again.');
  } finally {
    btn.disabled = false;
    btn.innerHTML = orig;
  }
}

document.querySelector('.add-to-cart')?.addEventListener('click', ()=>addToCart(false));
document.querySelector('.buy-now')?.addEventListener('click',   ()=>addToCart(true));

/* -------- Wishlist (soft integration) -------- */
document.querySelector('.wishlist-btn')?.addEventListener('click', function(){
  const id = this.dataset.id;
  fetch('add_to_wishlist.php',{
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:new URLSearchParams({id})
  }).then(r=>r.text()).then(t=>{
    if (t.trim()==='added') alert('Added to wishlist!');
    else if (t.trim()==='login_required') window.location.href='login.php';
    else alert('Added to wishlist (or wishlist not configured).');
  }).catch(()=>alert('Wishlist unavailable.'));
});
</script>

<style>
/* ============================================================
   SAM'S PETS — Premium Product Details Page
   Brand: Ink #0f172a · Brand #ff5a3c · Accent #0ea5a4
   Fonts: Outfit (headings) · Manrope (body)
   ============================================================ */
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Manrope:wght@400;500;600;700;800&display=swap');

.pdp-wrap{
  background:#fbf7f2;
  color:#0f172a;
  font-family:'Manrope',system-ui,-apple-system,sans-serif;
  padding-bottom:40px;
}
.pdp-wrap h1,.pdp-wrap h2,.pdp-wrap h3,.pdp-wrap h4{
  font-family:'Outfit',sans-serif;
  font-weight:700;
}

/* ---------- Breadcrumb ---------- */
.pdp-breadcrumb{
  display:flex;flex-wrap:wrap;align-items:center;gap:8px;
  padding:20px 12px 0;font-size:13px;font-weight:600;color:#7a7268;
}
.pdp-breadcrumb a{color:#2b3446;text-decoration:none;transition:color .2s;}
.pdp-breadcrumb a:hover{color:#ff5a3c;}
.pdp-breadcrumb .current{color:#ff5a3c;font-weight:700;}

/* ---------- Layout ---------- */
.pdp-main{
  display:grid;
  grid-template-columns: minmax(0,1fr) minmax(0,1fr);
  gap:48px;
  padding-top:28px;
}

/* ---------- Gallery ---------- */
.pdp-gallery{position:sticky;top:100px;align-self:start;}
.pdp-main-img{
  position:relative;height:520px;
  background:#fff;
  border:1px solid #efe8e1;
  border-radius:26px;
  overflow:hidden;
  display:flex;align-items:center;justify-content:center;
  padding:24px;
  box-shadow:0 20px 60px -25px rgba(15,23,42,.15);
}
.pdp-main-img img{
  max-width:100%;max-height:100%;object-fit:cover;
  transition:transform .35s ease, opacity .25s ease;
  will-change:transform;
}
.pdp-nav{
  position:absolute;top:50%;transform:translateY(-50%);
  width:44px;height:44px;border-radius:50%;
  background:rgba(255,255,255,.95);
  border:1px solid #efe8e1;
  color:#0f172a;
  display:grid;place-items:center;
  cursor:pointer;font-size:18px;
  transition:.25s;box-shadow:0 6px 18px rgba(15,23,42,.08);
  z-index:2;
}
.pdp-nav.prev{left:14px;}
.pdp-nav.next{right:14px;}
.pdp-nav:hover{background:#ff5a3c;color:#fff;border-color:#ff5a3c;transform:translateY(-50%) scale(1.06);}
.pdp-zoom-tag{
  position:absolute;bottom:16px;right:16px;
  background:rgba(255,255,255,.95);
  backdrop-filter:blur(6px);
  padding:8px 14px;border-radius:999px;
  font-size:11px;font-weight:700;color:#2b3446;
  letter-spacing:.4px;
  box-shadow:0 4px 12px rgba(15,23,42,.06);
  display:flex;gap:6px;align-items:center;
}
.pdp-thumbs{
  display:flex;gap:12px;margin-top:16px;
  overflow-x:auto;padding-bottom:6px;
  scrollbar-width:thin;
}
.pdp-thumbs::-webkit-scrollbar{height:5px;}
.pdp-thumbs::-webkit-scrollbar-thumb{background:#efe8e1;border-radius:10px;}
.pdp-thumb{
  flex:0 0 88px;width:88px;height:88px;
  border-radius:18px;
  border:2px solid #efe8e1;
  background:#fff;
  padding:6px;cursor:pointer;
  transition:.25s;
}
.pdp-thumb img{width:100%;height:100%;object-fit:contain;}
.pdp-thumb:hover{border-color:#ffb8a8;transform:translateY(-2px);}
.pdp-thumb.active{border-color:#ff5a3c;box-shadow:0 6px 20px -6px rgba(255,90,60,.4);}

/* ---------- Info Column ---------- */
.pdp-info{padding-top:4px;}
.pdp-label{
  display:inline-block;
  background:#fff1ec;color:#ff5a3c;
  padding:6px 14px;border-radius:999px;
  font-size:11px;font-weight:800;
  letter-spacing:1px;text-transform:uppercase;
  margin-bottom:14px;
}
.pdp-category{
  color:#7a7268;font-size:12px;font-weight:700;
  letter-spacing:1.5px;text-transform:uppercase;
  margin-bottom:10px;
}
.pdp-title{
  font-size:clamp(26px,3.2vw,40px);
  line-height:1.15;letter-spacing:-.6px;
  color:#0f172a;margin:0 0 10px;
  font-weight:800;
}
.pdp-brand{color:#2b3446;font-size:14px;font-weight:600;margin-bottom:14px;}
.pdp-rating-row{display:flex;align-items:center;gap:12px;margin-bottom:22px;}
.pdp-stars{color:#f59e0b;letter-spacing:3px;font-size:18px;}
.pdp-rating-count{font-size:13px;color:#7a7268;font-weight:600;}

.pdp-price-box{
  display:flex;align-items:center;flex-wrap:wrap;gap:14px;
  background:linear-gradient(120deg,#fff1ec 0%,#fff 65%);
  border:1px solid #ffe0d6;
  border-radius:18px;
  padding:18px 22px;
  margin-bottom:22px;
}
.pdp-price-new{font-family:'Outfit';font-size:34px;font-weight:800;color:#ff5a3c;letter-spacing:-.5px;}
.pdp-price-old{color:#a19a92;text-decoration:line-through;font-size:17px;font-weight:500;}
.pdp-discount{
  background:#ff5a3c;color:#fff;
  padding:6px 12px;border-radius:999px;
  font-size:11px;font-weight:800;letter-spacing:.6px;
}
.pdp-short-desc{
  font-size:15px;line-height:1.75;color:#475467;
  margin:0 0 22px;
}
.pdp-stock{
  display:inline-flex;align-items:center;gap:8px;
  padding:10px 16px;border-radius:999px;
  font-size:13px;font-weight:700;
  margin-bottom:22px;
}
.pdp-stock.in{background:#e8f8f1;color:#0e7a4d;}
.pdp-stock.out{background:#fdeceb;color:#c0392b;}
.pdp-stock-count{font-weight:500;opacity:.8;margin-left:4px;}

/* ---------- Variants ---------- */
.pdp-variant{margin-bottom:22px;}
.pdp-variant > label{
  display:block;font-size:13px;font-weight:700;
  color:#2b3446;margin-bottom:10px;
  letter-spacing:.3px;
}
.pdp-variant > label strong{color:#ff5a3c;margin-left:4px;}

.pdp-colors{display:flex;flex-wrap:wrap;gap:10px;}
.pdp-swatch{
  width:40px;height:40px;border-radius:50%;
  border:3px solid #fff;
  background:var(--sw);
  box-shadow:0 0 0 1.5px #efe8e1;
  cursor:pointer;transition:.22s;
  position:relative;
}
.pdp-swatch:hover{transform:scale(1.08);}
.pdp-swatch.active{
  box-shadow:0 0 0 2.5px #ff5a3c, 0 8px 20px -6px rgba(255,90,60,.5);
  transform:scale(1.05);
}

.pdp-sizes{display:flex;flex-wrap:wrap;gap:10px;}
.pdp-size{
  min-width:52px;padding:11px 18px;
  border:1.5px solid #efe8e1;
  background:#fff;color:#2b3446;
  border-radius:14px;
  font-size:13px;font-weight:700;
  cursor:pointer;transition:.22s;
  letter-spacing:.3px;
}
.pdp-size:hover{border-color:#ffb8a8;color:#ff5a3c;}
.pdp-size.active{
  background:#ff5a3c;color:#fff;border-color:#ff5a3c;
  box-shadow:0 8px 20px -8px rgba(255,90,60,.6);
  transform:translateY(-1px);
}

/* ---------- Qty ---------- */
.pdp-qty-row{margin-bottom:24px;}
.pdp-qty-row > label{
  display:block;font-size:13px;font-weight:700;
  color:#2b3446;margin-bottom:10px;
}
.pdp-qty{
  display:inline-flex;align-items:center;
  background:#fff;border:1.5px solid #efe8e1;
  border-radius:999px;padding:6px;
  box-shadow:0 4px 14px -8px rgba(15,23,42,.08);
}
.pdp-qty button{
  width:38px;height:38px;border:0;
  border-radius:50%;background:#fbf7f2;
  color:#2b3446;font-size:18px;font-weight:700;
  cursor:pointer;transition:.2s;
}
.pdp-qty button:hover{background:#ff5a3c;color:#fff;}
.pdp-qty input{
  width:56px;border:0;outline:none;background:transparent;
  text-align:center;font-size:15px;font-weight:800;color:#0f172a;
  font-family:'Manrope';
}

/* ---------- Action Buttons ---------- */
.pdp-actions{
  display:grid;grid-template-columns:1fr 1fr;gap:12px;
  margin-bottom:12px;
}
.pdp-btn{
  display:inline-flex;align-items:center;justify-content:center;gap:8px;
  padding:15px 22px;
  border-radius:999px;
  font-family:'Outfit';font-size:14px;font-weight:700;
  letter-spacing:.4px;
  border:0;cursor:pointer;
  transition:.25s;
  text-decoration:none;
}
.pdp-btn:disabled{opacity:.55;cursor:not-allowed;transform:none!important;box-shadow:none!important;}
.pdp-btn-cart{
  background:#0f172a;color:#fff;
  box-shadow:0 10px 25px -10px rgba(15,23,42,.5);
}
.pdp-btn-cart:hover:not(:disabled){transform:translateY(-2px);box-shadow:0 14px 28px -10px rgba(15,23,42,.6);}
.pdp-btn-buy{
  background:linear-gradient(135deg,#ff5a3c 0%,#ff2d55 100%);
  color:#fff;
  box-shadow:0 10px 30px -10px rgba(255,90,60,.7);
}
.pdp-btn-buy:hover:not(:disabled){transform:translateY(-2px);box-shadow:0 16px 32px -10px rgba(255,45,85,.75);}
.pdp-wishlist{
  width:100%;background:#fff;color:#2b3446;
  border:1.5px solid #efe8e1;
  border-radius:999px;padding:13px;
  font-size:13px;font-weight:700;cursor:pointer;
  display:flex;align-items:center;justify-content:center;gap:8px;
  transition:.25s;font-family:'Manrope';
}
.pdp-wishlist:hover{border-color:#ff5a3c;color:#ff5a3c;background:#fff1ec;}
.pdp-oos-msg{color:#c0392b;font-size:13px;font-weight:700;margin-top:10px;text-align:center;}

/* ---------- Delivery ---------- */
.pdp-delivery{
  margin-top:26px;display:grid;gap:10px;
  padding:18px;border:1px solid #efe8e1;
  border-radius:18px;background:#fff;
}
.pdp-del-item{display:flex;align-items:center;gap:12px;}
.pdp-del-item i{
  width:38px;height:38px;border-radius:12px;
  background:#fff1ec;color:#ff5a3c;
  display:grid;place-items:center;font-size:16px;
  flex-shrink:0;
}
.pdp-del-item strong{display:block;font-size:13px;font-weight:700;color:#0f172a;margin-bottom:2px;}
.pdp-del-item small{font-size:11px;color:#7a7268;}

/* ---------- Sections ---------- */
.pdp-section{padding:60px 12px 0;}
.pdp-section-head{margin-bottom:28px;}
.pdp-section-head.center{text-align:center;max-width:640px;margin:0 auto 40px;}
.pdp-section-head > span{
  display:block;color:#ff5a3c;font-size:11px;
  font-weight:800;letter-spacing:2px;margin-bottom:8px;
}
.pdp-section-head h2{
  font-size:clamp(24px,3vw,34px);color:#0f172a;
  font-weight:800;letter-spacing:-.5px;margin:0;
}
.pdp-desc-card{
  background:#fff;border:1px solid #efe8e1;
  border-radius:26px;padding:32px;
  box-shadow:0 20px 50px -30px rgba(15,23,42,.15);
}
.pdp-desc{font-size:15px;line-height:1.85;color:#475467;}
.pdp-specs{margin-top:32px;}
.pdp-specs h3{font-size:18px;margin:0 0 18px;color:#0f172a;}
.pdp-spec-grid{
  display:grid;grid-template-columns:repeat(2,minmax(0,1fr));
  gap:1px;background:#efe8e1;
  border-radius:18px;overflow:hidden;
  border:1px solid #efe8e1;
}
.pdp-spec-grid > div{
  background:#fff;padding:16px 20px;
  display:flex;justify-content:space-between;gap:12px;
  font-size:13px;
}
.pdp-spec-grid span{color:#7a7268;font-weight:600;}
.pdp-spec-grid strong{color:#0f172a;font-weight:700;text-align:right;}
.pdp-tags{margin-top:24px;display:flex;flex-wrap:wrap;gap:8px;align-items:center;font-size:13px;}
.pdp-tags strong{color:#2b3446;margin-right:4px;}
.pdp-tags span{
  background:#fff1ec;color:#ff5a3c;
  padding:6px 14px;border-radius:999px;
  font-weight:700;font-size:12px;
}

/* ---------- Reviews ---------- */
.pdp-reviews{
  background:#fff;
  margin-top:60px;
  padding:60px 0;
  border-top:1px solid #efe8e1;
  border-bottom:1px solid #efe8e1;
}
.pdp-reviews-layout{
  display:grid;
  grid-template-columns:minmax(0,1.4fr) minmax(320px,.9fr);
  gap:30px;align-items:start;
}
.pdp-reviews-list{min-width:0;}
.pdp-review-card{
  background:#fbf7f2;
  border:1px solid #efe8e1;
  border-radius:18px;
  padding:22px;margin-bottom:16px;
}
.pdp-review-top{
  display:flex;align-items:center;gap:14px;
  margin-bottom:12px;
}
.pdp-avatar{
  width:44px;height:44px;flex:0 0 44px;
  border-radius:50%;background:#ff5a3c;color:#fff;
  display:grid;place-items:center;
  font-weight:800;font-size:17px;font-family:'Outfit';
}
.pdp-review-top > div:nth-child(2){flex:1;min-width:0;}
.pdp-review-top strong{display:block;font-size:14px;color:#0f172a;margin-bottom:2px;}
.pdp-review-top small{font-size:12px;color:#7a7268;}
.pdp-review-stars{color:#f59e0b;letter-spacing:2px;font-size:14px;white-space:nowrap;}
.pdp-review-card h4{font-size:15px;margin:0 0 6px;color:#0f172a;}
.pdp-review-card p{margin:0;font-size:14px;line-height:1.7;color:#475467;overflow-wrap:anywhere;}

.pdp-review-form{
  background:#fff;
  border:1px solid #efe8e1;
  border-radius:26px;
  padding:28px;
  box-shadow:0 20px 50px -30px rgba(15,23,42,.15);
  position:sticky;top:100px;
}
.pdp-review-form h3{font-size:22px;margin:0 0 6px;color:#0f172a;}
.pdp-review-form > p{color:#7a7268;font-size:13px;margin:0 0 22px;}
.pdp-form-group{margin-bottom:16px;}
.pdp-form-group label{
  display:block;font-size:12px;font-weight:700;
  color:#2b3446;margin-bottom:7px;letter-spacing:.3px;
}
.pdp-form-group input,
.pdp-form-group textarea{
  width:100%;padding:12px 14px;
  border:1.5px solid #efe8e1;border-radius:12px;
  font-size:14px;font-family:'Manrope';
  background:#fbf7f2;color:#0f172a;
  outline:none;transition:.22s;
}
.pdp-form-group input:focus,
.pdp-form-group textarea:focus{
  border-color:#ff5a3c;background:#fff;
  box-shadow:0 0 0 4px rgba(255,90,60,.1);
}
.pdp-form-group textarea{resize:vertical;min-height:110px;}

.pdp-rating-picker{
  display:flex;flex-direction:row-reverse;
  justify-content:flex-end;gap:6px;
}
.pdp-rating-picker input{position:absolute;opacity:0;width:1px;height:1px;}
.pdp-rating-picker label{
  font-size:30px;line-height:1;color:#d6cec5;
  cursor:pointer;transition:color .2s;
}
.pdp-rating-picker label:hover,
.pdp-rating-picker label:hover ~ label,
.pdp-rating-picker input:checked ~ label{color:#f59e0b;}

.pdp-alert{
  padding:14px 18px;border-radius:12px;
  font-size:14px;font-weight:600;
  margin-bottom:20px;
}
.pdp-alert.ok{background:#e8f8f1;color:#0e7a4d;border:1px solid #c8f1d8;}
.pdp-alert.err{background:#fdeceb;color:#c0392b;border:1px solid #ffd8d4;}

.pdp-no-reviews{
  text-align:center;padding:50px 20px;
  border:2px dashed #efe8e1;border-radius:20px;
  background:#fbf7f2;
}
.pdp-no-reviews i{
  font-size:42px;color:#ff5a3c;
  display:block;margin-bottom:14px;
}
.pdp-no-reviews h3{font-size:18px;margin:0 0 6px;}
.pdp-no-reviews p{font-size:13px;color:#7a7268;margin:0;}

/* ---------- Related ---------- */
.pdp-related{padding:60px 0 40px;}
.pdp-related-grid{
  display:grid;
  grid-template-columns:repeat(4,minmax(0,1fr));
  gap:20px;
}
.pdp-rel-card{
  background:#fff;border:1px solid #efe8e1;
  border-radius:22px;overflow:hidden;
  text-decoration:none;color:inherit;
  display:flex;flex-direction:column;
  transition:.3s;
  box-shadow:0 6px 20px -12px rgba(15,23,42,.1);
}
.pdp-rel-card:hover{transform:translateY(-6px);box-shadow:0 24px 40px -22px rgba(15,23,42,.25);}
.pdp-rel-img{
  position:relative;height:220px;
  background:#fbf7f2;
  display:flex;align-items:center;justify-content:center;
  padding:16px;overflow:hidden;
}
.pdp-rel-img img{
  max-width:100%;max-height:100%;object-fit:contain;
  transition:transform .4s;
}
.pdp-rel-card:hover .pdp-rel-img img{transform:scale(1.07);}
.pdp-rel-label{
  position:absolute;top:12px;left:12px;
  background:#ff5a3c;color:#fff;
  padding:5px 11px;border-radius:999px;
  font-size:10px;font-weight:800;letter-spacing:.6px;
}
.pdp-rel-body{padding:16px 18px 20px;display:flex;flex-direction:column;gap:6px;}
.pdp-rel-brand{
  font-size:11px;font-weight:800;color:#ff5a3c;
  letter-spacing:1.2px;text-transform:uppercase;
}
.pdp-rel-body h4{
  font-size:14px;font-weight:700;color:#0f172a;
  margin:0;line-height:1.4;
  display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;
  overflow:hidden;min-height:40px;
}
.pdp-rel-price{display:flex;align-items:center;gap:8px;margin-top:auto;padding-top:8px;}
.pdp-rel-price strong{color:#ff5a3c;font-size:17px;font-family:'Outfit';font-weight:800;}
.pdp-rel-price del{color:#a19a92;font-size:12px;}

/* ---------- Responsive ---------- */
@media (max-width:991px){
  .pdp-main{grid-template-columns:1fr;gap:36px;}
  .pdp-gallery{position:static;}
  .pdp-main-img{height:440px;}
  .pdp-reviews-layout{grid-template-columns:1fr;}
  .pdp-review-form{position:static;}
  .pdp-related-grid{grid-template-columns:repeat(3,minmax(0,1fr));}
  .pdp-rel-img{height:190px;}
}
@media (max-width:767px){
  .pdp-main-img{height:320px;padding:16px;border-radius:20px;}
  .pdp-nav{width:38px;height:38px;font-size:15px;}
  .pdp-thumb{flex:0 0 70px;width:70px;height:70px;border-radius:14px;}
  .pdp-title{font-size:26px;}
  .pdp-price-new{font-size:28px;}
  .pdp-actions{grid-template-columns:1fr;}
  .pdp-spec-grid{grid-template-columns:1fr;}
  .pdp-desc-card{padding:22px;}
  .pdp-related-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;}
  .pdp-rel-img{height:150px;}
  .pdp-reviews{padding:44px 0;}
}
@media (max-width:420px){
  .pdp-main-img{height:270px;}
  .pdp-thumb{flex:0 0 60px;width:60px;height:60px;}
  .pdp-title{font-size:22px;}
  .pdp-related-grid{gap:10px;}
  .pdp-rel-body{padding:12px 14px 16px;}
  .pdp-rel-body h4{font-size:13px;}
}
</style>

<?php include "include/footer.php"; ?>