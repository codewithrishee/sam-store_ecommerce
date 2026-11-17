<link rel="stylesheet" href="style.css">

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "config/db.php";
include "include/header.php";

// GET + POST
$cat = 0;
$sub = 0;

if(isset($_POST['cat_id'])){
    $cat = (int)$_POST['cat_id'];
}
elseif(isset($_GET['cat'])){
    $cat = (int)$_GET['cat'];
}

if(isset($_POST['sub_id'])){
    $sub = (int)$_POST['sub_id'];
}
elseif(isset($_GET['sub'])){
    $sub = (int)$_GET['sub'];
}

//  CATEGORY NAME FETCH ( HEADING KE LIYE)
$title = "All Products";

if($sub > 0){
    $res = mysqli_query($conn, "SELECT name FROM sub_categories WHERE id = $sub");
    if($row = mysqli_fetch_assoc($res)){
        $title = $row['name'];
    }
}
elseif($cat > 0){
    $res = mysqli_query($conn, "SELECT name FROM categories WHERE id = $cat");
    if($row = mysqli_fetch_assoc($res)){
        $title = $row['name'];
    }
}

$where = "WHERE 1=1";

if($cat > 0){
    $where .= " AND pc.category_id = $cat";
}

if($sub > 0){
    $where .= " AND pc.subcategory_id = $sub";
}

$query = "
SELECT DISTINCT p.* 
FROM products p
JOIN product_categories pc ON p.id = pc.product_id
$where
ORDER BY p.id DESC
";

$result = mysqli_query($conn, $query);
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

<div class="container my-5">
    
    <h2 class="text-center fw-bold mb-4">
        <?= htmlspecialchars($title) ?>
    </h2>

    <div class="row g-4">

    <?php if(mysqli_num_rows($result) > 0){ ?>

        <?php while($row = mysqli_fetch_assoc($result)){

            $discount = 0;
            if ($row['old_price'] > 0) {
                $discount = round((($row['old_price'] - $row['new_price']) / $row['old_price']) * 100);
            }
        ?>

        <div class="col-lg-3 col-md-6 col-6">
            <div class="card product-card border-0 shadow-sm h-100">

                <div class="product-img position-relative">

                    <?php if ($discount > 0) { ?>
                        <span class="discount">-<?= $discount ?>%</span>
                    <?php } ?>

                    <a href="product-details.php?id=<?= $row['id']; ?>">
                        <img src="images/<?= $row['image1']; ?>" class="img-fluid img1">

                        <img src="images/<?= !empty($row['image2']) ? $row['image2'] : $row['image1']; ?>"
                            class="img-fluid img2">
                    </a>

                    <div class="hover-icons">
                        <a href="javascript:void(0)" class="wishlist-btn" data-id="<?= $row['id']; ?>">
                            <i class="fa fa-heart"></i>
                        </a>

                        <a href="product-details.php?id=<?= $row['id']; ?>">
                            <i class="fa fa-eye"></i>
                        </a>
                    </div>

                </div>

                <div class="card-body position-relative">

                    <p class="text-muted mb-1">SAM'S Pets</p>

                    <h6>
                        <a href="product-details.php?id=<?= $row['id']; ?>"
                            class="text-dark text-decoration-none">
                            <?= htmlspecialchars($row['product_name']); ?>
                        </a>
                    </h6>

                    <p class="product-description">
    <?= shortProductDescription($row['description'] ?? '', 10); ?>
</p>

                    <div class="d-flex align-items-center">
                        <span class="text-danger fw-bold me-2">
                            ₹<?= number_format($row['new_price'], 2); ?>
                        </span>

                        <?php if ($row['old_price'] > 0) { ?>
                            <small class="text-muted text-decoration-line-through">
                                ₹<?= number_format($row['old_price'], 2); ?>
                            </small>
                        <?php } ?>
                    </div>

                    <button class="cart-btn">
                        <i class="fa fa-shopping-cart"></i>
                    </button>

                </div>

            </div>
        </div>

        <?php } ?>

    <?php } else { ?>

        <div class="col-12 text-center">
            <h4>No Products Found</h4>
        </div>

    <?php } ?>

    </div>

</div>

<?php
include "include/footer.php";
?>