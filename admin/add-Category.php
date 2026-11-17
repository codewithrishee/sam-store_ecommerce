<link rel="stylesheet" href="admin-style.css">

<?php
include("../config/db.php");
include "header.php";
include "sidebar.php";

if(isset($_POST['add_category'])){
    $name = trim($_POST['category_name']);

    // ================= VALIDATION =================
    if(empty($name)){
        echo "<script>alert('Category name required');</script>";
        exit;
    }

    // ================= IMAGE =================
    $image_name = "";
    if(!empty($_FILES['image']['name'])){
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp'];

        if(in_array($ext, $allowed)){
            $image_name = time()."_".rand(1000,9999).".".$ext;
            $tmp = $_FILES['image']['tmp_name'];
            if(!move_uploaded_file($tmp, "../images/".$image_name)){
                echo "<script>alert('Image Upload Failed');</script>";
                exit;
            }
        } else {
            echo "<script>alert('Invalid Image Format');</script>";
            exit;
        }
    }

    // ================= DUPLICATE CHECK =================
    $stmt = $conn->prepare("SELECT id FROM categories WHERE name=?");
    $stmt->bind_param("s",$name);
    $stmt->execute();
    $res = $stmt->get_result();

    if($res->num_rows > 0){
        echo "<script>alert('Category already exists');</script>";
        exit;
    }

    // ================= INSERT =================
    $stmt2 = $conn->prepare("INSERT INTO categories (name, image) VALUES (?, ?)");
    $stmt2->bind_param("ss", $name, $image_name);

    if($stmt2->execute()){
        echo "<script>alert('Category Added Successfully'); window.location='view-category.php';</script>";
    } else {
        echo "<script>alert('Error: ".$stmt2->error."');</script>";
    }
    $stmt2->close();
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
    .form-control { border-radius: 12px; border: 1.5px solid #efe6ec; padding: 12px 16px; box-shadow: none; font-size: 0.9rem; color: #2e3254; background-color: #fafbfc; transition: all 0.3s; }
    .form-control:focus { border-color: #ff4d6d; box-shadow: 0 0 0 4px #fff2f5; background-color: #fff; }
    
    /* Image Preview Box */
    .preview-box { width: 100%; max-width: 250px; height: 160px; border: 2px dashed #efe6ec; border-radius: 16px; display: flex; align-items: center; justify-content: center; background: #fafbfc; overflow: hidden; margin-top: 10px; }
    .preview-img { width: 100%; height: 100%; object-fit: cover; display: none; }
    .preview-placeholder { color: #a0a5b8; font-size: 0.85rem; text-align: center; }
    
    /* Button */
    .btn-admin { background: linear-gradient(135deg, #ff4d6d, #e63950); color: #fff; border: none; padding: 12px 28px; border-radius: 999px; font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 0.9rem; letter-spacing: 0.05em; text-transform: uppercase; box-shadow: 0 8px 20px rgba(255, 77, 109, 0.25); transition: all 0.3s; display: inline-flex; align-items: center; gap: 8px;}
    .btn-admin:hover { transform: translateY(-2px); box-shadow: 0 12px 25px rgba(255, 77, 109, 0.35); color: #fff; }
    
    @media (max-width: 991px) { .main-content { margin-left: 85px; padding: 25px 20px; margin-top: 65px; } .page-card { padding: 25px; } }
</style>

<!-- MAIN CONTENT -->
<div class="main-content">

    <div class="page-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="page-title m-0">Add New Category</h3>
            <a href="view-category.php" class="btn btn-light border rounded-pill px-4">Back to List</a>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <div class="row g-4">
                
                <!-- CATEGORY NAME -->
                <div class="col-md-12">
                    <label class="form-label">Category Name</label>
                    <input type="text" name="category_name" class="form-control" placeholder="e.g. Dog Food, Toys, Accessories" required>
                </div>

                <!-- IMAGE UPLOAD -->
                <div class="col-md-12">
                    <label class="form-label">Category Image</label>
                    <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp" required id="imageInput">
                    
                    <!-- Preview Box -->
                    <div class="preview-box" id="previewContainer">
                        <div class="preview-placeholder" id="placeholderText">
                            <i class="fa-solid fa-cloud-arrow-up fs-4 mb-2"></i><br>
                            Image Preview
                        </div>
                        <img id="preview" class="preview-img">
                    </div>
                </div>

                <!-- SUBMIT BUTTON -->
                <div class="col-12 mt-4">
                    <button type="submit" name="add_category" class="btn-admin">
                        <i class="fa-solid fa-plus-circle"></i> Add Category
                    </button>
                </div>

            </div>
        </form>
    </div>

</div>

<!-- IMAGE PREVIEW SCRIPT -->
<script>
document.getElementById("imageInput").addEventListener("change", function(e){
    const file = e.target.files[0];
    const preview = document.getElementById("preview");
    const placeholder = document.getElementById("placeholderText");

    if(file){
        const reader = new FileReader();
        reader.onload = function(e){
            preview.src = e.target.result;
            preview.style.display = "block";
            placeholder.style.display = "none";
        }
        reader.readAsDataURL(file);
    } else {
        preview.style.display = "none";
        placeholder.style.display = "block";
    }
});
</script>