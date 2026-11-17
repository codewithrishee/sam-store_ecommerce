<?php
include 'include/header.php';
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

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owl Carousel Banner</title>

    <link rel="stylesheet" href="style.css">
    <!-- jQuery (FIRST) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Owl Carousel CSS -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css" />
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css" />

    <!-- Owl Carousel JS (MISSING THA) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>


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

    <!--banner section-->
    <?php
    include "./config/db.php";
    $result = mysqli_query($conn, "
SELECT * FROM banners 
WHERE type='hero' 
AND (page='home' OR page='both')
AND status=1 
ORDER BY id DESC 
LIMIT 3
");
    ?>
    <div class="owl-carousel banner-slider">
        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
            <div class="item">
                <img src="images/<?= $row['image']; ?>" class="img-fluid">
                <div class="slide-content">
                    <h1><?= $row['heading']; ?></h1>
                    <p><?= $row['subheading']; ?></p>
                    <a href="#" class="btn mt-3" style="background:<?= $row['button_color']; ?>; color:#fff;">
                        <?= $row['button_text']; ?>
                    </a>
                </div>
            </div>
        <?php } ?>
    </div>




    <!--Best Selling-->
    <?php
    include("config/db.php");
    $best = mysqli_query($conn, "
SELECT p.* 
FROM products p
JOIN product_section ps ON p.id = ps.product_id
JOIN sections s ON ps.section_id = s.id
WHERE s.name = 'editors_picks'
LIMIT 8
");
    ?>

    <div class="container my-5">
        <div class="row g-4">

            <h1 class="text-center mb-4">Editor's Picks</h1>

            <?php if (mysqli_num_rows($best) > 0) { ?>

                <?php while ($row = mysqli_fetch_assoc($best)) {

                    $discount = 0;
                    if ($row['old_price'] > 0) {
                        $discount = round((($row['old_price'] - $row['new_price']) / $row['old_price']) * 100);
                    }
                    ?>

                    <div class="col-lg-2-4 col-md-6 col-6">
                        <div class="card product-card border-0">

                            <div class="product-img">

                                <?php if ($discount > 0) { ?>
                                    <span class="discount">-<?= $discount ?>%</span>
                                <?php } ?>

                                <a href="product-details.php?id=<?= $row['id']; ?>">
                                    <img src="images/<?= $row['image1']; ?>" class="img-fluid img1">

                                    <img src="images/<?= !empty($row['image2']) ? $row['image2'] : $row['image1']; ?>"
                                        class="img-fluid img2">
                                </a>

                                <div class="hover-icons">
                                    <a href="javascript:void(0)" class="wishlist-btn" data-id="<?= $row['id']; ?>"><i
                                            class="fa fa-heart"></i></a>
                                    <a href="product-details.php?id=<?= $row['id']; ?>">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                </div>

                            </div>

                            <div class="card-body position-relative">

                                <p class="text-muted mb-1">SAM'S Pets</p>

                                <h6>
                                    <a href="product-details.php?id=<?= $row['id']; ?>"
                                        style="text-decoration:none;color:#000;">
                                        <?= $row['product_name']; ?>
                                    </a>
                                </h6>

                                <p class="product-description">
                                    <?= shortProductDescription($row['description'] ?? '', 10); ?>
                                </p>

                                <div class="d-flex align-items-center">
                                    <span class="text-danger fw-bold me-2">
                                        ₹<?= $row['new_price']; ?>
                                    </span>

                                    <?php if ($row['old_price'] > 0) { ?>
                                        <small class="text-muted text-decoration-line-through">
                                            ₹<?= $row['old_price']; ?>
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
                    <h4>No Best Selling Products </h4>
                </div>
            <?php } ?>

        </div>
    </div>


    <!--Shop Our Top Collection-->
    <?php
    include("config/db.php");

    $new = mysqli_query($conn, "
SELECT p.* 
FROM products p
JOIN product_section ps ON p.id = ps.product_id
JOIN sections s ON ps.section_id = s.id
WHERE s.name = 'top_collection'
LIMIT 8
");
    ?>

    <div class="container my-5">

        <!-- TITLE -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="feature-title">Shop Our Top Collection</h2>
            <a href="shop.php" class="view-btn">View All</a>
        </div>

        <!--  SLIDER START -->
        <div class="owl-carousel product-slider">

            <?php while ($row = mysqli_fetch_assoc($new)) {
                $new_price = !empty($row['new_price']) ? $row['new_price'] : 0;
                $old_price = !empty($row['old_price']) ? $row['old_price'] : 0;
                $discount = 0;

                if ($old_price > 0 && $new_price > 0) {
                    $discount = round((($old_price - $new_price) / $old_price) * 100);
                }
                ?>

                <div class="product-item">
                    <div class="card product-card border-0">

                        <div class="product-img">
                            <?php if ($discount > 0) { ?>
                                <span class="discount">-<?= $discount ?>%</span>
                            <?php } ?>

                            <a href="product-details.php?id=<?= $row['id']; ?>">
                                <img src="images/<?= $row['image1']; ?>" class="img-fluid img1">
                                <img src="images/<?= $row['image2']; ?>" class="img-fluid img2">
                            </a>

                            <!-- HOVER ICONS -->
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
                                <a href="product-details.php?id=<?= $row['id']; ?>" class="text-dark text-decoration-none">
                                    <?= $row['product_name']; ?>
                                </a>
                            </h6>
                            <p class="product-description">
                                <?= shortProductDescription($row['description'] ?? '', 10); ?>
                            </p>

                            <div class="d-flex align-items-center">
                                <span class="text-danger fw-bold me-2">
                                    ₹<?= number_format($new_price, 2); ?>
                                </span>

                                <?php if ($old_price > 0) { ?>
                                    <small class="text-muted text-decoration-line-through">
                                        ₹<?= number_format($old_price, 2); ?>
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

        </div>
        <!--  SLIDER END -->

    </div>


    <!--small banners-->
    <?php
    include("config/db.php");
    $promo = mysqli_query($conn, "
    SELECT * FROM banners 
    WHERE type='promo'  
    AND status=1 
    AND (page='home' OR page='both')
    ORDER BY id DESC 
    LIMIT 2
    ");
    ?>
    <div class="container my-5 bg-light">
        <div class="row g-4">
            <h1 class="text-center">Everything Your Pet Needs, All in One Place</h1>
            <?php while ($row = mysqli_fetch_assoc($promo)) { ?>
                <div class="col-md-6">
                    <div class="promo-box">
                        <img src="images/<?php echo $row['image']; ?>" alt="">
                        <div class="promo-content">
                            <h4><?php echo $row['heading']; ?></h4>
                            <p><?php echo $row['subheading']; ?></p>
                            <a href="<?php echo $row['button_link']; ?>" class="promo-btn"
                                style="background:<?php echo $row['button_color']; ?>;">
                                <?php echo $row['button_text']; ?>
                            </a>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>

    <?php
    include("config/db.php");

    $best = mysqli_query($conn, "
SELECT p.* 
FROM products p
JOIN product_section ps ON p.id = ps.product_id
JOIN sections s ON ps.section_id = s.id
WHERE s.name = 'Featured_Products'
LIMIT 8
");
    ?>

    <div class="container my-5">
        <div class="row g-4">

            <h1 class="text-center mb-4">Featured Products</h1>

            <?php if (mysqli_num_rows($best) > 0) { ?>

                <?php while ($row = mysqli_fetch_assoc($best)) {

                    $discount = 0;
                    if ($row['old_price'] > 0) {
                        $discount = round((($row['old_price'] - $row['new_price']) / $row['old_price']) * 100);
                    }
                    ?>

                    <div class="col-lg-3 col-md-6 col-6">
                        <div class="card product-card border-0">

                            <div class="product-img">

                                <?php if ($discount > 0) { ?>
                                    <span class="discount">-<?= $discount ?>%</span>
                                <?php } ?>

                                <a href="product-details.php?id=<?= $row['id']; ?>">
                                    <img src="images/<?= $row['image1']; ?>" class="img-fluid img1">

                                    <img src="images/<?= !empty($row['image2']) ? $row['image2'] : $row['image1']; ?>"
                                        class="img-fluid img2">
                                </a>

                                <div class="hover-icons">
                                    <a href="javascript:void(0)" class="wishlist-btn" data-id="<?= $row['id']; ?>"><i
                                            class="fa fa-heart"></i></a>
                                    <a href="product-details.php?id=<?= $row['id']; ?>">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                </div>

                            </div>

                            <div class="card-body position-relative">

                                <p class="text-muted mb-1">SAM'S Pets</p>

                                <h6>
                                    <a href="product-details.php?id=<?= $row['id']; ?>"
                                        style="text-decoration:none;color:#000;">
                                        <?= $row['product_name']; ?>
                                    </a>
                                </h6>

                                <p class="product-description">
                                    <?= shortProductDescription($row['description'] ?? '', 10); ?>
                                </p>

                                <div class="d-flex align-items-center">
                                    <span class="text-danger fw-bold me-2">
                                        ₹<?= $row['new_price']; ?>
                                    </span>

                                    <?php if ($row['old_price'] > 0) { ?>
                                        <small class="text-muted text-decoration-line-through">
                                            ₹<?= $row['old_price']; ?>
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
                    <h4>No Best Selling Products </h4>
                </div>

            <?php } ?>

        </div>
    </div>

    <!--new arrival-->
    <?php
    include("config/db.php");
    $new = mysqli_query($conn, "
SELECT p.* 
FROM products p
JOIN product_section ps ON p.id = ps.product_id
JOIN sections s ON ps.section_id = s.id
WHERE s.name = 'home_new_arrival'
LIMIT 8
");
    ?>

    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="feature-title">New Arrivals</h2>
            <a href="shop.php" class="view-btn">View All</a>
        </div>
        <div class="row g-4">
            <?php while ($row = mysqli_fetch_assoc($new)) {
                $new_price = !empty($row['new_price']) ? $row['new_price'] : 0;
                $old_price = !empty($row['old_price']) ? $row['old_price'] : 0;
                $discount = 0;
                if ($old_price > 0 && $new_price > 0) {
                    $discount = round((($old_price - $new_price) / $old_price) * 100);
                }
                ?>
                <div class="col-lg-3 col-md-6 col-6 product-item">
                    <div class="card product-card border-0">
                        <div class="product-img">
                            <?php if ($discount > 0) { ?>
                                <span class="discount">-<?= $discount ?>%</span>
                            <?php } ?>
                            <a href="product-details.php?id=<?= $row['id']; ?>">
                                <img src="images/<?= $row['image1']; ?>" class="img-fluid img1">
                                <img src="images/<?= $row['image2']; ?>" class="img-fluid img2">
                            </a>
                            <!--HOVER ICONS -->
                            <div class="hover-icons">
                                <a href="javascript:void(0)" class="wishlist-btn" data-id="<?= $row['id']; ?>">
                                    <i class="fa fa-heart"></i></a>
                                <a href="product-details.php?id=<?= $row['id']; ?>">
                                    <i class="fa fa-eye"></i>
                                </a>
                            </div>
                        </div>
                        <div class="card-body position-relative">
                            <p class="text-muted mb-1">SAM'S Pets</p>
                            <h6>
                                <a href="product-details.php?id=<?= $row['id']; ?>"
                                    style="text-decoration:none;color:#000;">
                                    <?= $row['product_name']; ?>
                                </a>
                            </h6>

                            <p class="product-description">
                                <?= shortProductDescription($row['description'] ?? '', 10); ?>
                            </p>
                            <div class="d-flex align-items-center">
                                <span class="text-danger fw-bold me-2">
                                    ₹<?= number_format($new_price, 2); ?>
                                </span>
                                <?php if ($old_price > 0) { ?>
                                    <small class="text-muted text-decoration-line-through">
                                        ₹<?= number_format($old_price, 2); ?>
                                    </small>
                                <?php } ?>
                            </div>
                            <button class="cart-btn" onclick="event.stopPropagation();">
                                <i class="fa fa-shopping-cart"></i>
                            </button>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>


    <!--hot deals-->
    <?php
    include("config/db.php");

    $new = mysqli_query($conn, "
SELECT p.* 
FROM products p
JOIN product_section ps ON p.id = ps.product_id
JOIN sections s ON ps.section_id = s.id
WHERE s.name = 'hot_deals'
LIMIT 8
");
    ?>

    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="feature-title">Hot Deal's</h2>
            <a href="shop.php" class="view-btn">View All</a>
        </div>

        <div class="owl-carousel prod-slider">

            <?php while ($row = mysqli_fetch_assoc($new)) {
                $new_price = !empty($row['new_price']) ? $row['new_price'] : 0;
                $old_price = !empty($row['old_price']) ? $row['old_price'] : 0;
                $discount = 0;

                if ($old_price > 0 && $new_price > 0) {
                    $discount = round((($old_price - $new_price) / $old_price) * 100);
                }
                ?>

                <div class="product-item">
                    <div class="card product-card border-0">

                        <div class="product-img">
                            <?php if ($discount > 0) { ?>
                                <span class="discount">-<?= $discount ?>%</span>
                            <?php } ?>

                            <a href="product-details.php?id=<?= $row['id']; ?>">
                                <img src="images/<?= $row['image1']; ?>" class="img-fluid img1">
                                <img src="images/<?= $row['image2']; ?>" class="img-fluid img2">
                            </a>

                            <!-- HOVER ICONS -->
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
                                <a href="product-details.php?id=<?= $row['id']; ?>" class="text-dark text-decoration-none">
                                    <?= $row['product_name']; ?>
                                </a>
                            </h6>

                            <p class="product-description">
                                <?= shortProductDescription($row['description'] ?? '', 10); ?>
                            </p>

                            <div class="d-flex align-items-center">
                                <span class="text-danger fw-bold me-2">
                                    ₹<?= number_format($new_price, 2); ?>
                                </span>

                                <?php if ($old_price > 0) { ?>
                                    <small class="text-muted text-decoration-line-through">
                                        ₹<?= number_format($old_price, 2); ?>
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

        </div>
    </div>

    <!--shop our top collection-->
    <script>
        $('.product-slider').owlCarousel({
            loop: true,
            margin: 15,
            autoplay: true,
            autoplayTimeout: 3000,
            nav: true,
            dots: false,

            responsive: {
                0: { items: 2 },
                576: { items: 2 },
                768: { items: 3 },
                992: { items: 5 }
            }
        });
    </script>

    <!--hot deals---->
    <script>
        $('.prod-slider').owlCarousel({
            loop: true,
            margin: 15,
            autoplay: true,
            autoplayTimeout: 3000,
            nav: true,
            dots: false,

            responsive: {
                0: { items: 2 },
                576: { items: 2 },
                768: { items: 3 },
                992: { items: 5 }
            }
        });
    </script>



    <!--fetured categories filter-->
    <!-- <script>
        document.querySelectorAll(".category-list li").forEach(btn => {
            btn.addEventListener("click", function () {
                document.querySelectorAll(".category-list li").forEach(li => li.classList.remove("active"));
                this.classList.add("active");
                let cat = this.getAttribute("data-cat");
                document.querySelectorAll(".product-item").forEach(item => {
                    let itemCats = item.getAttribute("data-cat") || "";
                    if (cat === "all") {
                        item.style.display = "block";
                    } else if (itemCats.includes(cat)) {
                        item.style.display = "block";
                    } else {
                        item.style.display = "none";
                    }
                });
            });
        });
    </script> -->

    <!--banner slider-->
    <script>
        $(document).ready(function () {

            $(".banner-slider").owlCarousel({
                items: 1,
                loop: true,
                margin: 0,
                nav: true,
                dots: true,
                autoplay: true,
                autoplayTimeout: 3000,
                autoplayHoverPause: true,
                animateOut: 'fadeOut',
                smartSpeed: 1000
            });
        });
    </script>

    <script>

        function addWishlist(button, productId) {

            let formData = new FormData();
            formData.append("product_id", productId);

            fetch("add_wishlist.php", {
                method: "POST",
                body: formData
            })
                .then(response => response.text())
                .then(result => {

                    result = result.trim();

                    if (result === "login") {

                        Swal.fire({
                            icon: "warning",
                            title: "Login Required!",
                            text: "Please login first to add products to your wishlist.",
                            showCancelButton: true,
                            confirmButtonText: "Login Now",
                            cancelButtonText: "Cancel",
                            confirmButtonColor: "#10b981"
                        }).then((res) => {

                            if (res.isConfirmed) {
                                window.location.href = "login.php";
                            }

                        });

                    }
                    else if (result === "added") {

                        button.querySelector("i").className = "fa-solid fa-heart";

                        Swal.fire({
                            icon: "success",
                            title: "Added to Wishlist!",
                            text: "Product added successfully.",
                            timer: 1500,
                            showConfirmButton: false
                        });

                    }
                    else if (result === "exists") {

                        Swal.fire({
                            icon: "info",
                            title: "Already Added!",
                            text: "This product is already in your wishlist."
                        });

                    }
                    else {

                        Swal.fire({
                            icon: "error",
                            title: "Something Went Wrong!"
                        });

                    }

                })
                .catch(error => {
                    console.log(error);
                });
        }

    </script>

    </body>

</html>

<?php
include "include/footer.php";
?>