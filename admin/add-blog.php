<link rel="stylesheet" href="admin-style.css">

<?php
include("../config/db.php");
include "header.php";
include "sidebar.php";

if (!$conn) { die("Database connection failed!"); }

/* ================= CONFIG ================= */
define('IMG_DIR', __DIR__ . '/../images/');
define('MAX_IMAGE_BYTES', 5 * 1024 * 1024); // 5 MB
$ALLOWED_EXT  = ['jpg', 'jpeg', 'png', 'webp'];
$ALLOWED_MIME = ['image/jpeg', 'image/png', 'image/webp'];

$errors  = [];
$success = false;

/* ================= ADD BLOG ================= */
if (isset($_POST['add_blog'])) {
    $title = trim($_POST['title'] ?? '');
    $desc  = trim($_POST['description'] ?? '');
    $date  = trim($_POST['date'] ?? '');
    if ($date === '') { $date = date('Y-m-d'); }

    if ($title === '' || $desc === '' || $date === '') {
        $errors[] = "Title, description and publish date are required.";
    }

    $imageName = '';
    if (empty($errors)) {
        if (empty($_FILES['image']['name'])) {
            $errors[] = "Please upload a blog image.";
        } else {
            $file = $_FILES['image'];

            if (!isset($file['error']) || is_array($file['error'])) {
                $errors[] = "Invalid upload payload.";
            } elseif ($file['error'] !== UPLOAD_ERR_OK) {
                $errors[] = "Upload error (code: " . (int)$file['error'] . ").";
            } elseif ($file['size'] <= 0 || $file['size'] > MAX_IMAGE_BYTES) {
                $errors[] = "Image is too large. Maximum size is 5 MB.";
            } elseif (!is_uploaded_file($file['tmp_name'])) {
                $errors[] = "Upload could not be verified.";
            } else {
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, $ALLOWED_EXT, true)) {
                    $errors[] = "Only JPG, JPEG, PNG and WEBP images are allowed.";
                } else {
                    $mime = null;
                    if (function_exists('mime_content_type')) {
                        $mime = @mime_content_type($file['tmp_name']);
                    }
                    if (!$mime) {
                        $info = @getimagesize($file['tmp_name']);
                        $mime = $info['mime'] ?? '';
                    }
                    if (!in_array($mime, $ALLOWED_MIME, true)) {
                        $errors[] = "Invalid image type. Only real JPG, JPEG, PNG and WEBP files are allowed.";
                    } elseif (@getimagesize($file['tmp_name']) === false) {
                        $errors[] = "File is not a valid image.";
                    } else {
                        $newName = time() . "_" . bin2hex(random_bytes(4)) . "." . $ext;
                        if (!@move_uploaded_file($file['tmp_name'], IMG_DIR . $newName)) {
                            $errors[] = "Unable to save the uploaded image.";
                        } else {
                            $imageName = $newName;
                        }
                    }
                }
            }
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare(
            "INSERT INTO blogs (title, description, image, created_at) VALUES (?, ?, ?, ?)"
        );
        if ($stmt) {
            $stmt->bind_param("ssss", $title, $desc, $imageName, $date);
            if ($stmt->execute()) {
                $stmt->close();
                echo "<script>alert('Blog Added Successfully'); window.location='view-blog.php';</script>";
                exit;
            } else {
                $errors[] = "Database error: " . $stmt->error;
                $stmt->close();
            }
        } else {
            $errors[] = "Prepare failed: " . $conn->error;
        }

        // DB failed – clean up the uploaded file
        if ($imageName !== '' && is_file(IMG_DIR . $imageName)) {
            @unlink(IMG_DIR . $imageName);
        }
    }
}
?>

<div class="main-content">
    <div class="page-card">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="page-title m-0">Add Blog</h3>
            <a href="view-blog.php" class="btn btn-light border rounded-pill px-4">Back to Blogs</a>
        </div>

        <?php if (!empty($errors)) { ?>
            <div class="alert alert-danger" style="border-radius:12px;">
                <?= implode('<br>', array_map('htmlspecialchars', $errors)); ?>
            </div>
        <?php } ?>

        <form method="POST" enctype="multipart/form-data" id="addBlogForm">
            <div class="row g-4">

                <!-- TITLE -->
                <div class="col-md-6">
                    <label class="form-label">Blog Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control"
                           placeholder="Enter blog title"
                           value="<?= htmlspecialchars($_POST['title'] ?? ''); ?>" required>
                </div>

                <!-- DATE -->
                <div class="col-md-6">
                    <label class="form-label">Publish Date <span class="text-danger">*</span></label>
                    <input type="date" name="date" class="form-control"
                           value="<?= htmlspecialchars($_POST['date'] ?? date('Y-m-d')); ?>" required>
                </div>

                <!-- DESCRIPTION -->
                <div class="col-12">
                    <label class="form-label">Description <span class="text-danger">*</span></label>
                    <textarea name="description" class="form-control" rows="7"
                              placeholder="Write blog description..." required><?= htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                </div>

                <!-- IMAGE -->
                <div class="col-md-6">
                    <label class="form-label">Upload Image <span class="text-danger">*</span></label>
                    <input type="file" name="image" id="blogImage" class="form-control"
                           accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" required>
                    <small class="text-muted d-block mt-1" style="font-size:0.75rem;">
                        Allowed: JPG, JPEG, PNG, WEBP &middot; Max 5 MB.
                    </small>
                </div>

                <!-- PREVIEW -->
                <div class="col-md-6">
                    <label class="form-label">Image Preview</label>
                    <div>
                        <img id="preview"
                             style="width:240px;height:160px;object-fit:cover;border-radius:12px;border:1px solid #efe6ec;display:none;"
                             alt="Preview">
                    </div>
                </div>

                <!-- BUTTON -->
                <div class="col-12 text-end mt-2">
                    <button type="submit" name="add_blog" class="btn-admin">
                        <i class="fa-solid fa-plus me-2"></i> Add Blog
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('blogImage').addEventListener('change', function (e) {
    var file = e.target.files[0];
    var preview = document.getElementById('preview');
    if (!file) { preview.style.display = 'none'; preview.src = ''; return; }
    if (file.size > 5 * 1024 * 1024) {
        alert('Image is larger than 5 MB.');
        e.target.value = '';
        preview.style.display = 'none';
        return;
    }
    var reader = new FileReader();
    reader.onload = function (ev) {
        preview.src = ev.target.result;
        preview.style.display = 'block';
    };
    reader.readAsDataURL(file);
});
</script>