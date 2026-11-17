
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

    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return "UPLOAD_FAILED";
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $mime = mime_content_type($file['tmp_name']);

    if (!in_array($ext, $allowedExts) || !in_array($mime, $allowedMimes)) {
        return "INVALID_TYPE";
    }

    if ($file['size'] > $maxSize) {
        return "TOO_LARGE";
    }

    $newName = time() . "_service_" . bin2hex(random_bytes(5)) . "." . $ext;

    $targetPath = "../images/" . $newName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $newName;
    }

    return "UPLOAD_FAILED";
}

/* ================= ADD SERVICE ================= */

if (isset($_POST['add_service'])) {

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $link = trim($_POST['link'] ?? '');
    $status = isset($_POST['status']) ? (int)$_POST['status'] : 1;

    $error = false;
    $image_name = null;

    if ($title === '' || $description === '') {
        echo "<script>alert('Please fill all required fields.');</script>";
        $error = true;
    }

    if (!$error) {

        $upload = uploadServiceImage($_FILES['image'] ?? []);

        if ($upload === "NO_IMAGE") {
            echo "<script>alert('Service image is required.');</script>";
            $error = true;

        } elseif ($upload === "INVALID_TYPE") {
            echo "<script>alert('Only JPG, JPEG, PNG and WEBP images are allowed.');</script>";
            $error = true;

        } elseif ($upload === "TOO_LARGE") {
            echo "<script>alert('Image size must be below 5MB.');</script>";
            $error = true;

        } elseif ($upload === "UPLOAD_FAILED") {
            echo "<script>alert('Image upload failed. Please try again.');</script>";
            $error = true;

        } else {
            $image_name = $upload;
        }
    }

    if (!$error && $image_name) {

        $stmt = $conn->prepare("
            INSERT INTO services (title, description, image, link, status)
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "ssssi",
            $title,
            $description,
            $image_name,
            $link,
            $status
        );

        if ($stmt->execute()) {

            echo "<script>
                alert('Service Added Successfully!');
                window.location='view_services.php';
            </script>";

        } else {

            if (file_exists("../images/" . $image_name)) {
                unlink("../images/" . $image_name);
            }

            echo "<script>alert('Database Error: " . addslashes($stmt->error) . "');</script>";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>

    <title>Add Service</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="admin-style.css">

    <style>

        body {
            background: #f4f7f6;
            font-family: 'Manrope', sans-serif;
        }

        .main-content {
            margin-left: 290px;
            padding: 30px 40px;
            margin-top: 20px;
            min-height: calc(100vh - 75px);
        }

        .page-card {
            background: #fff;
            border-radius: 20px;
            padding: 30px 40px;
            border: 1px solid #efe6ec;
            box-shadow: 0 4px 15px rgba(46, 50, 84, 0.03);
        }

        .page-title {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            color: #2e3254;
            margin-bottom: 25px;
        }

        .form-label {
            font-family: 'Outfit', sans-serif;
            font-size: 0.85rem;
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
            font-size: 0.9rem;
            color: #2e3254;
            background-color: #fafbfc;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #ff4d6d;
            box-shadow: 0 0 0 4px #fff2f5;
            background-color: #fff;
        }

        .preview-img {
            width: 120px;
            height: 120px;
            object-fit: contain;
            border-radius: 12px;
            border: 2px dashed #efe6ec;
            padding: 5px;
            display: none;
        }

        .btn-admin {
            background: linear-gradient(135deg, #ff4d6d, #e63950);
            color: #fff;
            border: none;
            padding: 12px 28px;
            border-radius: 999px;
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 0.9rem;
            box-shadow: 0 8px 20px rgba(255, 77, 109, 0.25);
            transition: all 0.3s;
        }

        .btn-admin:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(255, 77, 109, 0.35);
            color: #fff;
        }

        @media (max-width: 991px) {
            .main-content {
                margin-left: 85px;
                padding: 20px;
                margin-top: 65px;
            }

            .page-card {
                padding: 20px;
            }
        }

    </style>

</head>

<body>

<div class="main-content">

    <div class="page-card">

        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">

            <h3 class="page-title m-0">Add New Service</h3>

            <a href="view_services.php" class="btn btn-light border rounded-pill px-4">
                Back to List
            </a>

        </div>

        <form method="POST" enctype="multipart/form-data">

            <div class="row">

                <!-- SERVICE TITLE -->

                <div class="col-md-6 mb-3">

                    <label class="form-label">Service Title *</label>

                    <input
                        type="text"
                        name="title"
                        class="form-control"
                        placeholder="e.g. Pet Grooming"
                        maxlength="255"
                        required
                    >

                </div>

                <!-- SERVICE LINK -->

                <div class="col-md-6 mb-3">

                    <label class="form-label">Read More Link</label>

                    <input
                        type="text"
                        name="link"
                        class="form-control"
                        placeholder="e.g. service-details.php"
                    >

                </div>

                <!-- DESCRIPTION -->

                <div class="col-12 mb-3">

                    <label class="form-label">Service Description *</label>

                    <textarea
                        name="description"
                        class="form-control"
                        rows="5"
                        placeholder="Enter service description..."
                        required
                    ></textarea>

                </div>

                <!-- SERVICE IMAGE -->

                <div class="col-md-6 mb-4">

                    <label class="form-label">Service Image *</label>

                    <input
                        type="file"
                        name="image"
                        class="form-control"
                        accept="image/jpeg,image/png,image/webp"
                        onchange="previewServiceImage(event)"
                        required
                    >

                    <small class="text-muted d-block mt-2">
                        JPG, PNG, WEBP | Maximum size 5MB
                    </small>

                    <img
                        id="servicePreview"
                        class="preview-img mt-3"
                        alt="Service Preview"
                    >

                </div>

                <!-- STATUS -->

                <div class="col-md-6 mb-3">

                    <label class="form-label">Status</label>

                    <select name="status" class="form-select">

                        <option value="1" selected>Active</option>
                        <option value="0">Inactive</option>

                    </select>

                </div>

            </div>

            <!-- BUTTONS -->

            <div class="d-flex justify-content-end gap-3 mt-3 flex-wrap">

                <button type="reset" class="btn btn-light border rounded-pill px-4"
                    onclick="document.getElementById('servicePreview').style.display='none';">
                    Reset
                </button>

                <button type="submit" name="add_service" class="btn-admin">
                    Save Service
                </button>

            </div>

        </form>

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
            preview.style.display = "block";
        };

        reader.readAsDataURL(input.files[0]);

    } else {

        preview.style.display = "none";

    }
}

</script>

</body>
</html>
