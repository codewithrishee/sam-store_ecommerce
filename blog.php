<?php
include "config/db.php";
include "include/header.php";
?>

<style>
  .blog-card {
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
    background: #fff;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

/*  CARD HOVER */
.blog-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

/* IMAGE */
.blog-card img {
    transition: transform 0.4s ease;
}

/*  IMAGE ZOOM */
.blog-card:hover img {
    transform: scale(1.08);
}

/* CONTENT */
.blog-content span {
    font-size: 13px;
    color: #888;
}

.blog-content h5 {
    font-weight: 600;
    margin: 8px 0;
    transition: color 0.3s ease;
}

/*  TITLE COLOR CHANGE */
.blog-card:hover h5 {
    color: #ff4d4d;
}

/* READ MORE */
.read-more {
    font-size: 14px;
    font-weight: 500;
    position: relative;
    display: inline-block;
    transition: 0.3s;
}

/*  UNDERLINE ANIMATION */
.read-more::after {
    content: "";
    position: absolute;
    width: 0%;
    height: 2px;
    background: #ff4d4d;
    left: 0;
    bottom: -2px;
    transition: 0.3s;
}

.blog-card:hover .read-more::after {
    width: 100%;
}
</style>

<link rel="stylesheet" href="style.css">


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
<section class="blog py-5">
  <div class="container">

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="feature-title">Latest Blog</h2>
    <!-- <a href="blog.php" class="view-btn">View All</a> -->
</div>

    <div class="row g-4">

<?php
$blogs = mysqli_query($conn,"
    SELECT * FROM blogs 
    ORDER BY created_at DESC
");


if(mysqli_num_rows($blogs) > 0){

while($row = mysqli_fetch_assoc($blogs)){

$image = !empty($row['image']) ? $row['image'] : "no-image.png";
$date  = !empty($row['created_at']) ? date("d M Y", strtotime($row['created_at'])) : "";
?>

<!-- BLOG ITEM -->
<div class="col-lg-4 col-md-6 col-12">

<div class="blog-card h-100">

<a href="blog-details.php?id=<?= $row['id']; ?>" class="text-decoration-none text-dark">

<img src="images/<?= $image; ?>" class="img-fluid">

<div class="blog-content p-3">
<span><?= $date; ?></span>
<h5><?= htmlspecialchars($row['title']); ?></h5>
<p class="read-more">Read More</p>
</div>

</a>

</div>

</div>

<?php 
} 

}else{
    echo "<p class='text-center'>No Blogs Found</p>";
}
?>

    </div>

  </div>
</section>

<?php include "include/footer.php"; ?>