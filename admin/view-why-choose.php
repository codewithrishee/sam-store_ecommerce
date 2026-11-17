
<?php
include("../config/db.php");
include "header.php";
include "sidebar.php";

/* ================= DELETE ================= */
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    $getImg = mysqli_query($conn, "SELECT image FROM why_choose WHERE id='$id'");
    $imgData = mysqli_fetch_assoc($getImg);

    if ($imgData && !empty($imgData['image']) && file_exists("../images/".$imgData['image'])) {
        unlink("../images/".$imgData['image']);
    }

    mysqli_query($conn, "DELETE FROM why_choose WHERE id='$id'");

    echo "<script>alert('Item Deleted Successfully');window.location='view-why-choose.php';</script>";
    exit;
}

/* ================= FETCH EDIT ================= */
$editData = null;

if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $result = mysqli_query($conn, "SELECT * FROM why_choose WHERE id='$id'");
    $editData = mysqli_fetch_assoc($result);
}

/* ================= UPDATE ================= */
if (isset($_POST['update_item'])) {
    $id = (int)$_POST['id'];
    $title = trim($_POST['title']);
    $desc = trim($_POST['description']);

    $oldQuery = mysqli_query($conn, "SELECT image FROM why_choose WHERE id='$id'");
    $oldData = mysqli_fetch_assoc($oldQuery);
    $image = $oldData['image'] ?? '';

    if ($title === '' || $desc === '') {
        echo "<script>alert('Please fill all required fields');</script>";
    } else {
        if (!empty($_FILES['image']['name'])) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];

            if (!in_array($ext, $allowed)) {
                echo "<script>alert('Invalid Image Format');</script>";
                exit;
            }

            if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                echo "<script>alert('Image must be under 5MB');</script>";
                exit;
            }

            $newImage = time()."_".rand(1000,9999).".".$ext;

            if (move_uploaded_file($_FILES['image']['tmp_name'], "../images/".$newImage)) {
                if ($image && file_exists("../images/".$image)) {
                    unlink("../images/".$image);
                }
                $image = $newImage;
            }
        }

        $stmt = mysqli_prepare($conn, "UPDATE why_choose SET title=?, description=?, image=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "sssi", $title, $desc, $image, $id);
        mysqli_stmt_execute($stmt);

        echo "<script>alert('Updated Successfully');window.location='view-why-choose.php';</script>";
        exit;
    }
}

/* ================= FETCH ALL ================= */
$result = mysqli_query($conn, "SELECT * FROM why_choose ORDER BY id DESC");
?>

<link rel="stylesheet" href="admin-style.css">

<style>
/* PAGE */
.wc-page {
    padding: 25px;
    color: #263044;
}

.wc-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    margin-bottom: 25px;
    flex-wrap: wrap;
}

.wc-heading h2 {
    font-size: 25px;
    font-weight: 800;
    margin: 0;
    color: #222b40;
}

.wc-heading p {
    color: #9299a8;
    margin: 6px 0 0;
    font-size: 13px;
}

/* CARDS */
.wc-card {
    background: #fff;
    border: 1px solid #f0edf2;
    border-radius: 20px;
    box-shadow: 0 8px 30px rgba(35, 25, 45, .045);
    overflow: hidden;
    margin-bottom: 25px;
}

.wc-card-header {
    padding: 22px 25px;
    border-bottom: 1px solid #f1edf2;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.wc-card-header h3 {
    font-size: 18px;
    font-weight: 800;
    margin: 0;
    color: #252d40;
}

.wc-card-header p {
    font-size: 12px;
    color: #969baa;
    margin: 5px 0 0;
}

/* FORM */
.wc-form {
    padding: 25px;
}

.wc-form label {
    display: block;
    font-size: 13px;
    font-weight: 700;
    color: #434b5e;
    margin-bottom: 9px;
}

.wc-form .form-control {
    border: 1px solid #e9e6ec;
    border-radius: 11px;
    padding: 12px 14px;
    font-size: 13px;
    box-shadow: none;
    background: #fcfbfd;
}

.wc-form .form-control:focus {
    border-color: #e95b91;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(233,91,145,.10);
}

.wc-form textarea {
    resize: vertical;
}

.wc-current-image {
    width: 150px;
    height: 110px;
    object-fit: cover;
    border-radius: 13px;
    border: 3px solid #fff;
    box-shadow: 0 4px 15px rgba(0,0,0,.12);
}

.wc-image-preview {
    display: none;
    width: 150px;
    height: 110px;
    object-fit: cover;
    border-radius: 13px;
    margin-top: 12px;
    border: 3px solid #fff;
    box-shadow: 0 4px 15px rgba(0,0,0,.12);
}

.wc-help {
    font-size: 11px;
    color: #9a9eaa;
    margin-top: 7px;
}

/* BUTTONS */
.wc-primary-btn {
    background: linear-gradient(135deg, #ec5b91, #d83c7b);
    border: none;
    color: #fff;
    border-radius: 11px;
    padding: 12px 23px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    transition: .25s;
}

.wc-primary-btn:hover {
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(216,60,123,.22);
}

.wc-cancel-btn {
    background: #f1f2f5;
    color: #555d6e;
    border-radius: 11px;
    padding: 12px 22px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
}

.wc-cancel-btn:hover {
    background: #e6e7eb;
    color: #333;
}

/* TABLE */
.wc-table-wrap {
    padding: 5px 22px 22px;
}

.wc-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 9px;
    min-width: 650px;
}

.wc-table thead th {
    background: #faf8fb;
    color: #9398a7;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .6px;
    padding: 15px;
    border: none;
}

.wc-table tbody tr {
    background: #fff;
    box-shadow: 0 2px 12px rgba(0,0,0,.025);
}

.wc-table tbody td {
    padding: 14px 15px;
    vertical-align: middle;
    border-top: 1px solid #f2eff3;
    border-bottom: 1px solid #f2eff3;
    font-size: 13px;
    color: #535b6d;
}

.wc-table tbody td:first-child {
    border-left: 1px solid #f2eff3;
    border-radius: 12px 0 0 12px;
}

.wc-table tbody td:last-child {
    border-right: 1px solid #f2eff3;
    border-radius: 0 12px 12px 0;
}

.wc-thumb {
    width: 65px;
    height: 58px;
    object-fit: cover;
    border-radius: 11px;
    background: #f5f3f6;
}

.wc-item-title {
    font-weight: 800;
    color: #30384b;
    font-size: 13px;
}

.wc-description {
    max-width: 300px;
    line-height: 1.6;
    color: #858b99;
    font-size: 12px;
}

.wc-number {
    width: 30px;
    height: 30px;
    border-radius: 9px;
    display: inline-flex;
    justify-content: center;
    align-items: center;
    background: #fff0f6;
    color: #df4d87;
    font-weight: 800;
    font-size: 12px;
}

.wc-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 8px 13px;
    border-radius: 9px;
    font-size: 11px;
    font-weight: 800;
    text-decoration: none;
    transition: .2s;
}

.wc-edit {
    background: #fff4df;
    color: #c88a24;
}

.wc-edit:hover {
    background: #ffe9bd;
    color: #a66b0b;
}

.wc-delete {
    background: #fff0f0;
    color: #e05252;
}

.wc-delete:hover {
    background: #ffe0e0;
    color: #c72e2e;
}

.wc-empty {
    text-align: center;
    padding: 45px 15px !important;
    color: #999;
}

.wc-empty i {
    font-size: 35px;
    color: #e5b4c9;
    display: block;
    margin-bottom: 12px;
}

@media(max-width:768px) {
    .wc-page {
        padding: 15px;
    }

    .wc-heading h2 {
        font-size: 21px;
    }

    .wc-card-header,
    .wc-form {
        padding: 18px;
    }

    .wc-table-wrap {
        padding: 5px 12px 15px;
    }

    .wc-heading .wc-primary-btn {
        width: 100%;
    }
}
.main-content {
    padding-top: 90px !important;
}
</style>

<div class="main-content">
<div class="wc-page">

    <!-- PAGE HEADING -->
    <div class="wc-heading">
        <div>
            <h2>Why Choose Us</h2>
            <p>Manage your website's Why Choose Us section</p>
        </div>

        <a href="add-why-choose.php" class="wc-primary-btn">
            + Add New Item
        </a>
    </div>

    <!-- ================= EDIT SECTION ================= -->
    <?php if ($editData): ?>

    <div class="wc-card">
        <div class="wc-card-header">
            <div>
                <h3>Edit Why Choose Item</h3>
                <p>Update the content and image of this item</p>
            </div>
        </div>

        <form method="POST" enctype="multipart/form-data" class="wc-form">
            <input type="hidden" name="id" value="<?= (int)$editData['id']; ?>">

            <div class="row">
                <div class="col-md-6 mb-4">
                    <label>Item Title *</label>
                    <input type="text" name="title"
                        value="<?= htmlspecialchars($editData['title']); ?>"
                        class="form-control" placeholder="Enter item title" required>
                </div>

                <div class="col-md-6 mb-4">
                    <label>Upload New Image</label>
                    <input type="file" name="image" id="wcImage"
                        class="form-control" accept=".jpg,.jpeg,.png,.webp">
                    <div class="wc-help">JPG, PNG, WEBP | Maximum 5MB</div>
                </div>

                <div class="col-12 mb-4">
                    <label>Description *</label>
                    <textarea name="description" class="form-control" rows="5"
                        placeholder="Enter description..." required><?= htmlspecialchars($editData['description']); ?></textarea>
                </div>

                <div class="col-12 mb-4">
                    <label>Current Image</label><br>
                    <?php if (!empty($editData['image'])): ?>
                        <img src="../images/<?= htmlspecialchars($editData['image']); ?>"
                             class="wc-current-image" id="wcCurrentImage" alt="Current image">
                    <?php endif; ?>

                    <img src="" class="wc-image-preview" id="wcPreview" alt="New image preview">
                </div>

                <div class="col-12 d-flex gap-3 flex-wrap">
                    <button type="submit" name="update_item" class="wc-primary-btn">
                        Save Changes
                    </button>

                    <a href="view-why-choose.php" class="wc-cancel-btn">
                        Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>

    <?php endif; ?>

    <!-- ================= TABLE SECTION ================= -->
    <div class="wc-card">

        <div class="wc-card-header">
            <div>
                <h3>All Why Choose Us Items</h3>
                <p>View, edit and manage all your items</p>
            </div>
            <span class="badge rounded-pill text-bg-light px-3 py-2">
                <?= mysqli_num_rows($result); ?> Items
            </span>
        </div>

        <div class="table-responsive wc-table-wrap">
            <table class="wc-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>

                <tbody>
                <?php
                $i = 1;
                if (mysqli_num_rows($result) > 0):
                    while ($row = mysqli_fetch_assoc($result)):
                ?>
                    <tr>
                        <td><span class="wc-number"><?= $i++; ?></span></td>

                        <td>
                            <?php if (!empty($row['image'])): ?>
                                <img src="../images/<?= htmlspecialchars($row['image']); ?>"
                                     class="wc-thumb" alt="Item image">
                            <?php else: ?>
                                <span class="wc-help">No image</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <div class="wc-item-title">
                                <?= htmlspecialchars($row['title']); ?>
                            </div>
                        </td>

                        <td>
                            <div class="wc-description">
                                <?= htmlspecialchars(mb_strimwidth($row['description'], 0, 100, '...')); ?>
                            </div>
                        </td>

                        <td>
                            <div class="d-flex justify-content-center gap-2">
                                <a href="?edit=<?= (int)$row['id']; ?>" class="wc-action wc-edit">
                                    Edit
                                </a>

                                <a href="?delete=<?= (int)$row['id']; ?>"
                                   class="wc-action wc-delete"
                                   onclick="return confirm('Are you sure you want to delete this item?')">
                                    Delete
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php
                    endwhile;
                else:
                ?>
                    <tr>
                        <td colspan="5" class="wc-empty">
                            <i class="fa fa-folder-open"></i>
                            No Why Choose Us items found.
                            <br><br>
                            <a href="add-why-choose.php" class="wc-primary-btn">
                                + Add First Item
                            </a>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
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

    if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
        alert('Please select a JPG, PNG or WEBP image.');
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
