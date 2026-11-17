<?php
include "config/db.php";
include "include/header.php";
?>

<div class="container mt-5">
    <h3 class="mb-4">All Categories</h3>

    <div class="row">
    <?php
    $cat = $conn->query("SELECT * FROM categories ORDER BY name ASC");

    while($c = $cat->fetch_assoc()){
    ?>
        <div class="col-md-3 col-6 mb-4">
            <div class="card p-3 text-center shadow-sm">

                <a href="fetch_products.php?cat_id=<?= $c['id'] ?>" 
                   class="text-decoration-none text-dark">

                    <h5><?= htmlspecialchars($c['name']) ?></h5>

                </a>

            </div>
        </div>
    <?php } ?>
    </div>
</div>


<style>
    .cat-box {
    padding: 10px 15px;
    background: #f5f5f5;
    border-radius: 8px;
    display: inline-block;
    margin: 5px;
}

.cat-box:hover {
    background: #000;
    color: #fff;
}
</style>
<?php include "include/footer.php"; ?>