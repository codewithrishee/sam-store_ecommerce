<link rel="stylesheet" href="admin-style.css">

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include("../config/db.php");
include "header.php";
include "sidebar.php";

if (!$conn) { die("Database connection failed!"); }

/* ================= CONFIG ================= */
define('IMG_DIR', __DIR__ . '/../images/');
define('IMG_WEB', '../images/');
define('MAX_IMAGE_BYTES', 5 * 1024 * 1024); // 5 MB per image
$ALLOWED_EXT  = ['jpg', 'jpeg', 'png', 'webp'];
$ALLOWED_MIME = ['image/jpeg', 'image/png', 'image/webp'];

/* ================= HELPERS ================= */
function makeSlug($text) {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

/**
 * Build the gallery for a product:
 * - product_images rows (primary)
 * - legacy products.image1..image3 (fallback)
 * Deduplicated by filename. First item = main image.
 */
function getProductGallery($conn, $productId, $productRow) {
    $gallery = [];
    $seen = [];

    $stmt = $conn->prepare("SELECT id, image_name FROM product_images WHERE product_id = ? ORDER BY id ASC");
    if ($stmt) {
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($r = $res->fetch_assoc()) {
            $name = $r['image_name'];
            if ($name === '' || isset($seen[$name])) continue;
            $seen[$name] = true;
            $gallery[] = ['id' => (int)$r['id'], 'name' => $name, 'legacy' => false];
        }
        $stmt->close();
    }

    foreach (['image1', 'image2', 'image3'] as $col) {
        $name = $productRow[$col] ?? '';
        if ($name === '' || isset($seen[$name])) continue;
        $seen[$name] = true;
        $gallery[] = ['id' => null, 'name' => $name, 'legacy' => true];
    }

    return $gallery;
}

/**
 * Copy legacy image1..image3 into product_images if missing.
 * Also inserts any gallery image not yet present.
 */
function syncLegacyIntoGallery($conn, $productId, $productRow) {
    $existing = [];
    $stmt = $conn->prepare("SELECT image_name FROM product_images WHERE product_id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) { $existing[$r['image_name']] = true; }
    $stmt->close();

    foreach (['image1', 'image2', 'image3'] as $col) {
        $name = $productRow[$col] ?? '';
        if ($name === '' || isset($existing[$name])) continue;
        if (!file_exists(IMG_DIR . $name)) continue;
        $stmt = $conn->prepare("INSERT INTO product_images (product_id, image_name) VALUES (?, ?)");
        $stmt->bind_param("is", $productId, $name);
        $stmt->execute();
        $stmt->close();
        $existing[$name] = true;
    }
}

/**
 * Rebuild products.image1..image3 from the first three product_images rows.
 */
function syncProductsImageColumns($conn, $productId) {
    $names = [];
    $stmt = $conn->prepare("SELECT image_name FROM product_images WHERE product_id = ? ORDER BY id ASC LIMIT 3");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) { $names[] = $r['image_name']; }
    $stmt->close();

    $i1 = $names[0] ?? '';
    $i2 = $names[1] ?? '';
    $i3 = $names[2] ?? '';

    $stmt = $conn->prepare("UPDATE products SET image1 = ?, image2 = ?, image3 = ? WHERE id = ?");
    $stmt->bind_param("sssi", $i1, $i2, $i3, $productId);
    $stmt->execute();
    $stmt->close();
}

/**
 * Validate & move a single uploaded file. Returns filename or throws.
 */
function handleUploadedImage($file, array $allowedExt, array $allowedMime) {
    if (!isset($file['error']) || is_array($file['error'])) {
        throw new Exception("Invalid upload payload.");
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Upload error code: " . (int)$file['error']);
    }
    if ($file['size'] <= 0 || $file['size'] > MAX_IMAGE_BYTES) {
        throw new Exception("File too large (max 5 MB).");
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        throw new Exception("Upload could not be verified.");
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) {
        throw new Exception("Only JPG, JPEG, PNG and WEBP are allowed.");
    }

    $mime = null;
    if (function_exists('mime_content_type')) {
        $mime = @mime_content_type($file['tmp_name']);
    }
    if (!$mime) {
        $info = @getimagesize($file['tmp_name']);
        $mime = $info['mime'] ?? '';
    }
    if (!in_array($mime, $allowedMime, true)) {
        throw new Exception("Invalid image type (got: " . htmlspecialchars($mime) . ").");
    }

    $probe = @getimagesize($file['tmp_name']);
    if ($probe === false) {
        throw new Exception("File is not a valid image.");
    }

    $newName = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    if (!@move_uploaded_file($file['tmp_name'], IMG_DIR . $newName)) {
        throw new Exception("Unable to move uploaded file.");
    }
    return $newName;
}

/**
 * Normalize $_FILES['images'] into a flat list of file entries.
 */
function normalizeFilesArray($files) {
    $out = [];
    if (!isset($files['name'])) return $out;

    if (is_array($files['name'])) {
        $count = count($files['name']);
        for ($i = 0; $i < $count; $i++) {
            if ((int)$files['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
            $out[] = [
                'name'     => $files['name'][$i],
                'type'     => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error'    => $files['error'][$i],
                'size'     => $files['size'][$i],
            ];
        }
    } else {
        if ((int)$files['error'] !== UPLOAD_ERR_NO_FILE) {
            $out[] = $files;
        }
    }
    return $out;
}

/* ================= DELETE PRODUCT ================= */
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    // Gather gallery + legacy images up front
    $stmt = $conn->prepare("SELECT image1, image2, image3 FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $imgData = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $filesToDelete = [];
    if ($imgData) {
        foreach (['image1', 'image2', 'image3'] as $col) {
            if (!empty($imgData[$col])) $filesToDelete[$imgData[$col]] = true;
        }
    }
    $stmt = $conn->prepare("SELECT image_name FROM product_images WHERE product_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) {
        if (!empty($r['image_name'])) $filesToDelete[$r['image_name']] = true;
    }
    $stmt->close();

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("DELETE FROM product_images WHERE product_id = ?");
        $stmt->bind_param("i", $id); $stmt->execute(); $stmt->close();

        $stmt = $conn->prepare("DELETE FROM product_categories WHERE product_id = ?");
        $stmt->bind_param("i", $id); $stmt->execute(); $stmt->close();

        $stmt = $conn->prepare("DELETE FROM product_section WHERE product_id = ?");
        $stmt->bind_param("i", $id); $stmt->execute(); $stmt->close();

        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $id); $stmt->execute(); $stmt->close();

        $conn->commit();

        // Only delete physical files AFTER commit succeeds
        foreach (array_keys($filesToDelete) as $name) {
            $path = IMG_DIR . $name;
            if ($name !== '' && is_file($path)) {
                // Safety: skip if another product still references this filename
                $chk = $conn->prepare("SELECT COUNT(*) AS c FROM product_images WHERE image_name = ?");
                $chk->bind_param("s", $name);
                $chk->execute();
                $stillUsed = (int)($chk->get_result()->fetch_assoc()['c'] ?? 0);
                $chk->close();

                $chk2 = $conn->prepare("SELECT COUNT(*) AS c FROM products WHERE image1 = ? OR image2 = ? OR image3 = ?");
                $chk2->bind_param("sss", $name, $name, $name);
                $chk2->execute();
                $stillUsed += (int)($chk2->get_result()->fetch_assoc()['c'] ?? 0);
                $chk2->close();

                if ($stillUsed === 0) { @unlink($path); }
            }
        }

        echo "<script>alert('Product Deleted Successfully'); window.location='view_product.php';</script>";
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        die("Delete Error: " . htmlspecialchars($e->getMessage()));
    }
}

/* ================= FETCH EDIT PRODUCT ================= */
$editData = null; $selectedSections = []; $catData = null; $existingGallery = [];
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $id); $stmt->execute();
    $editData = $stmt->get_result()->fetch_assoc(); $stmt->close();
    if (!$editData) { die("Product not found."); }

    $stmt = $conn->prepare("SELECT category_id, subcategory_id FROM product_categories WHERE product_id = ? LIMIT 1");
    $stmt->bind_param("i", $id); $stmt->execute();
    $catData = $stmt->get_result()->fetch_assoc(); $stmt->close();
    if (!$catData) {
        $catData = ['category_id' => $editData['category_id'] ?? 0, 'subcategory_id' => $editData['subcategory_id'] ?? 0];
    }

    $stmt = $conn->prepare("SELECT section_id FROM product_section WHERE product_id = ?");
    $stmt->bind_param("i", $id); $stmt->execute();
    $secRes = $stmt->get_result();
    while ($s = $secRes->fetch_assoc()) { $selectedSections[] = (int)$s['section_id']; }
    $stmt->close();

    // Best-effort: backfill legacy image columns into the gallery table for display.
    // Committed immediately so the admin sees a unified gallery.
    syncLegacyIntoGallery($conn, $id, $editData);

    $existingGallery = getProductGallery($conn, $id, $editData);
}

/* ================= DELETE ONE EXISTING IMAGE (AJAX) ================= */
if (isset($_POST['delete_image']) && isset($_POST['product_id'])) {
    header('Content-Type: application/json');

    $productId = (int)$_POST['product_id'];
    $imageId   = isset($_POST['image_id']) && $_POST['image_id'] !== '' ? (int)$_POST['image_id'] : null;
    $imageName = trim($_POST['image_name'] ?? '');

    try {
        // Verify the product exists
        $stmt = $conn->prepare("SELECT id FROM products WHERE id = ?");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        if (!$stmt->get_result()->fetch_assoc()) {
            $stmt->close();
            throw new Exception("Product not found.");
        }
        $stmt->close();

        // Resolve which filename is targeted
        $targetName = '';
        if ($imageId !== null) {
            $stmt = $conn->prepare("SELECT image_name FROM product_images WHERE id = ? AND product_id = ? LIMIT 1");
            $stmt->bind_param("ii", $imageId, $productId);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if (!$row) throw new Exception("Image not found for this product.");
            $targetName = $row['image_name'];
        } elseif ($imageName !== '') {
            $targetName = $imageName;
        } else {
            throw new Exception("Missing image identifier.");
        }

        // Count current gallery size (gallery + legacy, deduplicated)
        $productRow = null;
        $stmt = $conn->prepare("SELECT image1, image2, image3 FROM products WHERE id = ?");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $productRow = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $current = getProductGallery($conn, $productId, $productRow);
        if (count($current) <= 1) {
            throw new Exception("Cannot delete the last remaining image unless a replacement is uploaded.");
        }

        // Ensure the target exists in the gallery and belongs to this product
        $foundInGallery = false;
        $legacyCol = null;
        foreach ($current as $item) {
            if ($item['name'] === $targetName) {
                if ($item['id'] !== null) $foundInGallery = true;
            }
        }
        // If filename lives only in a legacy column, mark the column for clearing
        if (!$foundInGallery) {
            foreach (['image1', 'image2', 'image3'] as $col) {
                if (($productRow[$col] ?? '') === $targetName) { $legacyCol = $col; break; }
            }
        }

        $conn->begin_transaction();
        try {
            if ($imageId !== null) {
                $stmt = $conn->prepare("DELETE FROM product_images WHERE id = ? AND product_id = ?");
                $stmt->bind_param("ii", $imageId, $productId);
                $stmt->execute();
                $stmt->close();
            } else {
                $stmt = $conn->prepare("DELETE FROM product_images WHERE image_name = ? AND product_id = ?");
                $stmt->bind_param("si", $targetName, $productId);
                $stmt->execute();
                $stmt->close();

                if ($legacyCol !== null) {
                    // Clear the specific legacy column
                    $sql = "UPDATE products SET {$legacyCol} = '' WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $productId);
                    $stmt->execute();
                    $stmt->close();
                }
            }

            // Re-sync first three columns from product_images
            syncProductsImageColumns($conn, $productId);

            // Re-fetch post-state to determine if physical file is still referenced
            $stmt = $conn->prepare("SELECT image1, image2, image3 FROM products WHERE id = ?");
            $stmt->bind_param("i", $productId);
            $stmt->execute();
            $after = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            $conn->commit();

            // Determine whether file is still referenced by anything
            $stillUsed = false;
            if (in_array($targetName, [$after['image1'] ?? '', $after['image2'] ?? '', $after['image3'] ?? ''], true)) {
                $stillUsed = true;
            }
            if (!$stillUsed) {
                $chk = $conn->prepare("SELECT COUNT(*) AS c FROM product_images WHERE image_name = ?");
                $chk->bind_param("s", $targetName);
                $chk->execute();
                $stillUsed = ((int)($chk->get_result()->fetch_assoc()['c'] ?? 0)) > 0;
                $chk->close();
            }
            if (!$stillUsed && $targetName !== '' && is_file(IMG_DIR . $targetName)) {
                @unlink(IMG_DIR . $targetName);
            }

            echo json_encode(['ok' => true]);
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }
    } catch (Exception $e) {
        echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
        exit;
    }
}

/* ================= UPDATE PRODUCT ================= */
if (isset($_POST['update_product'])) {
    $id = (int)$_POST['id'];
    $product_name = trim($_POST['product_name'] ?? '');
    $short_description = trim($_POST['short_description'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $sku = trim($_POST['sku'] ?? '');
    $brand = trim($_POST['brand'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($slug === '') { $slug = makeSlug($product_name); } else { $slug = makeSlug($slug); }

    $old_price = (float)($_POST['old_price'] ?? 0);
    $new_price = (float)($_POST['new_price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $rating = (int)($_POST['rating'] ?? 5);
    $label = $_POST['label'] ?? '';
    $material = trim($_POST['material'] ?? '');
    $color = trim($_POST['color'] ?? '');
    $size = trim($_POST['size'] ?? '');
    $tags = trim($_POST['tags'] ?? '');
    $meta_title = trim($_POST['meta_title'] ?? '');
    $meta_description = trim($_POST['meta_description'] ?? '');
    $meta_keywords = trim($_POST['meta_keywords'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $subcategory_id = (int)($_POST['subcategory_id'] ?? 0);
    $sections = $_POST['section_id'] ?? [];

    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $id); $stmt->execute();
    $oldData = $stmt->get_result()->fetch_assoc(); $stmt->close();
    if (!$oldData) { die("Product not found."); }

    // Ensure any legacy image columns are represented in the gallery table
    // so syncProductsImageColumns() sees a complete picture.
    syncLegacyIntoGallery($conn, $id, $oldData);

    // Gallery snapshot BEFORE this request (for diff on cleanup)
    $galleryBefore = getProductGallery($conn, $id, $oldData);

    // ---- 1) Handle image deletions submitted via form (image_ids_to_delete[]) ----
    $deleteIds = [];
    if (!empty($_POST['image_ids_to_delete']) && is_array($_POST['image_ids_to_delete'])) {
        foreach ($_POST['image_ids_to_delete'] as $v) { $deleteIds[] = (int)$v; }
    }
    $deleteLegacyNames = [];
    if (!empty($_POST['legacy_images_to_delete']) && is_array($_POST['legacy_images_to_delete'])) {
        foreach ($_POST['legacy_images_to_delete'] as $v) {
            $v = trim((string)$v);
            if ($v !== '') $deleteLegacyNames[] = $v;
        }
    }

    // ---- 2) Upload new images ----
    $newUploaded = [];
    $uploadErrors = [];
    $files = normalizeFilesArray($_FILES['images'] ?? []);
    foreach ($files as $f) {
        try {
            $newUploaded[] = handleUploadedImage($f, $ALLOWED_EXT, $ALLOWED_MIME);
        } catch (Exception $e) {
            $uploadErrors[] = $e->getMessage();
        }
    }
    if (!empty($uploadErrors)) {
        // Roll back any files we already saved
        foreach ($newUploaded as $n) { @unlink(IMG_DIR . $n); }
        die("Image upload error: " . htmlspecialchars(implode(' | ', $uploadErrors)));
    }

    // Safety net: block deleting the last image unless replacement is uploaded
    $remainingAfterDeletes = 0;
    foreach ($galleryBefore as $item) {
        $isDeleting = ($item['id'] !== null && in_array($item['id'], $deleteIds, true))
                   || ($item['id'] === null && in_array($item['name'], $deleteLegacyNames, true));
        if (!$isDeleting) $remainingAfterDeletes++;
    }
    if (count($galleryBefore) > 0 && $remainingAfterDeletes === 0 && count($newUploaded) === 0) {
        die("Cannot delete the last remaining image. Please upload a replacement image first.");
    }

    // New images are inserted FIRST when gallery is empty so first upload becomes main image
    $conn->begin_transaction();
    try {
        // ---- Delete selected gallery rows (only for THIS product) ----
        foreach ($deleteIds as $did) {
            $stmt = $conn->prepare("DELETE FROM product_images WHERE id = ? AND product_id = ?");
            $stmt->bind_param("ii", $did, $id);
            $stmt->execute();
            $stmt->close();
        }

        // ---- Delete selected legacy-only filenames ----
        foreach ($deleteLegacyNames as $name) {
            // Remove any matching gallery row for this product
            $stmt = $conn->prepare("DELETE FROM product_images WHERE image_name = ? AND product_id = ?");
            $stmt->bind_param("si", $name, $id);
            $stmt->execute();
            $stmt->close();

            // Clear matching products column
            foreach (['image1', 'image2', 'image3'] as $col) {
                if (($oldData[$col] ?? '') === $name) {
                    $sql = "UPDATE products SET {$col} = '' WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        }

        // ---- Insert new uploaded images ----
        if (!empty($newUploaded)) {
            $stmt = $conn->prepare("INSERT INTO product_images (product_id, image_name) VALUES (?, ?)");
            foreach ($newUploaded as $name) {
                $stmt->bind_param("is", $id, $name);
                $stmt->execute();
            }
            $stmt->close();
        }

        // ---- Sync image1/image2/image3 from product_images ----
        syncProductsImageColumns($conn, $id);

        // ---- Main UPDATE (26 placeholders total) ----
        $status = (int)$oldData['status'];
        $show_in_shop = (int)$oldData['show_in_shop'];

        $sql = "UPDATE products SET
                    product_name = ?, short_description = ?, slug = ?, sku = ?, brand = ?,
                    description = ?, old_price = ?, new_price = ?, stock = ?, rating = ?,
                    label = ?, material = ?, color = ?, size = ?, tags = ?,
                    meta_title = ?, meta_description = ?, meta_keywords = ?,
                    status = ?, show_in_shop = ?,
                    category_id = ?, subcategory_id = ?
                WHERE id = ?";
        // Types:  s s s s s  s d d i i  s s s s s  s s s  i i  i i  i
        $types = "ssssssddii" . "sssss" . "sss" . "iiiii";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed (product update): " . $conn->error);
        }
        $stmt->bind_param(
            $types,
            $product_name, $short_description, $slug, $sku, $brand,
            $description, $old_price, $new_price, $stock, $rating,
            $label, $material, $color, $size, $tags,
            $meta_title, $meta_description, $meta_keywords,
            $status, $show_in_shop,
            $category_id, $subcategory_id,
            $id
        );
        if (!$stmt->execute()) {
            throw new Exception("Update failed: " . $stmt->error);
        }
        $stmt->close();

        // ---- Categories ----
        $stmt = $conn->prepare("DELETE FROM product_categories WHERE product_id = ?");
        $stmt->bind_param("i", $id); $stmt->execute(); $stmt->close();

        $stmt = $conn->prepare("INSERT INTO product_categories (product_id, category_id, subcategory_id) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $id, $category_id, $subcategory_id); $stmt->execute(); $stmt->close();

        // ---- Sections ----
        $stmt = $conn->prepare("DELETE FROM product_section WHERE product_id = ?");
        $stmt->bind_param("i", $id); $stmt->execute(); $stmt->close();

        if (!empty($sections)) {
            $stmt = $conn->prepare("INSERT INTO product_section (product_id, section_id) VALUES (?, ?)");
            foreach ($sections as $sec_id) {
                $sec_id = (int)$sec_id;
                $stmt->bind_param("ii", $id, $sec_id);
                $stmt->execute();
            }
            $stmt->close();
        }

        $conn->commit();

        // ---- Post-commit cleanup of orphaned files ----
        // Files that used to be in the gallery but are no longer referenced.
        $galleryAfterRows = [];
        $stmt = $conn->prepare("SELECT image_name FROM product_images WHERE product_id = ?");
        $stmt->bind_param("i", $id); $stmt->execute();
        $res = $stmt->get_result();
        while ($r = $res->fetch_assoc()) { $galleryAfterRows[$r['image_name']] = true; }
        $stmt->close();

        $stmt = $conn->prepare("SELECT image1, image2, image3 FROM products WHERE id = ?");
        $stmt->bind_param("i", $id); $stmt->execute();
        $afterRow = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $stillReferenced = $galleryAfterRows;
        foreach (['image1', 'image2', 'image3'] as $col) {
            if (!empty($afterRow[$col])) $stillReferenced[$afterRow[$col]] = true;
        }

        $candidates = [];
        foreach ($galleryBefore as $item) { $candidates[$item['name']] = true; }
        foreach ($newUploaded as $n) {
            // New uploads are intentionally kept (they are in the DB now)
            unset($candidates[$n]);
        }

        foreach (array_keys($candidates) as $name) {
            if (isset($stillReferenced[$name])) continue;
            if ($name === '' || !is_file(IMG_DIR . $name)) continue;

            // Cross-product safety check
            $chk = $conn->prepare("SELECT COUNT(*) AS c FROM product_images WHERE image_name = ?");
            $chk->bind_param("s", $name); $chk->execute();
            $usedElsewhere = (int)($chk->get_result()->fetch_assoc()['c'] ?? 0);
            $chk->close();

            $chk = $conn->prepare("SELECT COUNT(*) AS c FROM products WHERE image1 = ? OR image2 = ? OR image3 = ?");
            $chk->bind_param("sss", $name, $name, $name); $chk->execute();
            $usedElsewhere += (int)($chk->get_result()->fetch_assoc()['c'] ?? 0);
            $chk->close();

            if ($usedElsewhere === 0) { @unlink(IMG_DIR . $name); }
        }

        echo "<script>alert('Product Updated Successfully'); window.location='view_product.php';</script>";
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        foreach ($newUploaded as $newImage) {
            if (is_file(IMG_DIR . $newImage)) { @unlink(IMG_DIR . $newImage); }
        }
        die("Update Error: " . htmlspecialchars($e->getMessage()));
    }
}
?>

<style>
    /* Layout & Theme */
    body { background: #f4f7f6; font-family: 'Manrope', sans-serif; }
    .main-content { margin-left: 290px; padding: 35px 40px; margin-top: 20px; min-height: calc(100vh - 75px); }

    .page-card { background: #fff; border-radius: 20px; padding: 35px; border: 1px solid #efe6ec; box-shadow: 0 4px 15px rgba(46, 50, 84, 0.03); }
    .page-title { font-family: 'Outfit', sans-serif; font-weight: 800; color: #2e3254; margin-bottom: 25px; font-size: 1.5rem; }

    /* Form Sections */
    .form-section { background: #fafbfc; border: 1px solid #efe6ec; border-radius: 16px; padding: 25px; margin-bottom: 25px; }
    .section-title { font-family: 'Outfit', sans-serif; font-weight: 800; color: #2e3254; font-size: 1.1rem; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; border-bottom: 2px dashed #efe6ec; padding-bottom: 12px; }
    .section-title i { color: #ff4d6d; }

    /* Form Controls */
    .form-label { font-family: 'Outfit', sans-serif; font-size: 0.85rem; font-weight: 700; color: #2e3254; margin-bottom: 8px; }
    .form-control, .form-select { border-radius: 10px; border: 1.5px solid #e2e8f0; padding: 10px 14px; box-shadow: none; font-size: 0.9rem; color: #2e3254; background-color: #fff; transition: all 0.3s; }
    .form-control:focus, .form-select:focus { border-color: #ff4d6d; box-shadow: 0 0 0 4px #fff2f5; }

    /* Button */
    .btn-admin { background: linear-gradient(135deg, #ff4d6d, #e63950); color: #fff; border: none; padding: 14px 32px; border-radius: 999px; font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 0.95rem; letter-spacing: 0.05em; text-transform: uppercase; box-shadow: 0 8px 20px rgba(255, 77, 109, 0.25); transition: all 0.3s; display: inline-block; }
    .btn-admin:hover { transform: translateY(-2px); box-shadow: 0 12px 25px rgba(255, 77, 109, 0.35); color: #fff; }

    /* Table */
    .table-card { background: #fff; border-radius: 20px; overflow: hidden; border: 1px solid #efe6ec; box-shadow: 0 4px 15px rgba(46, 50, 84, 0.03); }
    .table thead th { background: #fafbfc; border-bottom: 1px solid #efe6ec; padding: 18px 24px; font-size: 0.75rem; text-transform: uppercase; color: #8a8fa3; font-weight: 800; letter-spacing: 0.05em; }
    .table tbody td { padding: 16px 24px; vertical-align: middle; border-bottom: 1px solid #f8f9fa; color: #56607a; font-size: 0.9rem; }
    .table tbody tr:hover { background: #fafbfc; }

    /* Action Buttons */
    .btn-edit { background: #ffedd5; color: #ea580c; border: none; border-radius: 8px; font-weight: 700; font-size: 0.8rem; padding: 8px 14px; transition: 0.3s; display: inline-flex; align-items: center; gap: 6px; }
    .btn-edit:hover { background: #ea580c; color: #fff; }
    .btn-delete { background: #fee2e2; color: #dc2626; border: none; border-radius: 8px; font-weight: 700; font-size: 0.8rem; padding: 8px 14px; transition: 0.3s; display: inline-flex; align-items: center; gap: 6px; }
    .btn-delete:hover { background: #dc2626; color: #fff; }

    /* Badges */
    .badge-cat { background: #e0f2fe; color: #0284c7; padding: 4px 10px; border-radius: 6px; font-family: 'Outfit', sans-serif; font-size: 0.7rem; font-weight: 700; margin-right: 4px; margin-bottom: 4px; display:inline-block;}
    .badge-sec { background: #f3e8ff; color: #9333ea; padding: 4px 10px; border-radius: 6px; font-family: 'Outfit', sans-serif; font-size: 0.7rem; font-weight: 700; margin-right: 4px; margin-bottom: 4px; display:inline-block;}

    .stock-good { color: #16a34a; font-weight: 700; }
    .stock-low { color: #dc2626; font-weight: 700; }

    /* ===== Native <details> dropdown for Sections (no Bootstrap needed) ===== */
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
    .section-dd summary::-webkit-details-marker { display: none; }
    .section-dd summary::after {
        content: '\25BE';
        font-size: 0.9rem;
        color: #56607a;
        transition: transform 0.2s;
    }
    .section-dd[open] summary::after { transform: rotate(180deg); }
    .section-dd[open] summary { border-bottom: 1px solid #efe6ec; border-radius: 10px 10px 0 0; }

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
    .section-dd-item:hover { background: #fff2f5; color: #ff4d6d; }
    .section-dd-item input[type="checkbox"] { margin: 0; cursor: pointer; }

    /* ===== Image gallery (new multiple-image system) ===== */
    .image-gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }
    .image-card {
        position: relative;
        background: #fff;
        border: 1.5px solid #efe6ec;
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.25s ease;
    }
    .image-card:hover { box-shadow: 0 6px 18px rgba(46, 50, 84, 0.08); transform: translateY(-2px); }
    .image-card.is-main { border-color: #ff4d6d; box-shadow: 0 0 0 3px #fff2f5; }
    .image-card img {
        display: block;
        width: 100%;
        height: 130px;
        object-fit: cover;
    }
    .image-card .main-badge {
        position: absolute;
        top: 8px;
        left: 8px;
        background: linear-gradient(135deg, #ff4d6d, #e63950);
        color: #fff;
        font-size: 0.65rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        padding: 3px 9px;
        border-radius: 999px;
        z-index: 2;
    }
    .image-card .image-delete-btn {
        position: absolute;
        top: 8px;
        right: 8px;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: rgba(220, 38, 38, 0.92);
        color: #fff;
        border: none;
        font-size: 0.75rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        opacity: 0;
        transition: opacity 0.2s;
        z-index: 2;
    }
    .image-card:hover .image-delete-btn { opacity: 1; }
    .image-card .image-delete-btn:hover { background: #b91c1c; }
    .image-card .image-meta {
        padding: 6px 10px;
        font-size: 0.7rem;
        color: #8a8fa3;
        background: #fafbfc;
        border-top: 1px solid #f4f4f7;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .image-card.pending { border-style: dashed; border-color: #bae6fd; background: #f0f9ff; }

    .new-preview-wrap {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 14px;
    }
    .new-preview-item {
        position: relative;
        width: 110px;
        height: 110px;
        border-radius: 10px;
        overflow: hidden;
        border: 1.5px solid #e0f2fe;
        background: #f0f9ff;
    }
    .new-preview-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .new-preview-item .remove-new-btn {
        position: absolute;
        top: 4px;
        right: 4px;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: rgba(220, 38, 38, 0.92);
        color: #fff;
        border: none;
        font-size: 0.65rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }
    .new-preview-item .remove-new-btn:hover { background: #b91c1c; }

    @media (max-width: 991px) { .main-content { margin-left: 85px; padding: 25px 20px; margin-top: 65px; } .page-card { padding: 20px; } }
</style>

<div class="main-content">

    <?php if ($editData) { ?>
    <!-- ================= EDIT PRODUCT FORM ================= -->
    <div class="page-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="page-title m-0">Edit Product</h3>
            <a href="view_product.php" class="btn btn-light border rounded-pill px-4">Cancel Edit</a>
        </div>

        <form method="POST" enctype="multipart/form-data" id="editProductForm">
            <input type="hidden" name="id" value="<?= (int)$editData['id']; ?>">
            <div id="deleteInputsWrap"></div>

            <div class="row g-4">
                <!-- SECTION 1: BASIC INFO -->
                <div class="col-12">
                    <div class="form-section">
                        <h5 class="section-title"><i class="fa-solid fa-box-open"></i> Basic Details</h5>
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Product Name <span class="text-danger">*</span></label>
                                <input type="text" name="product_name" value="<?= htmlspecialchars($editData['product_name'] ?? ''); ?>" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Brand</label>
                                <input type="text" name="brand" value="<?= htmlspecialchars($editData['brand'] ?? ''); ?>" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Slug</label>
                                <input type="text" name="slug" value="<?= htmlspecialchars($editData['slug'] ?? ''); ?>" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">SKU</label>
                                <input type="text" name="sku" value="<?= htmlspecialchars($editData['sku'] ?? ''); ?>" class="form-control">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Short Description</label>
                                <textarea name="short_description" class="form-control" rows="2" maxlength="500"><?= htmlspecialchars($editData['short_description'] ?? ''); ?></textarea>
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
                                <input type="number" name="old_price" value="<?= htmlspecialchars($editData['old_price']); ?>" class="form-control" min="0" step="0.01" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">New Price (₹)</label>
                                <input type="number" name="new_price" value="<?= htmlspecialchars($editData['new_price']); ?>" class="form-control" min="0" step="0.01" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Stock</label>
                                <input type="number" name="stock" value="<?= (int)($editData['stock'] ?? 0); ?>" class="form-control" min="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Rating</label>
                                <select name="rating" class="form-select">
                                    <?php for ($i = 5; $i >= 1; $i--) { ?>
                                    <option value="<?= $i; ?>" <?= (int)$editData['rating'] === $i ? 'selected' : ''; ?>><?= $i; ?> Star</option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Label</label>
                                <select name="label" class="form-select">
                                    <option value="">None</option>
                                    <?php foreach (['Sale', 'Best Seller', 'New'] as $item) { ?>
                                    <option value="<?= htmlspecialchars($item); ?>" <?= ($editData['label'] ?? '') === $item ? 'selected' : ''; ?>><?= htmlspecialchars($item); ?></option>
                                    <?php } ?>
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
                                <label class="form-label">Category</label>
                                <select id="category" name="category_id" class="form-select" required>
                                    <option value="">Select Category</option>
                                    <?php $cat = mysqli_query($conn, "SELECT * FROM categories ORDER BY id DESC"); while ($c = mysqli_fetch_assoc($cat)) { ?>
                                    <option value="<?= (int)$c['id']; ?>" <?= (int)$catData['category_id'] === (int)$c['id'] ? 'selected' : ''; ?>><?= htmlspecialchars($c['name']); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Sub Category</label>
                                <select id="subcategory" name="subcategory_id" class="form-select" required>
                                    <option value="">Select Sub Category</option>
                                    <?php
                                    $categoryForSub = (int)$catData['category_id'];
                                    $stmt = $conn->prepare("SELECT * FROM sub_categories WHERE category_id = ? ORDER BY id DESC");
                                    $stmt->bind_param("i", $categoryForSub); $stmt->execute();
                                    $sub = $stmt->get_result();
                                    while ($sc = $sub->fetch_assoc()) { ?>
                                    <option value="<?= (int)$sc['id']; ?>" <?= (int)$catData['subcategory_id'] === (int)$sc['id'] ? 'selected' : ''; ?>><?= htmlspecialchars($sc['name']); ?></option>
                                    <?php } $stmt->close(); ?>
                                </select>
                            </div>

                            <!-- ===== FIXED: Sections (native <details>, no Bootstrap) ===== -->
                            <div class="col-md-12">
                                <label class="form-label">Sections</label>
                                <details class="section-dd">
                                    <summary id="sectionSummary">
                                        <?php
                                        $preselectedCount = count($selectedSections);
                                        echo $preselectedCount > 0
                                            ? $preselectedCount . ' section' . ($preselectedCount > 1 ? 's' : '') . ' selected'
                                            : 'Select Sections';
                                        ?>
                                    </summary>
                                    <div class="section-dd-menu">
                                        <?php
                                        $sec = mysqli_query($conn, "SELECT * FROM sections ORDER BY id DESC");
                                        if (!$sec) {
                                            echo '<div class="p-2 text-danger small">Query error: '
                                               . htmlspecialchars(mysqli_error($conn)) . '</div>';
                                        } elseif (mysqli_num_rows($sec) === 0) {
                                            echo '<div class="p-2 text-muted small">No sections found in the database.</div>';
                                        } else {
                                            while ($s = mysqli_fetch_assoc($sec)) {
                                                $labelText = $s['name']
                                                          ?? $s['section_name']
                                                          ?? $s['title']
                                                          ?? ('Section #' . $s['id']);
                                                $isChecked = in_array((int)$s['id'], $selectedSections);
                                                ?>
                                                <label class="section-dd-item">
                                                    <input type="checkbox" name="section_id[]" value="<?= (int)$s['id']; ?>" <?= $isChecked ? 'checked' : ''; ?>>
                                                    <span><?= htmlspecialchars($labelText); ?></span>
                                                </label>
                                                <?php
                                            }
                                        }
                                        ?>
                                    </div>
                                </details>
                            </div>
                            <!-- ===== END Sections ===== -->

                        </div>
                    </div>
                </div>

                <!-- SECTION 4: ATTRIBUTES -->
                <div class="col-12">
                    <div class="form-section">
                        <h5 class="section-title"><i class="fa-solid fa-list-check"></i> Attributes & Specifications</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Material</label>
                                <input type="text" name="material" value="<?= htmlspecialchars($editData['material'] ?? ''); ?>" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Color</label>
                                <input type="text" name="color" value="<?= htmlspecialchars($editData['color'] ?? ''); ?>" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Size</label>
                                <input type="text" name="size" value="<?= htmlspecialchars($editData['size'] ?? ''); ?>" class="form-control">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Tags</label>
                                <input type="text" name="tags" value="<?= htmlspecialchars($editData['tags'] ?? ''); ?>" class="form-control">
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
                                <label class="form-label">Full Description</label>
                                <textarea name="description" class="form-control" rows="4" required><?= htmlspecialchars($editData['description'] ?? ''); ?></textarea>
                            </div>
                            <div class="col-12"><hr class="my-2"></div>
                            <div class="col-12">
                                <label class="form-label">SEO Meta Title</label>
                                <input type="text" name="meta_title" value="<?= htmlspecialchars($editData['meta_title'] ?? ''); ?>" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">SEO Meta Description</label>
                                <textarea name="meta_description" class="form-control" rows="2"><?= htmlspecialchars($editData['meta_description'] ?? ''); ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">SEO Meta Keywords</label>
                                <textarea name="meta_keywords" class="form-control" rows="2"><?= htmlspecialchars($editData['meta_keywords'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 6: IMAGES (NEW MULTIPLE-IMAGE SYSTEM) -->
                <div class="col-12">
                    <div class="form-section">
                        <h5 class="section-title"><i class="fa-solid fa-images"></i> Product Images</h5>

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label m-0">Existing Images</label>
                            <small class="text-muted" style="font-size:0.75rem;">
                                First image is the <strong class="text-danger">Main Image</strong>. Hover a thumbnail to delete.
                            </small>
                        </div>

                        <?php if (empty($existingGallery)) { ?>
                            <div class="alert alert-warning py-2 px-3 mb-3" style="font-size:0.85rem;">
                                This product has no images yet. Upload at least one image below.
                            </div>
                        <?php } else { ?>
                        <div class="image-gallery-grid" id="existingGallery">
                            <?php foreach ($existingGallery as $idx => $item) {
                                $isMain = ($idx === 0);
                                $src = IMG_WEB . rawurlencode($item['name']);
                            ?>
                            <div class="image-card <?= $isMain ? 'is-main' : ''; ?>"
                                 data-image-id="<?= $item['id'] !== null ? (int)$item['id'] : ''; ?>"
                                 data-image-name="<?= htmlspecialchars($item['name']); ?>"
                                 data-legacy="<?= $item['legacy'] ? '1' : '0'; ?>">
                                <?php if ($isMain) { ?><span class="main-badge">Main</span><?php } ?>
                                <button type="button" class="image-delete-btn" title="Delete this image">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                                <img src="<?= $src; ?>" alt="<?= htmlspecialchars($item['name']); ?>">
                                <div class="image-meta"><?= htmlspecialchars($item['name']); ?></div>
                            </div>
                            <?php } ?>
                        </div>
                        <?php } ?>

                        <hr class="my-3">

                        <label class="form-label">Add New Images</label>
                        <input type="file"
                               name="images[]"
                               id="newImagesInput"
                               class="form-control"
                               accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                               multiple>
                        <small class="text-muted d-block mt-1" style="font-size:0.75rem;">
                            Allowed: JPG, JPEG, PNG, WEBP &middot; Max 5 MB per file &middot; Select multiple at once.
                        </small>

                        <div class="new-preview-wrap" id="newPreviewWrap"></div>

                        <div id="newImageError" class="text-danger small mt-2" style="display:none;"></div>
                    </div>
                </div>

                <!-- SUBMIT BUTTON -->
                <div class="col-12 text-end mt-4">
                    <button type="submit" name="update_product" class="btn-admin">
                        <i class="fa-solid fa-check-circle me-2"></i> Update Product
                    </button>
                </div>
            </div>
        </form>
    </div>

    <?php } else { ?>
    <!-- ================= ALL PRODUCTS TABLE ================= -->
    <div class="table-card">
        <div class="p-4 border-bottom d-flex justify-content-between align-items-center bg-white">
            <h3 class="page-title m-0">All Products</h3>
            <a href="add_product.php" class="btn-admin text-decoration-none" style="font-size: 0.8rem; padding: 10px 20px;">
                <i class="fa-solid fa-plus"></i> Add New Product
            </a>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th width="5%">#</th>
                        <th width="10%">Image</th>
                        <th width="25%">Name & Price</th>
                        <th width="20%">Categories & Sections</th>
                        <th width="15%">Stock</th>
                        <th width="10%">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = $conn->query("
                        SELECT p.*,
                        GROUP_CONCAT(DISTINCT c.name SEPARATOR ', ') AS categories,
                        GROUP_CONCAT(DISTINCT s.name SEPARATOR ', ') AS sections
                        FROM products p
                        LEFT JOIN product_categories pc ON p.id = pc.product_id
                        LEFT JOIN categories c ON pc.category_id = c.id
                        LEFT JOIN product_section ps ON p.id = ps.product_id
                        LEFT JOIN sections s ON ps.section_id = s.id
                        GROUP BY p.id
                        ORDER BY p.id DESC
                    ");
                    $i = 1;
                    if ($query && $query->num_rows > 0) {
                        while ($row = $query->fetch_assoc()) {
                    ?>
                    <tr>
                        <td class="fw-bold text-muted"><?= $i++; ?></td>
                        <td>
                            <?php
                            // Prefer product_images main image, fall back to legacy image1
                            $mainImg = $row['image1'];
                            $piStmt = $conn->prepare("SELECT image_name FROM product_images WHERE product_id = ? ORDER BY id ASC LIMIT 1");
                            $pid = (int)$row['id'];
                            $piStmt->bind_param("i", $pid);
                            $piStmt->execute();
                            $piRow = $piStmt->get_result()->fetch_assoc();
                            $piStmt->close();
                            if ($piRow && !empty($piRow['image_name'])) { $mainImg = $piRow['image_name']; }
                            ?>
                            <img src="../images/<?= rawurlencode($mainImg); ?>" style="width:60px;height:60px;object-fit:cover;border-radius:12px;border:1px solid #efe6ec;">
                        </td>
                        <td>
                            <div class="fw-bold text-dark fs-6 mb-1"><?= htmlspecialchars($row['product_name']); ?></div>
                            <div class="text-danger fw-bold">₹<?= number_format((float)$row['new_price'], 2); ?></div>
                        </td>
                        <td>
                            <?php if(!empty($row['categories'])) { ?>
                                <span class="badge-cat"><i class="fa-solid fa-layer-group me-1"></i> <?= htmlspecialchars($row['categories']); ?></span>
                            <?php } ?>
                            <?php if(!empty($row['sections'])) { ?>
                                <span class="badge-sec"><i class="fa-solid fa-star me-1"></i> <?= htmlspecialchars($row['sections']); ?></span>
                            <?php } ?>
                        </td>
                        <td>
                            <?php
                            $stock = (int)($row['stock'] ?? 0);
                            if($stock > 5) {
                                echo '<span class="stock-good"><i class="fa-solid fa-circle-check me-1"></i> ' . $stock . ' in stock</span>';
                            } elseif($stock > 0) {
                                echo '<span class="stock-low"><i class="fa-solid fa-triangle-exclamation me-1"></i> Only ' . $stock . ' left</span>';
                            } else {
                                echo '<span class="stock-low"><i class="fa-solid fa-circle-xmark me-1"></i> Out of stock</span>';
                            }
                            ?>
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                <a href="?edit=<?= (int)$row['id']; ?>" class="btn-edit text-decoration-none"><i class="fa-solid fa-pen"></i></a>
                                <a href="?delete=<?= (int)$row['id']; ?>" class="btn-delete text-decoration-none" onclick="return confirm('Are you sure you want to delete this product? All its images will be removed too.')"><i class="fa-solid fa-trash"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php
                        }
                    } else {
                        echo '<tr><td colspan="6" class="text-center py-5 text-muted"><i class="fa-solid fa-box-open fs-2 mb-3 text-light"></i><br>No products found. Click "Add New Product" to create one.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php } ?>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    $(document).ready(function(){
        // AJAX Subcategory loader
        $('#category').change(function(){
            var category_id = $(this).val();
            $('#subcategory').html('<option value="">Loading...</option>');
            if (!category_id) {
                $('#subcategory').html('<option value="">Select Sub Category</option>');
                return;
            }
            $.ajax({
                url: 'get_subcategory.php',
                type: 'POST',
                data: { category_id: category_id },
                success: function(data) { $('#subcategory').html(data); },
                error: function() { $('#subcategory').html('<option value="">Unable to load subcategories</option>'); }
            });
        });

        // Update the Sections summary text as checkboxes change
        $(document).on('change', 'input[name="section_id[]"]', function(){
            var count = $('input[name="section_id[]"]:checked').length;
            var summary = $('#sectionSummary');
            if (count > 0) {
                summary.text(count + ' section' + (count > 1 ? 's' : '') + ' selected');
            } else {
                summary.text('Select Sections');
            }
        });

        // Close the Sections dropdown when clicking outside
        $(document).on('click', function(e){
            if (!$(e.target).closest('.section-dd').length) {
                $('.section-dd').removeAttr('open');
            }
        });

        /* ============================================================
           MULTIPLE-IMAGE SYSTEM: existing-image deletion
           ============================================================ */
        var productId = $('input[name="id"]').val();

        function markMainImage() {
            $('#existingGallery .image-card').removeClass('is-main').find('.main-badge').remove();
            var $first = $('#existingGallery .image-card').first();
            if ($first.length) {
                $first.addClass('is-main').prepend('<span class="main-badge">Main</span>');
            }
        }

        function galleryCount() {
            return $('#existingGallery .image-card').length;
        }

        $(document).on('click', '#existingGallery .image-delete-btn', function(e){
            e.preventDefault();
            var $card = $(this).closest('.image-card');
            var imageId = $card.data('image-id') || '';
            var imageName = $card.data('image-name') || '';
            var isLegacy = $card.data('legacy') === 1 || $card.data('legacy') === '1';

            // Safety: block deleting the last image in the UI
            if (galleryCount() <= 1) {
                alert('Cannot delete the last remaining image. Please upload a replacement image first.');
                return;
            }
            if (!confirm('Delete this image? This will be removed when you click "Update Product".')) return;

            if (!isLegacy && imageId !== '') {
                // Queued deletion for product_images row
                $('#deleteInputsWrap').append(
                    '<input type="hidden" name="image_ids_to_delete[]" value="' + imageId + '">'
                );
            } else if (isLegacy && imageName !== '') {
                // Queued deletion for legacy products.image* column
                $('#deleteInputsWrap').append(
                    $('<input>', {
                        type: 'hidden',
                        name: 'legacy_images_to_delete[]',
                        value: imageName
                    })
                );
            }

            $card.fadeOut(180, function(){
                $(this).remove();
                markMainImage();
            });
        });

        /* ============================================================
           MULTIPLE-IMAGE SYSTEM: new image selection + previews
           ============================================================ */
        var ALLOWED_EXTS = ['jpg', 'jpeg', 'png', 'webp'];
        var MAX_BYTES = 5 * 1024 * 1024;
        var selectedFiles = []; // array of File objects

        function syncFileInput() {
            var dt = new DataTransfer();
            selectedFiles.forEach(function(f){ dt.items.add(f); });
            document.getElementById('newImagesInput').files = dt.files;
        }

        function renderNewPreviews() {
            var $wrap = $('#newPreviewWrap');
            $wrap.empty();
            selectedFiles.forEach(function(file, idx){
                var url = URL.createObjectURL(file);
                var $item = $(
                    '<div class="new-preview-item" data-idx="' + idx + '">' +
                        '<img src="' + url + '" alt="">' +
                        '<button type="button" class="remove-new-btn" title="Remove"><i class="fa-solid fa-xmark"></i></button>' +
                    '</div>'
                );
                $item.find('img').on('load', function(){ URL.revokeObjectURL(url); });
                $wrap.append($item);
            });
        }

        function showNewImageError(msg) {
            $('#newImageError').text(msg).show();
        }
        function clearNewImageError() {
            $('#newImageError').text('').hide();
        }

        $('#newImagesInput').on('change', function(){
            clearNewImageError();
            var incoming = Array.prototype.slice.call(this.files || []);
            var valid = [];
            var errorMsg = '';

            incoming.forEach(function(file){
                var ext = (file.name.split('.').pop() || '').toLowerCase();
                if (ALLOWED_EXTS.indexOf(ext) === -1) {
                    errorMsg = 'Unsupported file type: ' + file.name + '. Only JPG, JPEG, PNG and WEBP are allowed.';
                    return;
                }
                if (file.size > MAX_BYTES) {
                    errorMsg = 'File too large: ' + file.name + ' (max 5 MB).';
                    return;
                }
                if (file.size <= 0) {
                    errorMsg = 'Empty file skipped: ' + file.name + '.';
                    return;
                }
                valid.push(file);
            });

            if (errorMsg) showNewImageError(errorMsg);

            // Append to existing selection, dedupe by name+size+lastModified
            valid.forEach(function(f){
                var key = f.name + '|' + f.size + '|' + f.lastModified;
                var exists = selectedFiles.some(function(g){
                    return (g.name + '|' + g.size + '|' + g.lastModified) === key;
                });
                if (!exists) selectedFiles.push(f);
            });

            syncFileInput();
            renderNewPreviews();
        });

        $(document).on('click', '.new-preview-item .remove-new-btn', function(){
            var idx = $(this).closest('.new-preview-item').data('idx');
            selectedFiles.splice(idx, 1);
            syncFileInput();
            renderNewPreviews();
            clearNewImageError();
        });

        /* ============================================================
           FINAL SUBMIT GUARD
           ============================================================ */
        $('#editProductForm').on('submit', function(e){
            var remainingExisting = $('#existingGallery .image-card').length;
            var newCount = selectedFiles.length;
            if (remainingExisting === 0 && newCount === 0) {
                e.preventDefault();
                alert('A product must have at least one image. Please upload an image before saving.');
                return false;
            }
        });
    });
</script>