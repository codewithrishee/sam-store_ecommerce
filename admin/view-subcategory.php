<link rel="stylesheet" href="admin-style.css">

<?php
include("../config/db.php");
include "header.php";
include "sidebar.php";

/* ================= DELETE ================= */
if(isset($_GET['delete'])){
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM sub_categories WHERE id=$id");
    echo "<script>alert('Sub Category Deleted'); window.location='view-subcategory.php';</script>";
}

/* ================= EDIT DATA ================= */
$editData = null;
if(isset($_GET['edit'])){
    $id = (int)$_GET['edit'];
    $res = mysqli_query($conn, "SELECT * FROM sub_categories WHERE id=$id");
    $editData = mysqli_fetch_assoc($res);
}

/* ================= UPDATE ================= */
if(isset($_POST['update_subcategory'])){
    $id          = (int)$_POST['id'];
    $category_id = (int)$_POST['category_id'];
    $name        = mysqli_real_escape_string($conn, $_POST['name']);
    $status      = (int)$_POST['status'];

    mysqli_query($conn, "UPDATE sub_categories SET category_id='$category_id', name='$name', status='$status' WHERE id=$id");
    echo "<script>alert('Sub Category Updated'); window.location='view-subcategory.php';</script>";
}
?>

<style>
    /* Layout & Theme */
    body { background: #f4f7f6; font-family: 'Manrope', sans-serif; }
    .main-content { margin-left: 290px; padding: 35px 40px; margin-top: 20px; min-height: calc(100vh - 75px); }
    
    .page-card { background: #fff; border-radius: 20px; padding: 35px; border: 1px solid #efe6ec; box-shadow: 0 4px 15px rgba(46, 50, 84, 0.03); }
    .page-title { font-family: 'Outfit', sans-serif; font-weight: 800; color: #2e3254; margin-bottom: 25px; font-size: 1.5rem;}
    
    /* Form Controls */
    .form-label { font-family: 'Outfit', sans-serif; font-size: 0.85rem; font-weight: 700; color: #2e3254; margin-bottom: 8px; }
    .form-control, .form-select { border-radius: 12px; border: 1.5px solid #efe6ec; padding: 12px 16px; box-shadow: none; font-size: 0.9rem; color: #2e3254; background-color: #fafbfc; transition: all 0.3s; }
    .form-control:focus, .form-select:focus { border-color: #ff4d6d; box-shadow: 0 0 0 4px #fff2f5; background-color: #fff; }
    
    /* Buttons */
    .btn-admin { background: linear-gradient(135deg, #ff4d6d, #e63950); color: #fff; border: none; padding: 12px 28px; border-radius: 999px; font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 0.9rem; letter-spacing: 0.05em; text-transform: uppercase; box-shadow: 0 8px 20px rgba(255, 77, 109, 0.25); transition: all 0.3s; display: inline-flex; align-items: center; gap: 8px;}
    .btn-admin:hover { transform: translateY(-2px); box-shadow: 0 12px 25px rgba(255, 77, 109, 0.35); color: #fff; }
    
    /* Table */
    .table-card { background: #fff; border-radius: 20px; overflow: hidden; border: 1px solid #efe6ec; box-shadow: 0 4px 15px rgba(46, 50, 84, 0.03); }
    .table thead th { background: #fafbfc; border-bottom: 1px solid #efe6ec; padding: 18px 24px; font-size: 0.75rem; text-transform: uppercase; color: #8a8fa3; font-weight: 800; letter-spacing: 0.05em; }
    .table tbody td { padding: 16px 24px; vertical-align: middle; border-bottom: 1px solid #f8f9fa; color: #56607a; font-size: 0.9rem; }
    .table tbody tr:hover { background: #fafbfc; }
    
    /* Badges & Action Buttons */
    .badge-admin { background: #dcfce7; color: #16a34a; padding: 6px 12px; border-radius: 8px; font-family: 'Outfit', sans-serif; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; }
    .badge-cat { background: #e0f2fe; color: #0284c7; padding: 6px 12px; border-radius: 8px; font-family: 'Outfit', sans-serif; font-size: 0.75rem; font-weight: 700; }
    
    .btn-edit { background: #ffedd5; color: #ea580c; border: none; border-radius: 8px; font-weight: 700; font-size: 0.8rem; padding: 8px 14px; transition: 0.3s; display: inline-flex; align-items: center; gap: 6px; }
    .btn-edit:hover { background: #ea580c; color: #fff; }
    .btn-delete { background: #fee2e2; color: #dc2626; border: none; border-radius: 8px; font-weight: 700; font-size: 0.8rem; padding: 8px 14px; transition: 0.3s; display: inline-flex; align-items: center; gap: 6px; }
    .btn-delete:hover { background: #dc2626; color: #fff; }

    @media (max-width: 991px) { .main-content { margin-left: 85px; padding: 25px 20px; margin-top: 65px; } .page-card { padding: 25px; } }
</style>

<!-- MAIN CONTENT -->
<div class="main-content">

    <!-- EDIT CARD -->
    <?php if($editData){ ?>
    <div class="page-card mb-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="page-title m-0">Edit Sub Category</h3>
            <a href="view-subcategory.php" class="btn btn-light border rounded-pill px-4">Cancel Edit</a>
        </div>

        <form method="POST">
            <input type="hidden" name="id" value="<?= $editData['id']; ?>">
            
            <div class="row g-4">
                <!-- CATEGORY -->
                <div class="col-md-4">
                    <label class="form-label">Main Category</label>
                    <select name="category_id" class="form-select" required>
                        <option value="">Select Category</option>
                        <?php
                        $catRes = mysqli_query($conn, "SELECT * FROM categories WHERE status=1 ORDER BY id DESC");
                        while($cat = mysqli_fetch_assoc($catRes)){
                            $selected = ($cat['id'] == $editData['category_id']) ? 'selected' : '';
                        ?>
                        <option value="<?= $cat['id']; ?>" <?= $selected; ?>>
                            <?= htmlspecialchars($cat['name']); ?>
                        </option>
                        <?php } ?>
                    </select>
                </div>

                <!-- SUB CATEGORY -->
                <div class="col-md-4">
                    <label class="form-label">Sub Category Name</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($editData['name']); ?>" class="form-control" required>
                </div>

                <!-- STATUS -->
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="1" <?= $editData['status'] ? 'selected' : ''; ?>>Active</option>
                        <option value="0" <?= !$editData['status'] ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>

                <!-- BUTTONS -->
                <div class="col-12 mt-4 d-flex gap-3">
                    <button type="submit" name="update_subcategory" class="btn-admin">Update Sub Category</button>
                    <a href="view-subcategory.php" class="btn btn-light border rounded-pill px-4 py-2" style="font-family:'Outfit', sans-serif; font-weight:700;">Cancel</a>
                </div>
            </div>
        </form>
    </div>
    <?php } ?>

    <!-- TABLE CARD -->
    <div class="table-card">
        <div class="p-4 border-bottom d-flex justify-content-between align-items-center bg-white">
            <h3 class="page-title m-0">All Sub Categories</h3>
            <a href="add-subcategory.php" class="btn-admin text-decoration-none" style="font-size: 0.8rem; padding: 10px 20px;">
                <i class="fa-solid fa-plus"></i> Add New
            </a>
        </div>
        
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th width="5%">#</th>
                        <th width="35%">Sub Category Name</th>
                        <th width="25%">Main Category</th>
                        <th width="15%">Status</th>
                        <th width="20%">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "
                    SELECT s.*, c.name AS cat_name
                    FROM sub_categories s
                    JOIN categories c ON s.category_id = c.id
                    ORDER BY s.id DESC
                    ";
                    $res = mysqli_query($conn, $sql);
                    $i = 1;

                    if(mysqli_num_rows($res) > 0){
                        while($row = mysqli_fetch_assoc($res)){
                    ?>
                    <tr>
                        <td class="fw-bold text-muted"><?= $i++; ?></td>
                        <td class="fw-bold text-dark fs-6"><?= htmlspecialchars($row['name']); ?></td>
                        <td>
                            <span class="badge-cat"><?= htmlspecialchars($row['cat_name']); ?></span>
                        </td>
                        <td>
                            <?php if($row['status']){ ?>
                                <span class="badge-admin">Active</span>
                            <?php } else { ?>
                                <span class="badge bg-danger rounded-pill px-3 py-2" style="font-size: 0.7rem;">Inactive</span>
                            <?php } ?>
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                <a href="?edit=<?= $row['id']; ?>" class="btn-edit text-decoration-none"><i class="fa-solid fa-pen"></i> Edit</a>
                                <a href="?delete=<?= $row['id']; ?>" class="btn-delete text-decoration-none" onclick="return confirm('Are you sure you want to delete this sub category?')"><i class="fa-solid fa-trash"></i> Delete</a>
                            </div>
                        </td>
                    </tr>
                    <?php 
                        } 
                    } else {
                        echo '<tr><td colspan="5" class="text-center py-5 text-muted"><i class="fa-solid fa-folder-open fs-2 mb-3 text-light"></i><br>No sub categories found. Click "Add New" to create one.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</div>