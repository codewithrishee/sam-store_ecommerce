
<?php
include("../config/db.php");
include("header.php");
include("sidebar.php");

/* ================= IMAGE UPLOAD HELPER ================= */

function uploadServiceImage($file) {

    if (empty($file['name'])) {
        return "NO_IMAGE";
    }

    $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
    $maxSize = 5 * 1024 * 1024;

    if (
        !isset($file['tmp_name']) ||
        !is_uploaded_file($file['tmp_name'])
    ) {
        return "UPLOAD_FAILED";
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $mime = mime_content_type($file['tmp_name']);

    if (
        !in_array($ext, $allowedExts) ||
        !in_array($mime, $allowedMimes)
    ) {
        return "INVALID_TYPE";
    }

    if ($file['size'] > $maxSize) {
        return "TOO_LARGE";
    }

    $newName = time() . "_service_" . bin2hex(random_bytes(5)) . "." . $ext;

    if (move_uploaded_file($file['tmp_name'], "../images/" . $newName)) {
        return $newName;
    }

    return "UPLOAD_FAILED";
}

/* ================= DELETE SERVICE ================= */

if (isset($_POST['delete_service'])) {

    $id = (int)$_POST['id'];

    $stmt = $conn->prepare("SELECT image FROM services WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $serviceData = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($serviceData) {

        $stmt = $conn->prepare("DELETE FROM services WHERE id=?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {

            if (!empty($serviceData['image'])) {
                $imagePath = "../images/" . basename($serviceData['image']);

                if (is_file($imagePath)) {
                    unlink($imagePath);
                }
            }

            echo "<script>
                alert('Service Deleted Successfully!');
                window.location='view-service.php';
            </script>";
            exit;

        } else {
            echo "<script>alert('Unable to delete service.');</script>";
        }

        $stmt->close();

    } else {
        echo "<script>alert('Service not found.');</script>";
    }
}

/* ================= STATUS UPDATE ================= */

if (isset($_GET['status'])) {

    $id = (int)$_GET['status'];

    $stmt = $conn->prepare("
        UPDATE services
        SET status = IF(status = 1, 0, 1)
        WHERE id = ?
    ");

    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "<script>
            alert('Service Status Updated!');
            window.location='view-service.php';
        </script>";
        exit;
    }

    $stmt->close();
}

/* ================= FETCH EDIT ================= */

$editData = null;

if (isset($_GET['edit'])) {

    $id = (int)$_GET['edit'];

    $stmt = $conn->prepare("SELECT * FROM services WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $editData = $stmt->get_result()->fetch_assoc();

    $stmt->close();
}

/* ================= UPDATE SERVICE ================= */

if (isset($_POST['update_service'])) {

    $id = (int)$_POST['id'];
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $link = trim($_POST['link'] ?? '');
    $status = isset($_POST['status']) ? (int)$_POST['status'] : 1;

    if ($title === '' || $description === '') {

        echo "<script>alert('Title and Description are required.');</script>";

    } else {

        // GET OLD IMAGE
        $stmt = $conn->prepare("SELECT image FROM services WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $oldData = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$oldData) {

            echo "<script>alert('Service not found.');</script>";

        } else {

            $newImage = null;
            $uploadError = false;

            // IMAGE UPDATE (OPTIONAL)
            if (!empty($_FILES['image']['name'])) {

                $upload = uploadServiceImage($_FILES['image']);

                if ($upload === "INVALID_TYPE") {
                    echo "<script>alert('Only JPG, PNG and WEBP images are allowed.');</script>";
                    $uploadError = true;

                } elseif ($upload === "TOO_LARGE") {
                    echo "<script>alert('Image size must be below 5MB.');</script>";
                    $uploadError = true;

                } elseif ($upload === "UPLOAD_FAILED") {
                    echo "<script>alert('Image upload failed.');</script>";
                    $uploadError = true;

                } else {
                    $newImage = $upload;
                }
            }

            if (!$uploadError) {

                if ($newImage) {

                    $stmt = $conn->prepare("
                        UPDATE services
                        SET title=?, description=?, image=?, link=?, status=?
                        WHERE id=?
                    ");

                    $stmt->bind_param(
                        "ssssii",
                        $title,
                        $description,
                        $newImage,
                        $link,
                        $status,
                        $id
                    );

                } else {

                    $stmt = $conn->prepare("
                        UPDATE services
                        SET title=?, description=?, link=?, status=?
                        WHERE id=?
                    ");

                    $stmt->bind_param(
                        "sssii",
                        $title,
                        $description,
                        $link,
                        $status,
                        $id
                    );
                }

                if ($stmt->execute()) {

                    // DELETE OLD IMAGE AFTER SUCCESSFUL UPDATE
                    if ($newImage && !empty($oldData['image'])) {

                        $oldPath = "../images/" . basename($oldData['image']);

                        if (is_file($oldPath)) {
                            unlink($oldPath);
                        }
                    }

                    echo "<script>
                        alert('Service Updated Successfully!');
                        window.location='view-service.php';
                    </script>";
                    exit;

                } else {

                    // CLEANUP NEW IMAGE IF UPDATE FAILED
                    if ($newImage && is_file("../images/" . $newImage)) {
                        unlink("../images/" . $newImage);
                    }

                    echo "<script>alert('Database Error: " . addslashes($stmt->error) . "');</script>";
                }

                $stmt->close();
            }
        }
    }

    // Reload edit data after validation/upload error
    if (!isset($_GET['edit']) && $id > 0) {
        $_GET['edit'] = $id;
        $stmt = $conn->prepare("SELECT * FROM services WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $editData = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
}
?>

<style>

/* ================= PREMIUM ADMIN UI ================= */

body {
    background: #f4f7f6;
    font-family: 'Manrope', sans-serif;
}

.main-content {
    margin-left: 290px;
    padding: 35px 40px;
    margin-top: 20px;
    min-height: calc(100vh - 75px);
}

.page-card {
    background: #fff;
    border-radius: 20px;
    padding: 35px;
    border: 1px solid #efe6ec;
    box-shadow: 0 4px 15px rgba(46, 50, 84, 0.03);
}

.page-title {
    font-family: 'Outfit', sans-serif;
    font-weight: 800;
    color: #2e3254;
    margin-bottom: 25px;
}

/* FORM */

.form-label {
    font-family: 'Outfit', sans-serif;
    font-size: .85rem;
    font-weight: 700;
    color: #2e3254;
    margin-bottom: 8px;
}

.form-control,
.form-select {
    border-radius: 12px;
    border: 1.5px solid #efe6ec;
    padding: 12px 16px;
    box-shadow: none;
    font-size: .9rem;
    color: #2e3254;
    background: #fafbfc;
}

.form-control:focus,
.form-select:focus {
    border-color: #ff4d6d;
    box-shadow: 0 0 0 4px #fff2f5;
    background: #fff;
}

/* BUTTONS */

.btn-admin {
    background: linear-gradient(135deg, #ff4d6d, #e63950);
    color: #fff;
    border: none;
    padding: 12px 28px;
    border-radius: 999px;
    font-family: 'Outfit', sans-serif;
    font-weight: 800;
    font-size: .9rem;
    box-shadow: 0 8px 20px rgba(255, 77, 109, .25);
    transition: all .3s;
}

.btn-admin:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 25px rgba(255, 77, 109, .35);
    color: #fff;
}

/* TABLE */

.table-card {
    background: #fff;
    border-radius: 20px;
    overflow: hidden;
    border: 1px solid #efe6ec;
    box-shadow: 0 4px 15px rgba(46, 50, 84, .03);
}

.table thead th {
    background: #fafbfc;
    border-bottom: 1px solid #efe6ec;
    padding: 18px 20px;
    font-size: .75rem;
    text-transform: uppercase;
    color: #8a8fa3;
    font-weight: 800;
    letter-spacing: .05em;
    white-space: nowrap;
}

.table tbody td {
    padding: 16px 20px;
    vertical-align: middle;
    border-bottom: 1px solid #f8f9fa;
    color: #56607a;
    font-size: .9rem;
}

.table tbody tr:hover {
    background: #fafbfc;
}

/* BADGES */

.badge-admin {
    background: #dcfce7;
    color: #16a34a;
    padding: 6px 12px;
    border-radius: 8px;
    font-family: 'Outfit', sans-serif;
    font-size: .7rem;
    font-weight: 700;
    text-transform: uppercase;
}

.badge-inactive {
    background: #fee2e2;
    color: #dc2626;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: .7rem;
    font-weight: 700;
}

/* ACTION BUTTONS */

.btn-edit {
    background: #ffedd5;
    color: #ea580c;
    border: none;
    border-radius: 8px;
    font-weight: 700;
    font-size: .8rem;
    padding: 7px 13px;
    text-decoration: none;
    transition: .3s;
}

.btn-edit:hover {
    background: #ea580c;
    color: #fff;
}

.btn-delete {
    background: #fee2e2;
    color: #dc2626;
    border: none;
    border-radius: 8px;
    font-weight: 700;
    font-size: .8rem;
    padding: 7px 13px;
    transition: .3s;
}

.btn-delete:hover {
    background: #dc2626;
    color: #fff;
}

.btn-status {
    background: #f3f4f6;
    color: #475569;
    border-radius: 8px;
    padding: 7px 12px;
    font-size: .75rem;
    font-weight: 700;
    text-decoration: none;
    white-space: nowrap;
}

.btn-status:hover {
    background: #e2e8f0;
    color: #1e293b;
}

.service-thumb {
    width: 65px;
    height: 65px;
    object-fit: contain;
    border-radius: 12px;
    padding: 5px;
    background: #fff2f5;
    border: 1px solid #efe6ec;
}

.edit-preview {
    width: 130px;
    height: 110px;
    object-fit: contain;
    border: 1px solid #efe6ec;
    border-radius: 12px;
    padding: 5px;
    background: #fff2f5;
}

/* RESPONSIVE */

@media (max-width: 991px) {

    .main-content {
        margin-left: 85px;
        padding: 25px 20px;
        margin-top: 65px;
    }

    .page-card {
        padding: 20px;
    }
}

@media (max-width: 575px) {

    .main-content {
        margin-left: 75px;
        padding: 15px 10px;
    }

    .page-card {
        padding: 18px;
    }

    .page-title {
        font-size: 20px;
    }

}

</style>

<!-- ================= MAIN CONTENT ================= -->

<div class="main-content">

    <!-- ================= EDIT SERVICE ================= -->

    <?php if ($editData) { ?>

    <div class="page-card mb-4">

        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">

            <h3 class="page-title m-0">Edit Service</h3>

            <a href="view-service.php"
               class="btn btn-light border rounded-pill px-4">
                Cancel Edit
            </a>

        </div>

        <form method="POST" enctype="multipart/form-data">

            <input type="hidden" name="id"
                   value="<?= (int)$editData['id']; ?>">

            <div class="row">

                <div class="col-md-6 mb-3">

                    <label class="form-label">Service Title *</label>

                    <input
                        type="text"
                        name="title"
                        class="form-control"
                        value="<?= htmlspecialchars($editData['title']); ?>"
                        maxlength="255"
                        required
                    >

                </div>

                <div class="col-md-6 mb-3">

                    <label class="form-label">Read More Link</label>

                    <input
                        type="text"
                        name="link"
                        class="form-control"
                        value="<?= htmlspecialchars($editData['link']); ?>"
                    >

                </div>

                <div class="col-12 mb-3">

                    <label class="form-label">Service Description *</label>

                    <textarea
                        name="description"
                        class="form-control"
                        rows="5"
                        required
                    ><?= htmlspecialchars($editData['description']); ?></textarea>

                </div>

                <div class="col-md-6 mb-4">

                    <label class="form-label">Change Service Image (Optional)</label>

                    <input
                        type="file"
                        name="image"
                        class="form-control"
                        accept="image/jpeg,image/png,image/webp"
                        onchange="previewServiceImage(event)"
                    >

                    <img
                        id="servicePreview"
                        src="../images/<?= htmlspecialchars($editData['image']); ?>"
                        class="edit-preview mt-3"
                        alt="Current Service Image"
                    >

                </div>

                <div class="col-md-6 mb-3">

                    <label class="form-label">Status</label>

                    <select name="status" class="form-select">

                        <option value="1" <?= $editData['status'] == 1 ? 'selected' : ''; ?>>
                            Active
                        </option>

                        <option value="0" <?= $editData['status'] == 0 ? 'selected' : ''; ?>>
                            Inactive
                        </option>

                    </select>

                </div>

            </div>

            <button type="submit" name="update_service" class="btn-admin">
                Update Service
            </button>

        </form>

    </div>

    <?php } ?>


    <!-- ================= ALL SERVICES TABLE ================= -->

    <div class="table-card">

        <div class="p-4 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-3 bg-white">

            <h3 class="page-title m-0">All Services</h3>

            <a href="add-service.php"
               class="btn-admin text-decoration-none"
               style="font-size:.8rem;padding:10px 20px;">

                <i class="fa-solid fa-plus me-1"></i>
                Add New

            </a>

        </div>

        <div class="table-responsive">

            <table class="table align-middle mb-0">

                <thead>

                    <tr>
                        <th>#</th>
                        <th>Image</th>
                        <th>Service Title</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>

                </thead>

                <tbody>

                    <?php

                    $result = mysqli_query(
                        $conn,
                        "SELECT * FROM services ORDER BY id DESC"
                    );

                    $i = 1;

                    if ($result && mysqli_num_rows($result) > 0) {

                        while ($row = mysqli_fetch_assoc($result)) {

                    ?>

                    <tr>

                        <td class="fw-bold text-muted">
                            <?= $i++; ?>
                        </td>

                        <td>

                            <img
                                src="../images/<?= htmlspecialchars($row['image']); ?>"
                                class="service-thumb"
                                alt="<?= htmlspecialchars($row['title']); ?>"
                            >

                        </td>

                        <td>

                            <div class="fw-bold text-dark">
                                <?= htmlspecialchars($row['title']); ?>
                            </div>

                            <small class="text-muted">
                                ID: <?= (int)$row['id']; ?>
                            </small>

                        </td>

                        <td style="min-width:200px;max-width:300px;">

                            <?= htmlspecialchars(
                                mb_strimwidth($row['description'], 0, 100, '...')
                            ); ?>

                        </td>

                        <td>

                            <?php if ($row['status'] == 1) { ?>

                                <span class="badge-admin">Active</span>

                            <?php } else { ?>

                                <span class="badge-inactive">Inactive</span>

                            <?php } ?>

                            <div class="mt-2">

                                <a
                                    href="?status=<?= (int)$row['id']; ?>"
                                    class="btn-status"
                                    onclick="return confirm('Change service status?')"
                                >
                                    <?= $row['status'] == 1 ? 'Deactivate' : 'Activate'; ?>
                                </a>

                            </div>

                        </td>

                        <td>

                            <div class="d-flex gap-2 flex-wrap">

                                <a
                                    href="?edit=<?= (int)$row['id']; ?>"
                                    class="btn-edit"
                                >
                                    <i class="fa-solid fa-pen me-1"></i>
                                    Edit
                                </a>

                                <form method="POST"
                                      onsubmit="return confirm('Are you sure you want to delete this service?');">

                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?= (int)$row['id']; ?>"
                                    >

                                    <button
                                        type="submit"
                                        name="delete_service"
                                        class="btn-delete"
                                    >
                                        <i class="fa-solid fa-trash me-1"></i>
                                        Delete
                                    </button>

                                </form>

                            </div>

                        </td>

                    </tr>

                    <?php
                        }

                    } else {

                        echo '
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                No services found. Click "Add New" to create one.
                            </td>
                        </tr>';

                    }
                    ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<script>

function previewServiceImage(event) {

    const input = event.target;
    const preview = document.getElementById('servicePreview');

    if (input.files && input.files[0]) {

        const reader = new FileReader();

        reader.onload = function(e) {
            preview.src = e.target.result;
        };

        reader.readAsDataURL(input.files[0]);

    }

}

</script>
</body>
</html>
