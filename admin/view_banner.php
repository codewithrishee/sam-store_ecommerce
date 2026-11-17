<link rel="stylesheet" href="admin-style.css">

<?php
include("../config/db.php");
include "header.php";
include "sidebar.php";

/* ================= DELETE ================= */
if(isset($_GET['delete'])){
    $id = (int)$_GET['delete'];

    // GET IMAGE
    $stmt = $conn->prepare("SELECT image FROM banners WHERE id=?");
    $stmt->bind_param("i",$id);
    $stmt->execute();
    $imgData = $stmt->get_result()->fetch_assoc();

    // DELETE IMAGE
    if($imgData && !empty($imgData['image'])){
        $path = "../images/".$imgData['image'];
        if(file_exists($path)){ unlink($path); }
    }

    // DELETE DATA
    $stmt2 = $conn->prepare("DELETE FROM banners WHERE id=?");
    $stmt2->bind_param("i",$id);
    $stmt2->execute();

    echo "<script>alert('Banner Deleted'); window.location='view_banner.php';</script>";
}

/* ================= FETCH EDIT ================= */
$editData = null;
if(isset($_GET['edit'])){
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM banners WHERE id=?");
    $stmt->bind_param("i",$id);
    $stmt->execute();
    $editData = $stmt->get_result()->fetch_assoc();
}

/* ================= UPDATE ================= */
if(isset($_POST['update_banner'])){
    $id           = (int)$_POST['id'];
    $heading      = $_POST['heading'];
    $subheading   = $_POST['subheading'];
    $button_text  = $_POST['button_text'];
    $button_link  = $_POST['button_link'];
    $button_color = $_POST['button_color'];
    $page         = $_POST['page'];
    $status       = (int)$_POST['status'];

    // IMAGE UPDATE
    if(!empty($_FILES['image']['name'])){
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp'];

        if(in_array($ext,$allowed)){
            // OLD IMAGE DELETE
            $stmt = $conn->prepare("SELECT image FROM banners WHERE id=?");
            $stmt->bind_param("i",$id);
            $stmt->execute();
            $oldData = $stmt->get_result()->fetch_assoc();
            if($oldData && !empty($oldData['image'])){
                $path = "../images/".$oldData['image'];
                if(file_exists($path)){ unlink($path); }
            }

            // NEW IMAGE
            $image_name = time()."_".rand(1000,9999).".".$ext;
            move_uploaded_file($_FILES['image']['tmp_name'], "../images/".$image_name);

            // UPDATE WITH IMAGE
            $stmt = $conn->prepare("UPDATE banners SET heading=?, subheading=?, button_text=?, button_link=?, button_color=?, page=?, status=?, image=? WHERE id=?");
            $stmt->bind_param("ssssssisi", $heading, $subheading, $button_text, $button_link, $button_color, $page, $status, $image_name, $id);
            $stmt->execute();
        }
    } else {
        // UPDATE WITHOUT IMAGE
        $stmt = $conn->prepare("UPDATE banners SET heading=?, subheading=?, button_text=?, button_link=?, button_color=?, page=?, status=? WHERE id=?");
        $stmt->bind_param("ssssssii", $heading, $subheading, $button_text, $button_link, $button_color, $page, $status, $id);
        $stmt->execute();
    }

    echo "<script>alert('Banner Updated'); window.location='view_banner.php';</script>";
}
?>

<style>
    /* ==========================================================
       PREMIUM ADMIN UI (Layout + Theme)
       ========================================================== */
    body { background: #f4f7f6; font-family: 'Manrope', sans-serif; }
    
    /* Strict Layout Fix: Navbar overlap se bachne ke liye margin-top 75px */
    .main-content { 
        margin-left: 290px; 
        padding: 35px 40px; 
        margin-top: 20px; /* Fixed navbar height */
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
    
    /* Form Controls */
    .form-label { 
        font-family: 'Outfit', sans-serif; 
        font-size: 0.85rem; 
        font-weight: 700; 
        color: #2e3254; 
        margin-bottom: 8px; 
    }
    .form-control, .form-select { 
        border-radius: 12px; 
        border: 1.5px solid #efe6ec; 
        padding: 12px 16px; 
        box-shadow: none; 
        font-size: 0.9rem; 
        color: #2e3254; 
        background-color: #fafbfc; 
        transition: all 0.3s;
    }
    .form-control:focus, .form-select:focus { 
        border-color: #ff4d6d; 
        box-shadow: 0 0 0 4px #fff2f5; 
        background-color: #fff; 
    }
    
    /* Buttons */
    .btn-admin { 
        background: linear-gradient(135deg, #ff4d6d, #e63950); 
        color: #fff; 
        border: none; 
        padding: 12px 28px; 
        border-radius: 999px; 
        font-family: 'Outfit', sans-serif; 
        font-weight: 800; 
        font-size: 0.9rem; 
        letter-spacing: 0.05em; 
        text-transform: uppercase; 
        box-shadow: 0 8px 20px rgba(255, 77, 109, 0.25); 
        transition: all 0.3s; 
    }
    .btn-admin:hover { 
        transform: translateY(-2px); 
        box-shadow: 0 12px 25px rgba(255, 77, 109, 0.35); 
        color: #fff; 
    }
    
    /* Table Styling */
    .table-card { 
        background: #fff; 
        border-radius: 20px; 
        overflow: hidden; 
        border: 1px solid #efe6ec; 
        box-shadow: 0 4px 15px rgba(46, 50, 84, 0.03); 
    }
    .table thead th { 
        background: #fafbfc; 
        border-bottom: 1px solid #efe6ec; 
        padding: 18px 24px; 
        font-size: 0.75rem; 
        text-transform: uppercase; 
        color: #8a8fa3; 
        font-weight: 800; 
        letter-spacing: 0.05em; 
    }
    .table tbody td { 
        padding: 16px 24px; 
        vertical-align: middle; 
        border-bottom: 1px solid #f8f9fa; 
        color: #56607a; 
        font-size: 0.9rem; 
    }
    .table tbody tr:hover { background: #fafbfc; }
    
    /* Badges & Action Buttons */
    .badge-admin { 
        background: #dcfce7; 
        color: #16a34a; 
        padding: 6px 12px; 
        border-radius: 8px; 
        font-family: 'Outfit', sans-serif; 
        font-size: 0.7rem; 
        font-weight: 700; 
        text-transform: uppercase; 
    }
    .btn-edit { 
        background: #ffedd5; 
        color: #ea580c; 
        border: none; 
        border-radius: 8px; 
        font-weight: 700; 
        font-size: 0.8rem; 
        padding: 6px 14px; 
        transition: 0.3s; 
    }
    .btn-edit:hover { background: #ea580c; color: #fff; }
    
    .btn-delete { 
        background: #fee2e2; 
        color: #dc2626; 
        border: none; 
        border-radius: 8px; 
        font-weight: 700; 
        font-size: 0.8rem; 
        padding: 6px 14px; 
        transition: 0.3s; 
    }
    .btn-delete:hover { background: #dc2626; color: #fff; }

    /* Responsive Fix */
    @media (max-width: 991px) { 
        .main-content { margin-left: 85px; padding: 25px 20px; margin-top: 65px; } 
    }
</style>

<!-- MAIN CONTENT -->
<div class="main-content">

    <!-- EDIT CARD -->
    <?php if($editData){ ?>
    <div class="page-card mb-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="page-title m-0">Edit Banner</h3>
            <a href="view_banner.php" class="btn btn-light border rounded-pill px-4">Cancel Edit</a>
        </div>
        
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $editData['id']; ?>">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Show On Page</label>
                    <select name="page" class="form-select">
                        <option value="home" <?= $editData['page']=='home'?'selected':''; ?>>Home</option>
                        <option value="shop" <?= $editData['page']=='shop'?'selected':''; ?>>Shop</option>
                        <option value="both" <?= $editData['page']=='both'?'selected':''; ?>>Both</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="1" <?= $editData['status']==1?'selected':''; ?>>Active</option>
                        <option value="0" <?= $editData['status']==0?'selected':''; ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Heading</label>
                    <input type="text" name="heading" value="<?= htmlspecialchars($editData['heading']); ?>" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Sub Heading</label>
                    <input type="text" name="subheading" value="<?= htmlspecialchars($editData['subheading']); ?>" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Button Text</label>
                    <input type="text" name="button_text" value="<?= htmlspecialchars($editData['button_text']); ?>" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Button Link</label>
                    <input type="text" name="button_link" value="<?= htmlspecialchars($editData['button_link']); ?>" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Button Color</label>
                    <input type="color" name="button_color" value="<?= htmlspecialchars($editData['button_color']); ?>" class="form-control form-control-color w-100" style="height: 48px;">
                </div>
                <div class="col-12 mb-4">
                    <label class="form-label">Change Image (Optional)</label>
                    <input type="file" name="image" class="form-control mb-3">
                    <img src="../images/<?= htmlspecialchars($editData['image']); ?>" class="rounded-3 border" style="width:200px;height:120px;object-fit:cover; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
                </div>
            </div>
            <button type="submit" name="update_banner" class="btn-admin">Update Banner</button>
        </form>
    </div>
    <?php } ?>

    <!-- TABLE CARD -->
    <div class="table-card">
        <div class="p-4 border-bottom d-flex justify-content-between align-items-center bg-white">
            <h3 class="page-title m-0">All Banners</h3>
            <a href="add_banner.php" class="btn-admin text-decoration-none" style="font-size: 0.8rem; padding: 10px 20px;">
                <i class="fa-solid fa-plus me-1"></i> Add New
            </a>
        </div>
        
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Banner Image</th>
                        <th>Heading / Type</th>
                        <th>Page</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = mysqli_query($conn, "SELECT * FROM banners ORDER BY id DESC");
                    $i=1;
                    if(mysqli_num_rows($result) > 0){
                        while($row=mysqli_fetch_assoc($result)){
                    ?>
                    <tr>
                        <td class="fw-bold text-muted"><?= $i++; ?></td>
                        <td>
                            <img src="../images/<?= htmlspecialchars($row['image']); ?>" style="width:110px; height:65px; object-fit:cover; border-radius:10px; border: 1px solid #efe6ec;">
                        </td>
                        <td>
                            <div class="fw-bold text-dark"><?= htmlspecialchars($row['heading']); ?></div>
                            <span class="badge bg-light text-secondary border px-2 py-1 rounded-pill mt-1" style="font-size:0.65rem;"><?= htmlspecialchars($row['type']); ?></span>
                        </td>
                        <td><?= ucfirst(htmlspecialchars($row['page'])); ?></td>
                        <td>
                            <?php if($row['status']){ ?>
                                <span class="badge-admin">Active</span>
                            <?php } else { ?>
                                <span class="badge bg-danger rounded-pill px-3 py-2" style="font-size: 0.7rem;">Inactive</span>
                            <?php } ?>
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                <a href="?edit=<?= $row['id']; ?>" class="btn-edit text-decoration-none"><i class="fa-solid fa-pen me-1"></i>Edit</a>
                                <a href="?delete=<?= $row['id']; ?>" class="btn-delete text-decoration-none" onclick="return confirm('Are you sure you want to delete this banner?')"><i class="fa-solid fa-trash me-1"></i>Delete</a>
                            </div>
                        </td>
                    </tr>
                    <?php 
                        } 
                    } else {
                        echo '<tr><td colspan="6" class="text-center py-5 text-muted">No banners found. Click "Add New" to create one.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</div>