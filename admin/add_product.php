<link rel="stylesheet" href="admin-style.css">

<?php
include("../config/db.php");
include "header.php";
include "sidebar.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

/* ================= HELPER ================= */
function makeSlug($text)
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

/* Make sure the slug is unique (appends -1, -2, ... if needed) */
function uniqueSlug($conn, $slug, $excludeId = 0)
{
    if ($slug === '') {
        $slug = 'product';
    }
    $candidate = $slug;
    $suffix = 1;

    $stmt = $conn->prepare("SELECT id FROM products WHERE slug = ? AND id <> ? LIMIT 1");
    if (!$stmt) {
        return $slug; // fallback: let the DB decide
    }

    while (true) {
        $stmt->bind_param("si", $candidate, $excludeId);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->free_result();

        if (!$exists) {
            break;
        }
        $candidate = $slug . '-' . $suffix;
        $suffix++;
    }

    $stmt->close();
    return $candidate;
}

/* ================= IMAGE UPLOAD HANDLER ================= */
/**
 * Handles unlimited multiple image uploads.
 * Returns ['uploaded' => [filenames...], 'errors' => [messages...]]
 */
function handleImageUploads(array $files, $uploadDir)
{
    $uploaded = [];
    $errors   = [];

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
    $allowedMimeTypes  = ['image/jpeg', 'image/png', 'image/webp'];
    $maxSize           = 5 * 1024 * 1024; // 5 MB per image

    $uploadDir = rtrim($uploadDir, '/');

    if (!is_dir($uploadDir)) {
        if (!@mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
            return [
                'uploaded' => [],
                'errors'   => ['Upload directory "' . htmlspecialchars($uploadDir) . '" does not exist and could not be created.']
            ];
        }
    }

    if (!is_writable($uploadDir)) {
        return [
            'uploaded' => [],
            'errors'   => ['Upload directory "' . htmlspecialchars($uploadDir) . '" is not writable.']
        ];
    }

    $finfo = class_exists('finfo') ? new finfo(FILEINFO_MIME_TYPE) : null;

    $names  = isset($files['name'])  && is_array($files['name'])  ? $files['name']  : [];
    $tmp    = isset($files['tmp_name']) && is_array($files['tmp_name']) ? $files['tmp_name'] : [];
    $errors_ = isset($files['error']) && is_array($files['error']) ? $files['error'] : [];
    $sizes  = isset($files['size'])  && is_array($files['size'])  ? $files['size']  : [];

    $total = count($names);

    for ($i = 0; $i < $total; $i++) {
        $originalName = isset($names[$i]) ? $names[$i] : '';
        $tmpName      = isset($tmp[$i]) ? $tmp[$i] : '';
        $errorCode    = isset($errors_[$i]) ? (int) $errors_[$i] : UPLOAD_ERR_NO_FILE;
        $size         = isset($sizes[$i]) ? (int) $sizes[$i] : 0;

        /* Empty slot — user only selected e.g. 2 of 5 possible rows */
        if ($errorCode === UPLOAD_ERR_NO_FILE || $originalName === '') {
            continue;
        }

        if ($errorCode !== UPLOAD_ERR_OK) {
            $errors[] = 'Upload error for "' . htmlspecialchars($originalName) . '" (error code ' . $errorCode . ').';
            continue;
        }

        if (!is_uploaded_file($tmpName)) {
            $errors[] = '"' . htmlspecialchars($originalName) . '" is not a valid uploaded file.';
            continue;
        }

        if ($size <= 0) {
            $errors[] = '"' . htmlspecialchars($originalName) . '" is empty.';
            continue;
        }

        if ($size > $maxSize) {
            $errors[] = '"' . htmlspecialchars($originalName) . '" is larger than 5 MB.';
            continue;
        }

        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExtensions, true)) {
            $errors[] = '"' . htmlspecialchars($originalName) . '": only JPG, JPEG, PNG and WEBP images are allowed.';
            continue;
        }

        if ($finfo !== null) {
            $mimeType = @$finfo->file($tmpName);
            if ($mimeType === false || !in_array($mimeType, $allowedMimeTypes, true)) {
                $errors[] = '"' . htmlspecialchars($originalName) . '": invalid image type.';
                continue;
            }
        }

        if (@getimagesize($tmpName) === false) {
            $errors[] = '"' . htmlspecialchars($originalName) . '": the file is not a valid image.';
            continue;
        }

        $imageName   = date('YmdHis') . '_' . bin2hex(random_bytes(6)) . '.' . $extension;
        $destination = $uploadDir . '/' . $imageName;

        if (!move_uploaded_file($tmpName, $destination)) {
            $errors[] = '"' . htmlspecialchars($originalName) . '": could not be saved on the server.';
            continue;
        }

        $uploaded[] = $imageName;
    }

    return ['uploaded' => $uploaded, 'errors' => $errors];
}

/* ================= ADD PRODUCT ================= */
$errors = [];

if (isset($_POST['add_product'])) {

    /* ---------- Collect fields ---------- */
    $product_name      = trim($_POST['product_name'] ?? '');
    $short_description = trim($_POST['short_description'] ?? '');
    $slug_input        = trim($_POST['slug'] ?? '');
    $sku               = trim($_POST['sku'] ?? '');
    $brand             = trim($_POST['brand'] ?? '');
    $description       = trim($_POST['description'] ?? '');

    $old_price = (float) ($_POST['old_price'] ?? 0);
    $new_price = (float) ($_POST['new_price'] ?? 0);
    $stock     = (int) ($_POST['stock'] ?? 0);
    $rating    = (int) ($_POST['rating'] ?? 5);
    $label     = $_POST['label'] ?? '';

    $material   = trim($_POST['material'] ?? '');
    $color      = trim($_POST['color'] ?? '');
    $size       = trim($_POST['size'] ?? '');
    $tags       = trim($_POST['tags'] ?? '');
    $meta_title = trim($_POST['meta_title'] ?? '');
    $meta_description = trim($_POST['meta_description'] ?? '');
    $meta_keywords    = trim($_POST['meta_keywords'] ?? '');

    $category_id    = (int) ($_POST['category_id'] ?? 0);
    $subcategory_id = (int) ($_POST['subcategory_id'] ?? 0);
    $sections       = $_POST['section_id'] ?? [];
    if (!is_array($sections)) {
        $sections = [];
    }

    $status       = 1;
    $show_in_shop = 1;

    if ($rating < 1 || $rating > 5) {
        $rating = 5;
    }
    if ($stock < 0) {
        $stock = 0;
    }
    if ($old_price < 0) {
        $old_price = 0;
    }
    if ($new_price < 0) {
        $new_price = 0;
    }

    /* ---------- Validation ---------- */
    if ($product_name === '') {
        $errors[] = 'Product name is required.';
    }
    if ($description === '') {
        $errors[] = 'Full description is required.';
    }
    if ($category_id <= 0) {
        $errors[] = 'Please select a main category.';
    }
    if ($subcategory_id <= 0) {
        $errors[] = 'Please select a sub category.';
    }

    $slug = makeSlug($slug_input !== '' ? $slug_input : $product_name);
    if ($slug === '') {
        $slug = 'product';
    }

    /* ---------- Multiple image upload ---------- */
    $uploadedImages = [];

    $hasFiles = isset($_FILES['images']['name'])
        && is_array($_FILES['images']['name'])
        && count(array_filter($_FILES['images']['name'], 'strlen')) > 0;

    if (!$hasFiles) {
        $errors[] = 'Please select at least one product image.';
    } else {
        $uploadResult   = handleImageUploads($_FILES['images'], '../images/');
        $uploadedImages = $uploadResult['uploaded'];

        if (!empty($uploadResult['errors'])) {
            $errors = array_merge($errors, $uploadResult['errors']);
        }
        if (empty($uploadedImages) && empty($uploadResult['errors'])) {
            $errors[] = 'No valid image was uploaded.';
        }
    }

    /* ---------- Roll back uploaded files when something failed ---------- */
    if (!empty($errors)) {
        foreach ($uploadedImages as $f) {
            @unlink('../images/' . $f);
        }
        $uploadedImages = [];
    } else {

        $slug = uniqueSlug($conn, $slug);

        /* Backward compatibility: first three images into products.image1..3 */
        $image1 = $uploadedImages[0] ?? '';
        $image2 = $uploadedImages[1] ?? '';
        $image3 = $uploadedImages[2] ?? '';

        $conn->begin_transaction();

        try {
            /* ---------- INSERT PRODUCT ---------- */
            $sql = "INSERT INTO products (
                        product_name, short_description, slug, sku, brand, description,
                        old_price, new_price, stock, rating, label, material, color, size,
                        tags, meta_title, meta_description, meta_keywords,
                        image1, image2, image3, status, show_in_shop, category_id, subcategory_id
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?,
                        ?, ?, ?, ?, ?, ?, ?, ?,
                        ?, ?, ?, ?,
                        ?, ?, ?, ?, ?, ?, ?
                    )";

            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception('Prepare failed (products): ' . $conn->error);
            }

            /* 6 s + 2 d + 2 i + 11 s + 4 i = 25 types for 25 placeholders */
            $types = 'ssssss' . 'dd' . 'ii' . 'sssssssssss' . 'iiii';

            $stmt->bind_param(
                $types,
                $product_name,
                $short_description,
                $slug,
                $sku,
                $brand,
                $description,
                $old_price,
                $new_price,
                $stock,
                $rating,
                $label,
                $material,
                $color,
                $size,
                $tags,
                $meta_title,
                $meta_description,
                $meta_keywords,
                $image1,
                $image2,
                $image3,
                $status,
                $show_in_shop,
                $category_id,
                $subcategory_id
            );

            if (!$stmt->execute()) {
                throw new Exception('Insert failed (products): ' . $stmt->error);
            }

            $product_id = (int) $stmt->insert_id;
            $stmt->close();

            /* ---------- SAVE ALL PRODUCT IMAGES ---------- */
            $imageStmt = $conn->prepare(
                "INSERT INTO product_images (product_id, image_name) VALUES (?, ?)"
            );
            if (!$imageStmt) {
                throw new Exception('Prepare failed (product_images): ' . $conn->error);
            }

            foreach ($uploadedImages as $imageName) {
                $imageStmt->bind_param("is", $product_id, $imageName);
                if (!$imageStmt->execute()) {
                    throw new Exception('Insert failed (product_images): ' . $imageStmt->error);
                }
            }
            $imageStmt->close();

            /* ---------- PRODUCT ↔ CATEGORY LINK ---------- */
            $stmt2 = $conn->prepare(
                "INSERT INTO product_categories (product_id, category_id, subcategory_id) VALUES (?, ?, ?)"
            );
            if (!$stmt2) {
                throw new Exception('Prepare failed (product_categories): ' . $conn->error);
            }
            $stmt2->bind_param("iii", $product_id, $category_id, $subcategory_id);
            if (!$stmt2->execute()) {
                throw new Exception('Insert failed (product_categories): ' . $stmt2->error);
            }
            $stmt2->close();

            /* ---------- DISPLAY SECTIONS ---------- */
            if (!empty($sections)) {
                $stmt3 = $conn->prepare(
                    "INSERT INTO product_section (product_id, section_id) VALUES (?, ?)"
                );
                if (!$stmt3) {
                    throw new Exception('Prepare failed (product_section): ' . $conn->error);
                }

                foreach ($sections as $sec_id) {
                    $sec_id = (int) $sec_id;
                    if ($sec_id <= 0) {
                        continue;
                    }
                    $stmt3->bind_param("ii", $product_id, $sec_id);
                    if (!$stmt3->execute()) {
                        throw new Exception('Insert failed (product_section): ' . $stmt3->error);
                    }
                }
                $stmt3->close();
            }

            $conn->commit();

            echo "<script>alert('Product Added Successfully'); window.location='add_product.php';</script>";
            exit;

        } catch (Throwable $e) {
            $conn->rollback();

            /* Remove files that were uploaded for a product that never got saved */
            foreach ($uploadedImages as $f) {
                @unlink('../images/' . $f);
            }
            $uploadedImages = [];

            $errors[] = 'Could not save the product: ' . $e->getMessage();
        }
    }
}
?>

<style>
    /* Layout & Theme */
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
        font-size: 1.5rem;
    }

    /* Form Sections */
    .form-section {
        background: #fafbfc;
        border: 1px solid #efe6ec;
        border-radius: 16px;
        padding: 25px;
        margin-bottom: 25px;
    }

    .section-title {
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        color: #2e3254;
        font-size: 1.1rem;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        border-bottom: 2px dashed #efe6ec;
        padding-bottom: 12px;
    }

    .section-title i {
        color: #ff4d6d;
    }

    /* Form Controls */
    .form-label {
        font-family: 'Outfit', sans-serif;
        font-size: 0.85rem;
        font-weight: 700;
        color: #2e3254;
        margin-bottom: 8px;
    }

    .form-control,
    .form-select {
        border-radius: 10px;
        border: 1.5px solid #e2e8f0;
        padding: 10px 14px;
        box-shadow: none;
        font-size: 0.9rem;
        color: #2e3254;
        background-color: #fff;
        transition: all 0.3s;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #ff4d6d;
        box-shadow: 0 0 0 4px #fff2f5;
    }

    /* Button */
    .btn-admin {
        background: linear-gradient(135deg, #ff4d6d, #e63950);
        color: #fff;
        border: none;
        padding: 14px 32px;
        border-radius: 999px;
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        font-size: 0.95rem;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        box-shadow: 0 8px 20px rgba(255, 77, 109, 0.25);
        transition: all 0.3s;
        width: 100%;
    }

    .btn-admin:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 25px rgba(255, 77, 109, 0.35);
        color: #fff;
    }

    /* ===== Multiple image preview grid ===== */
    .img-preview-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
        gap: 12px;
        margin-top: 14px;
    }

    .img-preview-item {
        position: relative;
        aspect-ratio: 1 / 1;
        border: 1px solid #efe6ec;
        border-radius: 12px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 2px 8px rgba(46, 50, 84, 0.05);
    }

    .img-preview-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .img-preview-item .remove-btn {
        position: absolute;
        top: 6px;
        right: 6px;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        border: none;
        background: rgba(46, 50, 84, 0.8);
        color: #fff;
        font-size: 16px;
        line-height: 1;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        transition: background 0.2s;
    }

    .img-preview-item .remove-btn:hover {
        background: #ff4d6d;
    }

    .img-preview-item .img-index {
        position: absolute;
        bottom: 6px;
        left: 6px;
        background: rgba(46, 50, 84, 0.75);
        color: #fff;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.03em;
        padding: 2px 8px;
        border-radius: 999px;
        text-transform: uppercase;
    }

    /* ===== Native <details> dropdown for Display Sections ===== */
    .section-dd {
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        background: #fff;
        transition: all 0.3s;
    }

    .section-dd[open] {
        border-color: #ff4d6d;
        box-shadow: 0 0 0 4px #fff2f5;
    }

    .section-dd summary {
        padding: 10px 14px;
        cursor: pointer;
        list-style: none;
        font-size: 0.9rem;
        color: #2e3254;
        display: flex;
        justify-content: space-between;
        align-items: center;
        user-select: none;
        border-radius: 10px;
    }

    .section-dd summary::-webkit-details-marker {
        display: none;
    }

    .section-dd summary::after {
        content: '\25BE';
        font-size: 0.9rem;
        color: #56607a;
        transition: transform 0.2s;
    }

    .section-dd[open] summary::after {
        transform: rotate(180deg);
    }

    .section-dd[open] summary {
        border-bottom: 1px solid #efe6ec;
        border-radius: 10px 10px 0 0;
    }

    .section-dd-menu {
        padding: 8px;
        max-height: 220px;
        overflow-y: auto;
    }

    .section-dd-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 12px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 0.9rem;
        color: #56607a;
        margin: 0;
    }

    .section-dd-item:hover {
        background: #fff2f5;
        color: #ff4d6d;
    }

    .section-dd-item input[type="checkbox"] {
        margin: 0;
        cursor: pointer;
    }

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
</style>

<div class="main-content">
    <div class="page-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="page-title m-0">Add New Product</h3>
            <a href="view_product.php" class="btn btn-light border rounded-pill px-4">View All Products</a>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger rounded-3 mb-4">
                <strong>Please fix the following:</strong>
                <ul class="mb-0 mt-2">
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="row g-4">

                <!-- SECTION 1: BASIC INFO -->
                <div class="col-12">
                    <div class="form-section">
                        <h5 class="section-title"><i class="fa-solid fa-box-open"></i> Basic Details</h5>
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Product Name <span class="text-danger">*</span></label>
                                <input type="text" name="product_name" class="form-control"
                                    placeholder="Enter product name" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Brand</label>
                                <input type="text" name="brand" class="form-control" placeholder="Enter brand name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Slug</label>
                                <input type="text" name="slug" class="form-control"
                                    placeholder="Leave blank to auto-generate">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">SKU</label>
                                <input type="text" name="sku" class="form-control" placeholder="Enter product SKU">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Short Description <small class="text-muted fw-normal">(For
                                        product cards)</small></label>
                                <textarea name="short_description" class="form-control" rows="2" maxlength="500"
                                    placeholder="Write a short catchy description..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: PRICING & STOCK -->
                <div class="col-md-6">
                    <div class="form-section h-100">
                        <h5 class="section-title"><i class="fa-solid fa-tags"></i> Pricing & Inventory</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Old Price (₹)</label>
                                <input type="number" name="old_price" class="form-control" placeholder="0.00" min="0"
                                    step="0.01" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">New Price (₹)</label>
                                <input type="number" name="new_price" class="form-control" placeholder="0.00" min="0"
                                    step="0.01" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Stock Quantity</label>
                                <input type="number" name="stock" class="form-control" placeholder="0" min="0"
                                    value="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Rating</label>
                                <select name="rating" class="form-select">
                                    <option value="5">5 Star</option>
                                    <option value="4">4 Star</option>
                                    <option value="3">3 Star</option>
                                    <option value="2">2 Star</option>
                                    <option value="1">1 Star</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Special Label</label>
                                <select name="label" class="form-select">
                                    <option value="">None</option>
                                    <option value="Sale">Sale</option>
                                    <option value="Best Seller">Best Seller</option>
                                    <option value="New">New Arrival</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 3: ORGANIZATION -->
                <div class="col-md-6">
                    <div class="form-section h-100">
                        <h5 class="section-title"><i class="fa-solid fa-sitemap"></i> Organization</h5>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Main Category <span class="text-danger">*</span></label>
                                <select id="category" name="category_id" class="form-select" required>
                                    <option value="">Select Category</option>
                                    <?php $cat = mysqli_query($conn, "SELECT * FROM categories ORDER BY id DESC");
                                    while ($c = mysqli_fetch_assoc($cat)) { ?>
                                        <option value="<?= $c['id']; ?>"><?= htmlspecialchars($c['name']); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Sub Category <span class="text-danger">*</span></label>
                                <select id="subcategory" name="subcategory_id" class="form-select" required>
                                    <option value="">Select Sub Category</option>
                                </select>
                            </div>

                            <!-- ===== Display Sections (native <details> — no Bootstrap needed) ===== -->
                            <div class="col-md-12">
                                <label class="form-label">Display Sections</label>
                                <details class="section-dd">
                                    <summary id="sectionSummary">Select Sections (e.g. Featured, Hot Deals)</summary>
                                    <div class="section-dd-menu">
                                        <?php
                                        $sec = mysqli_query($conn, "SELECT * FROM sections ORDER BY id DESC");
                                        if (!$sec) {
                                            echo '<div class="p-2 text-danger small">Query error: '
                                                . htmlspecialchars(mysqli_error($conn)) . '</div>';
                                        } elseif (mysqli_num_rows($sec) === 0) {
                                            echo '<div class="p-2 text-muted small">No sections found in the database. Please add sections first.</div>';
                                        } else {
                                            while ($s = mysqli_fetch_assoc($sec)) {
                                                // Be defensive about which column holds the label
                                                if (isset($s['name'])) {
                                                    $labelText = $s['name'];
                                                } elseif (isset($s['section_name'])) {
                                                    $labelText = $s['section_name'];
                                                } elseif (isset($s['title'])) {
                                                    $labelText = $s['title'];
                                                } else {
                                                    // fallback: first non-id column
                                                    $tmp = $s;
                                                    unset($tmp['id']);
                                                    $labelText = $tmp ? reset($tmp) : ('Section #' . $s['id']);
                                                }
                                                ?>
                                                <label class="section-dd-item">
                                                    <input type="checkbox" name="section_id[]" value="<?= (int) $s['id']; ?>">
                                                    <span><?= htmlspecialchars($labelText); ?></span>
                                                </label>
                                                <?php
                                            }
                                        }
                                        ?>
                                    </div>
                                </details>
                            </div>
                            <!-- ===== END Display Sections ===== -->

                        </div>
                    </div>
                </div>

                <!-- SECTION 4: ATTRIBUTES -->
                <div class="col-12">
                    <div class="form-section">
                        <h5 class="section-title"><i class="fa-solid fa-list-check"></i> Attributes & Specifications
                        </h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Material</label>
                                <input type="text" name="material" class="form-control"
                                    placeholder="e.g. Cotton, Brass">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Color</label>
                                <input type="text" name="color" class="form-control" placeholder="e.g. Black, White">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Size</label>
                                <input type="text" name="size" class="form-control" placeholder="e.g. S, M, L">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Search Tags <small class="text-muted fw-normal">(Comma
                                        separated)</small></label>
                                <input type="text" name="tags" class="form-control"
                                    placeholder="e.g. dog food, premium, grain free">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 5: DESCRIPTION & SEO -->
                <div class="col-12">
                    <div class="form-section">
                        <h5 class="section-title"><i class="fa-solid fa-align-left"></i> Description & SEO</h5>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Full Description <span class="text-danger">*</span></label>
                                <textarea name="description" class="form-control" rows="4"
                                    placeholder="Write complete product details..." required></textarea>
                            </div>
                            <div class="col-12">
                                <hr class="my-2 text-muted">
                            </div>
                            <div class="col-12">
                                <label class="form-label">SEO Meta Title</label>
                                <input type="text" name="meta_title" class="form-control" placeholder="Enter SEO title">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">SEO Meta Description</label>
                                <textarea name="meta_description" class="form-control" rows="2"
                                    placeholder="Enter SEO description"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">SEO Meta Keywords</label>
                                <textarea name="meta_keywords" class="form-control" rows="2"
                                    placeholder="Enter SEO keywords"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 6: IMAGES -->
                <div class="col-12">
                    <div class="form-section">
                        <h5 class="section-title"><i class="fa-solid fa-images"></i> Product Images <span
                                class="text-danger">*</span></h5>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label" for="productImages">
                                    Select Images
                                    <small class="text-muted fw-normal">(JPG, JPEG, PNG or WEBP — select as many as you
                                        need)</small>
                                </label>
                                <input type="file" id="productImages" name="images[]" class="form-control"
                                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" multiple required>
                                <div class="form-text mt-2" id="imageCountText">No images selected yet.</div>
                                <div id="imagePreviewGrid" class="img-preview-grid"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SUBMIT BUTTON -->
                <div class="col-12 text-end mt-4">
                    <button type="submit" name="add_product" class="btn-admin" style="max-width: 300px;">
                        <i class="fa-solid fa-cloud-arrow-up me-2"></i> Publish Product
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    /* ================= MULTIPLE IMAGE PREVIEW + REMOVE ================= */
    document.addEventListener('DOMContentLoaded', function () {
        var input     = document.getElementById('productImages');
        var grid      = document.getElementById('imagePreviewGrid');
        var countText = document.getElementById('imageCountText');

        if (!input || !grid || !countText) {
            return;
        }

        var selectedFiles = [];   // master list of File objects that will be submitted
        var previewUrls   = [];

        // DataTransfer lets us rebuild input.files after removals (all modern browsers)
        var supportsDataTransfer = (function () {
            try { return !!new DataTransfer(); } catch (e) { return false; }
        })();

        function fileKey(file) {
            return file.name + '|' + file.size + '|' + file.lastModified;
        }

        function syncInputFiles() {
            if (!supportsDataTransfer) {
                return;
            }
            var dt = new DataTransfer();
            selectedFiles.forEach(function (file) {
                dt.items.add(file);
            });
            input.files = dt.files;
        }

        function renderPreviews() {
            // release old object URLs
            previewUrls.forEach(function (url) { URL.revokeObjectURL(url); });
            previewUrls = [];

            grid.innerHTML = '';

            selectedFiles.forEach(function (file, index) {
                var url = URL.createObjectURL(file);
                previewUrls.push(url);

                var item = document.createElement('div');
                item.className = 'img-preview-item';

                var img = document.createElement('img');
                img.src = url;
                img.alt = file.name;

                var badge = document.createElement('span');
                badge.className = 'img-index';
                badge.textContent = (index === 0) ? 'Main' : ('#' + (index + 1));

                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'remove-btn';
                btn.title = 'Remove this image';
                btn.setAttribute('aria-label', 'Remove image');
                btn.innerHTML = '&times;';

                btn.addEventListener('click', function () {
                    selectedFiles.splice(index, 1);
                    syncInputFiles();
                    renderPreviews();
                });

                item.appendChild(img);
                item.appendChild(btn);
                item.appendChild(badge);
                grid.appendChild(item);
            });

            if (selectedFiles.length > 0) {
                countText.textContent = selectedFiles.length + ' image'
                    + (selectedFiles.length > 1 ? 's' : '')
                    + ' selected. The first image will be used as the main image.';
            } else {
                countText.textContent = 'No images selected yet.';
            }
        }

        input.addEventListener('change', function () {
            var incoming = Array.prototype.slice.call(input.files);

            // Very old browser: no DataTransfer support, just mirror the input
            if (!supportsDataTransfer) {
                selectedFiles = incoming;
                renderPreviews();
                return;
            }

            // Merge new picks with the existing selection (dedupe by name+size+date)
            var existingKeys = selectedFiles.map(fileKey);

            incoming.forEach(function (file) {
                var key = fileKey(file);
                if (existingKeys.indexOf(key) === -1) {
                    selectedFiles.push(file);
                    existingKeys.push(key);
                }
            });

            syncInputFiles();
            renderPreviews();
        });

        // Safety net if every preview was removed
        var form = input.closest('form');
        if (form) {
            form.addEventListener('submit', function (e) {
                if (!input.files || input.files.length === 0) {
                    e.preventDefault();
                    alert('Please select at least one product image.');
                }
            });
        }
    });

    /* ================= EXISTING ADMIN BEHAVIOUR ================= */
    $(document).ready(function () {
        // AJAX Subcategory
        $('#category').change(function () {
            var category_id = $(this).val();
            $('#subcategory').html('<option value="">Loading...</option>');
            if (category_id === '') {
                $('#subcategory').html('<option value="">Select Sub Category</option>');
                return;
            }
            $.ajax({
                url: 'get_subcategory.php',
                type: 'POST',
                data: { category_id: category_id },
                success: function (data) { $('#subcategory').html(data); },
                error: function () { $('#subcategory').html('<option value="">Unable to load subcategories</option>'); }
            });
        });

        // Live update the summary text as sections are checked
        $(document).on('change', 'input[name="section_id[]"]', function () {
            var count = $('input[name="section_id[]"]:checked').length;
            var summary = $('#sectionSummary');
            if (count > 0) {
                summary.text(count + ' section' + (count > 1 ? 's' : '') + ' selected');
            } else {
                summary.text('Select Sections (e.g. Featured, Hot Deals)');
            }
        });

        // Close the <details> dropdown when clicking outside
        $(document).on('click', function (e) {
            if (!$(e.target).closest('.section-dd').length) {
                $('.section-dd').removeAttr('open');
            }
        });
    });
</script>