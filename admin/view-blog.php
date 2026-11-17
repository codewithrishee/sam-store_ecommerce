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

$errors = [];

/* ================= DELETE ================= */
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    $stmt = $conn->prepare("SELECT image FROM blogs WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $imgData = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($imgData) {
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("DELETE FROM blogs WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            $conn->commit();

            if (!empty($imgData['image']) && is_file(IMG_DIR . $imgData['image'])) {
                // Only remove file after DB commit succeeds
                @unlink(IMG_DIR . $imgData['image']);
            }

            echo "<script>alert('Blog Deleted'); window.location='view-blog.php';</script>";
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            die("Delete Error: " . htmlspecialchars($e->getMessage()));
        }
    } else {
        echo "<script>alert('Blog not found.'); window.location='view-blog.php';</script>";
        exit;
    }
}

/* ================= FETCH EDIT ================= */
$editData = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM blogs WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $editData = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

/* ================= UPDATE ================= */
if (isset($_POST['update_blog'])) {
    $id    = (int)$_POST['id'];
    $title = trim($_POST['title'] ?? '');
    $desc  = trim($_POST['description'] ?? '');
    $date  = trim($_POST['date'] ?? '');

    if ($date === '') { $date = date('Y-m-d'); }

    if ($title === '' || $desc === '' || $date === '') {
        $errors[] = "Title, description and publish date are required.";
    }

    // Current image
    $image = '';
    $stmt = $conn->prepare("SELECT image FROM blogs WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$row) {
        $errors[] = "Blog not found.";
    } else {
        $image = $row['image'] ?? '';
    }

    $newUploadedName = '';
    $replaceImage    = false;

    if (empty($errors) && !empty($_FILES['image']['name'])) {
        $file = $_FILES['image'];

        if (!isset($file['error']) || is_array($file['error'])) {
            $errors[] = "Invalid upload payload.";
        } elseif ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Upload error (code: " . (int)$file['error'] . ").";
        } elseif ($file['size'] <= 0 || $file['size'] > MAX_IMAGE_BYTES) {
            $errors[] = "Replacement image is too large. Maximum size is 5 MB.";
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
                    $candidate = time() . "_" . bin2hex(random_bytes(4)) . "." . $ext;
                    if (!@move_uploaded_file($file['tmp_name'], IMG_DIR . $candidate)) {
                        $errors[] = "Unable to save the uploaded image.";
                    } else {
                        $newUploadedName = $candidate;
                        $replaceImage    = true;
                    }
                }
            }
        }
    }

    if (empty($errors)) {
        $updateImage = $replaceImage ? $newUploadedName : $image;

        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare(
                "UPDATE blogs SET title = ?, description = ?, created_at = ?, image = ? WHERE id = ?"
            );
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            $stmt->bind_param("ssssi", $title, $desc, $date, $updateImage, $id);
            if (!$stmt->execute()) {
                throw new Exception("Update failed: " . $stmt->error);
            }
            $stmt->close();

            $conn->commit();

            // Delete the OLD image only after a successful commit
            if ($replaceImage && !empty($image) && is_file(IMG_DIR . $image)) {
                @unlink(IMG_DIR . $image);
            }

            echo "<script>alert('Blog Updated Successfully'); window.location='view-blog.php';</script>";
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            if ($newUploadedName !== '' && is_file(IMG_DIR . $newUploadedName)) {
                @unlink(IMG_DIR . $newUploadedName);
            }
            $errors[] = $e->getMessage();
        }
    } elseif ($newUploadedName !== '' && is_file(IMG_DIR . $newUploadedName)) {
        @unlink(IMG_DIR . $newUploadedName);
    }

    // If validation or DB failed, keep showing the form with errors
    if (empty($editData)) {
        $stmt = $conn->prepare("SELECT * FROM blogs WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $editData = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
}
?>

<div class="main-content">

    <?php if (!empty($errors)) { ?>
        <div class="alert alert-danger" style="border-radius:12px;">
            <?= implode('<br>', array_map('htmlspecialchars', $errors)); ?>
        </div>
    <?php } ?>

    <!-- ================= EDIT CARD ================= -->
    <?php if ($editData) { ?>
    <div class="page-card mb-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="page-title m-0">Edit Blog</h3>
            <a href="view-blog.php" class="btn btn-light border rounded-pill px-4">Cancel Edit</a>
        </div>

        <form method="POST" enctype="multipart/form-data" id="editBlogForm">
            <input type="hidden" name="id" value="<?= (int)$editData['id']; ?>">

            <div class="row g-4">

                <!-- TITLE -->
                <div class="col-md-6">
                    <label class="form-label">Blog Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control"
                           value="<?= htmlspecialchars($editData['title']); ?>" required>
                </div>

                <!-- DATE -->
                <div class="col-md-6">
                    <label class="form-label">Publish Date <span class="text-danger">*</span></label>
                    <input type="date" name="date" class="form-control"
                           value="<?= htmlspecialchars(date('Y-m-d', strtotime($editData['created_at']))); ?>" required>
                </div>

                <!-- DESCRIPTION -->
                <div class="col-12">
                    <label class="form-label">Description <span class="text-danger">*</span></label>
                    <textarea name="description" class="form-control" rows="7" required><?= htmlspecialchars($editData['description']); ?></textarea>
                </div>

                <!-- NEW IMAGE -->
                <div class="col-md-6">
                    <label class="form-label">Replace Image (optional)</label>
                    <input type="file" name="image" id="editBlogImage" class="form-control"
                           accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                    <small class="text-muted d-block mt-1" style="font-size:0.75rem;">
                        Allowed: JPG, JPEG, PNG, WEBP &middot; Max 5 MB. Leave empty to keep current image.
                    </small>
                </div>

                <!-- CURRENT + PREVIEW -->
                <div class="col-md-6">
                    <label class="form-label">Current Image</label>
                    <div>
                        <img id="currentPreview"
                             src="../images/<?= rawurlencode($editData['image']); ?>"
                             style="width:220px;height:150px;object-fit:cover;border-radius:12px;border:1px solid #efe6ec;display:<?= !empty($editData['image']) ? 'block' : 'none'; ?>;"
                             alt="Current image">
                    </div>
                </div>

                <!-- BUTTON -->
                <div class="col-12 d-flex gap-3 mt-2">
                    <button type="submit" name="update_blog" class="btn-admin">
                        <i class="fa-solid fa-check-circle me-2"></i> Update Blog
                    </button>
                    <a href="view-blog.php" class="btn btn-secondary rounded-4 px-4 py-3">Cancel</a>
                </div>

            </div>
        </form>
    </div>
    <?php } ?>

    <!-- ================= TABLE CARD ================= -->
    <div class="table-card">
        <div class="p-4 border-bottom d-flex justify-content-between align-items-center bg-white">
            <h3 class="page-title m-0">All Blogs</h3>
            <a href="add-blog.php" class="btn-admin text-decoration-none" style="font-size:0.8rem;padding:10px 20px;">
                <i class="fa-solid fa-plus"></i> Add New Blog
            </a>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th width="5%">#</th>
                        <th width="10%">Image</th>
                        <th width="20%">Title</th>
                        <th width="35%">Description</th>
                        <th width="15%">Publish Date</th>
                        <th width="15%">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $conn->query("SELECT * FROM blogs ORDER BY id DESC");
                    $i = 1;
                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $shortDesc = mb_strlen($row['description']) > 90
                                ? mb_substr($row['description'], 0, 90) . '…'
                                : $row['description'];
                    ?>
                    <tr>
                        <td class="fw-bold text-muted"><?= $i++; ?></td>
                        <td>
                            <?php if (!empty($row['image'])) { ?>
                                <img src="../images/<?= rawurlencode($row['image']); ?>"
                                     style="width:70px;height:70px;object-fit:cover;border-radius:12px;border:1px solid #efe6ec;">
                            <?php } else { ?>
                                <span class="text-muted small">No image</span>
                            <?php } ?>
                        </td>
                        <td class="fw-semibold"><?= htmlspecialchars($row['title']); ?></td>
                        <td class="text-muted" style="font-size:0.85rem;"><?= htmlspecialchars($shortDesc); ?></td>
                        <td><?= htmlspecialchars(date('d M Y', strtotime($row['created_at']))); ?></td>
                        <td>
                            <div class="d-flex gap-2">
                                <a href="?edit=<?= (int)$row['id']; ?>" class="btn-edit text-decoration-none" title="Edit">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <a href="?delete=<?= (int)$row['id']; ?>" class="btn-delete text-decoration-none"
                                   title="Delete"
                                   onclick="return confirm('Delete this blog? The image will also be removed.');">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php
                        }
                    } else {
                        echo '<tr><td colspan="6" class="text-center py-5 text-muted">No blogs found. Click "Add New Blog" to create one.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.getElementById('editBlogImage')?.addEventListener('change', function (e) {
    var file = e.target.files[0];
    var preview = document.getElementById('currentPreview');
    if (!preview) return;
    if (!file) return;
    if (file.size > 5 * 1024 * 1024) {
        alert('Image is larger than 5 MB.');
        e.target.value = '';
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