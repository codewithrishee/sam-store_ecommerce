<?php
include("../config/db.php");
include("header.php");
include("sidebar.php");

/* ================= SECURE FILE UPLOAD HELPER ================= */
function uploadBannerImage($file, $prefix = 'desk') {
    if (empty($file['name'])) return null;

    $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $mime = mime_content_type($file['tmp_name']);

    if (!in_array($ext, $allowedExts) || !in_array($mime, $allowedMimes)) {
        return "INVALID_TYPE";
    }
    if ($file['size'] > $maxSize) {
        return "TOO_LARGE";
    }

    $newName = time() . "_" . $prefix . "_" . rand(1000, 9999) . "." . $ext;
    $targetPath = "../images/" . $newName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $newName;
    }
    return "UPLOAD_FAILED";
}

/* ================= ADD BANNER ================= */
if (isset($_POST['add_banner'])) {
    $title = trim($_POST['title']);
    $type = $_POST['type'];
    $page = $_POST['page'];
    $heading = trim($_POST['heading']);
    $subheading = trim($_POST['subheading']);
    $button_text = trim($_POST['button_text']);
    $button_link = trim($_POST['button_link']);
    $button_color = $_POST['button_color'];
    $status = (int)$_POST['status'];
    $display_order = (int)$_POST['display_order'];

    $image_name = null;
    $mobile_image_name = null;

    // Validate Desktop Image
    if (empty($_FILES['image']['name'])) {
        echo "<script>alert('Desktop Banner Image is required.');</script>";
    } else {
        $upload = uploadBannerImage($_FILES['image'], 'desk');
        if ($upload === "INVALID_TYPE") {
            echo "<script>alert('Invalid Desktop Image Format (JPG, PNG, WEBP only).');</script>";
            $error = true;
        } elseif ($upload === "TOO_LARGE") {
            echo "<script>alert('Desktop Image is too large (Max 5MB).');</script>";
            $error = true;
        } elseif ($upload === "UPLOAD_FAILED") {
            echo "<script>alert('Failed to upload Desktop Image.');</script>";
            $error = true;
        } else {
            $image_name = $upload;
        }
    }

    // Validate Mobile Image (Optional)
    if (!empty($_FILES['mobile_image']['name'])) {
        $upload = uploadBannerImage($_FILES['mobile_image'], 'mob');
        if ($upload === "INVALID_TYPE") {
            echo "<script>alert('Invalid Mobile Image Format.');</script>";
            $error = true;
        } elseif ($upload === "TOO_LARGE") {
            echo "<script>alert('Mobile Image is too large (Max 5MB).');</script>";
            $error = true;
        } elseif ($upload === "UPLOAD_FAILED") {
            echo "<script>alert('Failed to upload Mobile Image.');</script>";
            $error = true;
        } else {
            $mobile_image_name = $upload;
        }
    }

    // Proceed if no errors
    if (!isset($error) && $image_name) {
        $stmt = $conn->prepare("
            INSERT INTO banners (title, type, page, heading, subheading, button_text, button_link, button_color, image, mobile_image, status, display_order) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "ssssssssssii", 
            $title, $type, $page, $heading, $subheading, $button_text, $button_link, $button_color, 
            $image_name, $mobile_image_name, $status, $display_order
        );

        if ($stmt->execute()) {
            echo "<script>alert('Banner Added Successfully'); window.location='view_banner.php';</script>";
        } else {
            echo "<script>alert('Database Error: " . $stmt->error . "');</script>";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Banner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="admin-style.css">
    <style>
        body { background: #f4f7f6; font-family: 'Manrope', sans-serif; }
        /* Strict layout constraints to prevent navbar/sidebar overlap */
        .main-content { margin-left: 290px; padding: 30px 40px; margin-top: 20px; min-height: calc(100vh - 75px); }
        
        .page-card { background: #fff; border-radius: 20px; padding: 30px 40px; border: 1px solid #efe6ec; box-shadow: 0 4px 15px rgba(46, 50, 84, 0.03); }
        .page-title { font-family: 'Outfit', sans-serif; font-weight: 800; color: #2e3254; margin-bottom: 25px; }
        
        .form-label { font-family: 'Outfit', sans-serif; font-size: 0.85rem; font-weight: 700; color: #2e3254; margin-bottom: 8px; }
        .form-control, .form-select { border-radius: 12px; border: 1.5px solid #efe6ec; padding: 12px 16px; box-shadow: none; font-size: 0.9rem; color: #2e3254; background-color: #fafbfc; }
        .form-control:focus, .form-select:focus { border-color: #ff4d6d; box-shadow: 0 0 0 4px #fff2f5; background-color: #fff; }
        
        input[type="color"].form-control { padding: 4px; height: 48px; cursor: pointer; }
        .preview-box { display: flex; gap: 15px; margin-top: 10px; }
        .preview-img { max-width: 100%; height: 100px; object-fit: cover; border-radius: 8px; border: 2px dashed #efe6ec; display: none; }
        
        .btn-admin { background: linear-gradient(135deg, #ff4d6d, #e63950); color: #fff; border: none; padding: 12px 28px; border-radius: 999px; font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 0.9rem; box-shadow: 0 8px 20px rgba(255, 77, 109, 0.25); transition: all 0.3s; }
        .btn-admin:hover { transform: translateY(-2px); box-shadow: 0 12px 25px rgba(255, 77, 109, 0.35); color: #fff; }
        
        @media (max-width: 991px) { .main-content { margin-left: 85px; padding: 20px; margin-top: 65px; } .page-card { padding: 20px; } }
    </style>
</head>
<body>

<div class="main-content">
    <div class="page-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="page-title m-0">Add New Banner</h3>
            <a href="view_banner.php" class="btn btn-light border rounded-pill px-4">Back to List</a>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <!-- TITLE & TYPE -->
                <div class="col-md-4 mb-3">
                    <label class="form-label">Banner Title/Name</label>
                    <input type="text" name="title" class="form-control" placeholder="e.g. Summer Sale Hero" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Banner Type</label>
                    <select name="type" class="form-select" required>
                        <option value="">Select Type</option>
                        <option value="hero">Hero Slider</option>
                        <option value="promo">Promo Banner</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Show On Page</label>
                    <select name="page" class="form-select" required>
                        <option value="home">Home</option>
                        <option value="shop">Shop</option>
                        <option value="both">Both</option>
                    </select>
                </div>

                <!-- HEADINGS -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Heading (Hero Text)</label>
                    <input type="text" name="heading" class="form-control" placeholder="e.g. Get 50% Off Today">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Sub Heading</label>
                    <input type="text" name="subheading" class="form-control" placeholder="e.g. Limited time offer on pet food">
                </div>

                <!-- BUTTON -->
                <div class="col-md-3 mb-3">
                    <label class="form-label">Button Text</label>
                    <input type="text" name="button_text" class="form-control" placeholder="Shop Now">
                </div>
                <div class="col-md-5 mb-3">
                    <label class="form-label">Button Link</label>
                    <input type="text" name="button_link" class="form-control" placeholder="shop.php">
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">Button Color</label>
                    <input type="color" name="button_color" class="form-control form-control-color w-100" value="#ff4d6d">
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">Display Order</label>
                    <input type="number" name="display_order" class="form-control" value="0">
                </div>

                <!-- IMAGES -->
                <div class="col-md-6 mb-4">
                    <label class="form-label">Desktop Banner Image (Required)</label>
                    <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp" onchange="previewImage(event, 'deskPreview')" required>
                    <img id="deskPreview" class="preview-img mt-3" style="width: 200px; height: 100px;">
                </div>
                <div class="col-md-6 mb-4">
                    <label class="form-label">Mobile Banner Image (Optional)</label>
                    <input type="file" name="mobile_image" class="form-control" accept="image/jpeg,image/png,image/webp" onchange="previewImage(event, 'mobPreview')">
                    <img id="mobPreview" class="preview-img mt-3" style="width: 100px; height: 100px;">
                </div>

                <!-- STATUS -->
                <div class="col-md-4 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-3 mt-3">
                <button type="reset" class="btn btn-light border rounded-pill px-4">Reset</button>
                <button type="submit" name="add_banner" class="btn-admin">Save Banner</button>
            </div>
        </form>
    </div>
</div>

<script>
function previewImage(event, previewId) {
    const input = event.target;
    const preview = document.getElementById(previewId);
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = "block";
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.style.display = "none";
    }
}
</script>
</body>
</html>