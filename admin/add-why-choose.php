
<?php
include("../config/db.php");

$error = "";

function uploadWhyChooseImage($file) {
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Please select a valid image.'];
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        return ['success' => false, 'message' => 'Image size must be under 5MB.'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
        return ['success' => false, 'message' => 'Only JPG, JPEG, PNG and WEBP images are allowed.'];
    }

    $mime = mime_content_type($file['tmp_name']);
    $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];

    if (!in_array($mime, $allowedMime)) {
        return ['success' => false, 'message' => 'Invalid image file.'];
    }

    $imageName = time() . "_" . bin2hex(random_bytes(5)) . "." . $ext;
    $destination = "../images/" . $imageName;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => false, 'message' => 'Image upload failed.'];
    }

    return ['success' => true, 'image' => $imageName];
}

if (isset($_POST['add'])) {

    $title = trim($_POST['title'] ?? '');
    $desc = trim($_POST['description'] ?? '');

    if ($title === '' || $desc === '') {
        $error = "Please fill all required fields.";
    } else {

        $upload = uploadWhyChooseImage($_FILES['image'] ?? null);

        if (!$upload['success']) {
            $error = $upload['message'];
        } else {

            $image = $upload['image'];

            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO why_choose (title, description, image) VALUES (?, ?, ?)"
            );

            mysqli_stmt_bind_param($stmt, "sss", $title, $desc, $image);

            if (mysqli_stmt_execute($stmt)) {
                echo "<script>alert('Why Choose item added successfully!');window.location='view-why-choose.php';</script>";
                exit;
            } else {
                if (file_exists("../images/" . $image)) {
                    unlink("../images/" . $image);
                }
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}

include("header.php");
include("sidebar.php");
?>

<link rel="stylesheet" href="admin-style.css">

<style>
.wc-page{padding:25px;color:#293044}
.wc-heading{margin-bottom:25px}
.wc-heading h2{font-size:25px;font-weight:800;margin:0;color:#252d40}
.wc-heading p{font-size:13px;color:#9299a8;margin:7px 0 0}
.wc-card{background:#fff;border:1px solid #f0edf2;border-radius:20px;box-shadow:0 8px 30px rgba(35,25,45,.045);overflow:hidden}
.wc-card-header{padding:22px 25px;border-bottom:1px solid #f1edf2}
.wc-card-header h3{font-size:18px;font-weight:800;margin:0;color:#252d40}
.wc-card-header p{font-size:12px;color:#969baa;margin:5px 0 0}
.wc-form{padding:25px}
.wc-form label{display:block;font-size:13px;font-weight:700;color:#434b5e;margin-bottom:9px}
.wc-form .form-control{border:1px solid #e9e6ec;border-radius:11px;padding:12px 14px;font-size:13px;box-shadow:none;background:#fcfbfd}
.wc-form .form-control:focus{border-color:#e95b91;background:#fff;box-shadow:0 0 0 3px rgba(233,91,145,.10)}
.wc-form textarea{resize:vertical}
.wc-help{font-size:11px;color:#9a9eaa;margin-top:7px}
.wc-preview{display:none;width:190px;height:135px;object-fit:cover;border-radius:13px;margin-top:14px;border:3px solid white;box-shadow:0 4px 15px rgba(0,0,0,.12)}
.wc-primary{background:linear-gradient(135deg,#ec5b91,#d83c7b);border:0;color:white;border-radius:11px;padding:12px 24px;font-size:13px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;gap:7px;transition:.25s}
.wc-primary:hover{color:white;transform:translateY(-2px);box-shadow:0 6px 15px rgba(216,60,123,.22)}
.wc-secondary{background:#f1f2f5;color:#555d6e;border-radius:11px;padding:12px 22px;font-size:13px;font-weight:700;text-decoration:none}
.wc-alert{padding:13px 16px;border-radius:11px;background:#fff0f0;color:#d34444;font-size:13px;margin:20px 25px 0}
@media(max-width:768px){.wc-page{padding:15px}.wc-heading h2{font-size:21px}.wc-form{padding:18px}.wc-card-header{padding:18px}}
.main-content {
    padding-top: 90px !important;
}
</style>

<div class="main-content">
<div class="wc-page">

    <div class="wc-heading">
        <h2>Why Choose Us</h2>
        <p>Add a new item to your Why Choose Us section</p>
    </div>

    <div class="wc-card">

        <div class="wc-card-header">
            <h3>Add Why Choose Item</h3>
            <p>Enter the item details and upload an image</p>
        </div>

        <?php if ($error): ?>
            <div class="wc-alert"><?= htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="wc-form">

            <div class="row">

                <div class="col-md-6 mb-4">
                    <label>Title *</label>
                    <input type="text" name="title" class="form-control"
                           placeholder="Enter title" required
                           value="<?= htmlspecialchars($_POST['title'] ?? ''); ?>">
                </div>

                <div class="col-md-6 mb-4">
                    <label>Upload Image *</label>
                    <input type="file" name="image" id="wcImage"
                           class="form-control" accept=".jpg,.jpeg,.png,.webp" required>
                    <div class="wc-help">JPG, PNG, WEBP | Maximum 5MB</div>
                    <img src="" id="wcPreview" class="wc-preview" alt="Image preview">
                </div>

                <div class="col-12 mb-4">
                    <label>Description *</label>
                    <textarea name="description" class="form-control" rows="5"
                              placeholder="Write description..." required><?= htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                </div>

                <div class="col-12 d-flex gap-3 flex-wrap">
                    <button type="submit" name="add" class="wc-primary">
                        + Add Item
                    </button>

                    <a href="view-why-choose.php" class="wc-secondary">
                        View All Items
                    </a>
                </div>

            </div>
        </form>
    </div>

</div>
</div>

<script>
document.getElementById('wcImage')?.addEventListener('change', function () {
    const file = this.files[0];
    const preview = document.getElementById('wcPreview');

    if (!file) {
        preview.style.display = 'none';
        return;
    }

    if (!['image/jpeg','image/png','image/webp'].includes(file.type)) {
        alert('Only JPG, PNG and WEBP images are allowed.');
        this.value = '';
        preview.style.display = 'none';
        return;
    }

    if (file.size > 5 * 1024 * 1024) {
        alert('Image must be under 5MB.');
        this.value = '';
        preview.style.display = 'none';
        return;
    }

    preview.src = URL.createObjectURL(file);
    preview.style.display = 'block';
});
</script>
