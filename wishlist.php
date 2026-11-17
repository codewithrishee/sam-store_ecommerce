
<?php
ob_start();
session_start();

include "config/db.php";

// LOGIN CHECK
if (!isset($_SESSION['user'])) {

    $_SESSION['wishlist_login_required'] = true;
    $_SESSION['redirect_after_login'] = 'wishlist.php';

    header("Location: login.php");
    exit;
}

// GET LOGGED-IN USER
$email = $_SESSION['user'];

$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();

$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    die("User not found");
}

$user_id = (int) $user['id'];

// ALERT MESSAGE
$alertMessage = "";

if (isset($_GET['msg'])) {

    $messages = [
        "added"   => "Added to wishlist!",
        "exist"   => "Already in wishlist!",
        "removed" => "Removed from wishlist!"
    ];

    if (isset($messages[$_GET['msg']])) {
        $alertMessage = $messages[$_GET['msg']];
    }
}

// FETCH WISHLIST PRODUCTS
$stmt = $conn->prepare("
    SELECT p.*, w.id AS wid
    FROM wishlist w
    JOIN products p ON w.product_id = p.id
    WHERE w.user_id = ?
    ORDER BY w.id DESC
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$query = $stmt->get_result();

include "include/header.php";
?>

<!-- SUCCESS ALERT -->
<?php if (!empty($alertMessage)) { ?>
<script>
document.addEventListener("DOMContentLoaded", function () {
    Swal.fire({
        icon: "success",
        title: <?= json_encode($alertMessage) ?>,
        showConfirmButton: false,
        timer: 1800
    });
});
</script>
<?php } ?>


<div class="container my-5">

    <h1 class="text-center mb-4">Wishlist Products</h1>

    <div class="row g-4">

        <?php if ($query->num_rows > 0) { ?>

            <?php while ($row = $query->fetch_assoc()) {

                $discount = 0;

                if ($row['old_price'] > 0) {
                    $discount = round(
                        (($row['old_price'] - $row['new_price']) / $row['old_price']) * 100
                    );
                }
            ?>

                <div class="col-lg-3 col-md-6 col-6 mb-4">

                    <div class="card product-card border-0">

                        <div class="product-img">

                            <?php if ($discount > 0) { ?>
                                <span class="discount">
                                    -<?= $discount ?>%
                                </span>
                            <?php } ?>

                            <a href="product-details.php?id=<?= (int)$row['id'] ?>">
                                <img
                                    src="images/<?= htmlspecialchars($row['image1']) ?>"
                                    class="img-fluid img1"
                                    alt="<?= htmlspecialchars($row['product_name']) ?>"
                                >

                                <img
                                    src="images/<?= htmlspecialchars($row['image2'] ?: $row['image1']) ?>"
                                    class="img-fluid img2"
                                    alt="<?= htmlspecialchars($row['product_name']) ?>"
                                >
                            </a>

                            <div class="hover-icons">

                                <!-- REMOVE FROM WISHLIST -->
                                <a href="remove-wish.php?id=<?= (int)$row['wid'] ?>">
                                    <i class="fa fa-heart text-danger"></i>
                                </a>

                                <!-- VIEW PRODUCT -->
                                <a href="product-details.php?id=<?= (int)$row['id'] ?>">
                                    <i class="fa fa-eye"></i>
                                </a>

                            </div>

                        </div>

                        <div class="card-body position-relative">

                            <p class="text-muted mb-1">SAM'S Pets</p>

                            <h6>
                                <a
                                    href="product-details.php?id=<?= (int)$row['id'] ?>"
                                    style="text-decoration:none;color:#000;"
                                >
                                    <?= htmlspecialchars($row['product_name']) ?>
                                </a>
                            </h6>

                            <div class="d-flex align-items-center">

                                <span class="text-danger fw-bold me-2">
                                    ₹<?= htmlspecialchars($row['new_price']) ?>
                                </span>

                                <?php if ($row['old_price'] > 0) { ?>
                                    <small class="text-muted text-decoration-line-through">
                                        ₹<?= htmlspecialchars($row['old_price']) ?>
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
                <h4>No items in wishlist</h4>
            </div>

        <?php } ?>

    </div>

</div>

<link rel="stylesheet" href="style.css">

<?php include "include/footer.php"; ?>
