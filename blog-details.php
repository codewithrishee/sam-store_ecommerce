<?php
include("config/db.php");
include("include/header.php");

// Check ID
if(!isset($_GET['id'])){
    echo "Blog not found";
    exit;
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

// Fetch blog
$res = mysqli_query($conn,"SELECT * FROM blogs WHERE id='$id'");
$data = mysqli_fetch_assoc($res);

if(!$data){
    echo "Blog not found";
    exit;
}
?>

<style>
.blog-container {
    max-width: 900px;
    margin: auto;
}

.blog-card {
    background: #fff;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
}

.blog-title {
    font-size: 32px;
    font-weight: 700;
}

.blog-date {
    color: #888;
    font-size: 14px;
}

.blog-img {
    width: 100%;
    height: 400px;
    object-fit: cover;
    border-radius: 12px;
    margin: 20px 0;
}

.blog-content {
    font-size: 17px;
    line-height: 1.9;
    color: #444;
}

.back-btn {
    display: inline-block;
    margin-bottom: 20px;
    text-decoration: none;
    color: #555;
}

.back-btn:hover {
    color: #000;
}

.share-box {
    margin-top: 30px;
    border-top: 1px solid #eee;
    padding-top: 15px;
}
.social-icons {
    display: flex;
    gap: 15px;
}

.icon {
    width: 45px;
    height: 45px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-size: 18px;
    color: #fff;
    transition: 0.3s;
}

.icon:hover {
    transform: scale(1.1);
}

/* Colors */
.whatsapp { background: #25D366; }
.facebook { background: #1877F2; }
.twitter { background: #1DA1F2; }
</style>

<div class="container mt-5 blog-container">

    <!-- Back Button -->
    <a href="javascript:history.back()" class="back-btn">← Back</a>

    <div class="blog-card">

        <!-- Title -->
        <h1 class="blog-title"><?php echo $data['title']; ?></h1>

        <!-- Date -->
        <p class="blog-date">
            📅 <?php echo date("d M Y", strtotime($data['created_at'])); ?>
        </p>

        <!-- Image -->
        <?php if(!empty($data['image'])){ ?>
            <img src="images/<?php echo $data['image']; ?>" class="blog-img">
        <?php } ?>

        <!-- Content -->
        <div class="blog-content">
            <?php echo nl2br($data['description']); ?>
        </div>

        <!-- Share -->
        <div class="share-box">
    <strong>Share this post:</strong>

    <div class="social-icons mt-3">

        <a href="https://wa.me/?text=<?php echo urlencode($data['title']); ?>" 
           target="_blank" class="icon whatsapp">
           <i class="fab fa-whatsapp"></i>
        </a>

        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('http://yourdomain.com/blog-detail.php?id='.$data['id']); ?>" 
           target="_blank" class="icon facebook">
           <i class="fab fa-facebook-f"></i>
        </a>

        <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode($data['title']); ?>" 
           target="_blank" class="icon twitter">
           <i class="fab fa-twitter"></i>
        </a>

    </div>
</div>
    </div>
</div>

<?php include("include/footer.php"); ?>