<?php 
include ("../config/db.php"); 
include "header.php";
include "sidebar.php";

/* ================= INSERT ================= */

if(isset($_POST['submit'])){

    $name   = $_POST['product_name'];
    $price  = $_POST['price'];
    $cat_id = $_POST['category_id'];
    $status = $_POST['status'];
    $desc   = $_POST['description'];

    $image = time() . "_" . $_FILES['image']['name'];
    $tmp   = $_FILES['image']['tmp_name'];

    move_uploaded_file($tmp,"../images/".$image);

    mysqli_query($conn,"INSERT INTO products
        (category_id,product_name,price,image,description,status)
        VALUES ('$cat_id','$name','$price','$image','$desc','$status')");

    echo "<script>alert('Product Added Successfully');</script>";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-dark text-white">
            <h4 class="mb-0">Add New Product</h4>
        </div>

        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">

                <div class="row">

                    <!-- Product Name -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Product Name</label>
                        <input type="text" name="product_name" class="form-control" required>
                    </div>

                    <!-- Price -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Price</label>
                        <input type="number" name="price" class="form-control" required>
                    </div>

                    <!-- Category -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Select Category</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">Choose Category</option>
                            <?php
                            $cat = mysqli_query($conn,"SELECT * FROM categories WHERE status=1");
                            while($c = mysqli_fetch_assoc($cat)){
                            ?>
                            <option value="<?= $c['id']; ?>">
                                <?= $c['category_name']; ?> (<?= $c['section']; ?>)
                            </option>
                            <?php } ?>
                        </select>
                    </div>

                    <!-- Status -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                    <!-- Image -->
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Product Image</label>
                        <input type="file" name="image" class="form-control" required>
                    </div>

                    <!-- Description -->
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4"></textarea>
                    </div>

                </div>

                <button type="submit" name="submit" class="btn btn-success">
                    Add Product
                </button>

            </form>
        </div>
    </div>
</div>

</body>
</html>