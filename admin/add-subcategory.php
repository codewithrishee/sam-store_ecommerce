<link rel="stylesheet" href="admin-style.css">

<?php
include("../config/db.php");
include("header.php");
include("sidebar.php");

/* ================= ADD SUB CATEGORY ================= */
if(isset($_POST['add_sub_cat'])){
    $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
    $sub_name = mysqli_real_escape_string($conn, $_POST['sub_name']);
    $status = 1;

    // VALIDATION
    if(empty($category_id) || empty($sub_name)){
        echo "<script>alert('All fields are required');</script>";
        exit;
    }

    // DUPLICATE CHECK
    $check = mysqli_query($conn, "SELECT id FROM sub_categories WHERE name='$sub_name'");
    if(mysqli_num_rows($check) > 0){
        echo "<script>alert('Sub Category Already Exists');</script>";
        exit;
    }

    // INSERT
    $sql = "INSERT INTO sub_categories (category_id, name, status) VALUES ('$category_id', '$sub_name', '$status')";

    if(mysqli_query($conn, $sql)){
        echo "<script>alert('Sub Category Added Successfully'); window.location.href='view-subcategory.php';</script>";
    } else {
        echo "<script>alert('Error: ".mysqli_error($conn)."');</script>";
    }
}
?>

<style>
    /* Layout & Theme */
    body { background: #f4f7f6; font-family: 'Manrope', sans-serif; }
    .main-content { margin-left: 290px; padding: 35px 40px; margin-top: 20px; min-height: calc(100vh - 75px); }
    
    .page-card { background: #fff; border-radius: 20px; padding: 35px; border: 1px solid #efe6ec; box-shadow: 0 4px 15px rgba(46, 50, 84, 0.03); max-width: 800px; }
    .page-title { font-family: 'Outfit', sans-serif; font-weight: 800; color: #2e3254; margin-bottom: 25px; font-size: 1.5rem;}
    
    /* Form Controls */
    .form-label { font-family: 'Outfit', sans-serif; font-size: 0.85rem; font-weight: 700; color: #2e3254; margin-bottom: 8px; }
    .form-control, .form-select { border-radius: 12px; border: 1.5px solid #efe6ec; padding: 12px 16px; box-shadow: none; font-size: 0.9rem; color: #2e3254; background-color: #fafbfc; transition: all 0.3s; }
    .form-control:focus, .form-select:focus { border-color: #ff4d6d; box-shadow: 0 0 0 4px #fff2f5; background-color: #fff; }
    
    /* Button */
    .btn-admin { background: linear-gradient(135deg, #ff4d6d, #e63950); color: #fff; border: none; padding: 12px 28px; border-radius: 999px; font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 0.9rem; letter-spacing: 0.05em; text-transform: uppercase; box-shadow: 0 8px 20px rgba(255, 77, 109, 0.25); transition: all 0.3s; display: inline-flex; align-items: center; gap: 8px;}
    .btn-admin:hover { transform: translateY(-2px); box-shadow: 0 12px 25px rgba(255, 77, 109, 0.35); color: #fff; }
    
    @media (max-width: 991px) { .main-content { margin-left: 85px; padding: 25px 20px; margin-top: 65px; } .page-card { padding: 25px; } }
</style>

<!-- MAIN CONTENT -->
<div class="main-content">

    <div class="page-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="page-title m-0">Add New Sub Category</h3>
            <a href="view-subcategory.php" class="btn btn-light border rounded-pill px-4">Back to List</a>
        </div>

        <form action="" method="POST">
            <div class="row g-4">
                
                <!-- CATEGORY -->
                <div class="col-md-6">
                    <label class="form-label">Select Main Category</label>
                    <select name="category_id" class="form-select" required>
                        <option value="">-- Choose Category --</option>
                        <?php
                        $res = mysqli_query($conn, "SELECT * FROM categories WHERE status = 1 ORDER BY id DESC");
                        while($row = mysqli_fetch_assoc($res)){
                        ?>
                        <option value="<?= $row['id']; ?>">
                            <?= htmlspecialchars($row['name']); ?>
                        </option>
                        <?php } ?>
                    </select>
                </div>

                <!-- SUB CATEGORY -->
                <div class="col-md-6">
                    <label class="form-label">Sub Category Name</label>
                    <input type="text" name="sub_name" class="form-control" placeholder="Enter sub category name" required>
                </div>

                <!-- BUTTON -->
                <div class="col-12 mt-4">
                    <button type="submit" name="add_sub_cat" class="btn-admin">
                        <i class="fa-solid fa-plus-circle"></i> Save Sub Category
                    </button>
                </div>

            </div>
        </form>
    </div>

</div>