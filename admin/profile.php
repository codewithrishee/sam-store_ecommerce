<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . "/../config/db.php";

if (empty($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

$adminEmail = $_SESSION['admin'];
$stmt = $conn->prepare("SELECT id, name, email, password, phone, profile_image, created_at FROM admins WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $adminEmail);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$admin) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token'], $_SESSION['profile_csrf']) || !hash_equals($_SESSION['profile_csrf'], $_POST['csrf_token'])) {
        $error = 'Session expired. Please refresh the page and try again.';
    } elseif (isset($_POST['update_profile'])) {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $newImage = $admin['profile_image'] ?? '';

        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid name and email address.';
        } elseif (strlen($name) > 100 || strlen($email) > 150 || strlen($phone) > 20) {
            $error = 'One or more fields exceed the allowed length.';
        } else {
            $check = $conn->prepare("SELECT id FROM admins WHERE email = ? AND id <> ? LIMIT 1");
            $check->bind_param("si", $email, $admin['id']);
            $check->execute();
            $duplicate = $check->get_result()->num_rows > 0;
            $check->close();

            if ($duplicate) {
                $error = 'This email address is already used by another admin.';
            } else {
                if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] !== UPLOAD_ERR_NO_FILE) {
                    if ($_FILES['profile_image']['error'] !== UPLOAD_ERR_OK) {
                        $error = 'Image upload failed. Please try again.';
                    } elseif ($_FILES['profile_image']['size'] > 3 * 1024 * 1024) {
                        $error = 'Profile image must be smaller than 3 MB.';
                    } else {
                        $tmp = $_FILES['profile_image']['tmp_name'];
                        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);
                        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
                        if (!isset($allowed[$mime])) {
                            $error = 'Only JPG, PNG and WEBP images are allowed.';
                        } else {
                            $imageDir = __DIR__ . '/../images/';
                            if (!is_dir($imageDir)) mkdir($imageDir, 0755, true);
                            $fileName = 'admin_' . $admin['id'] . '_' . bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
                            if (!move_uploaded_file($tmp, $imageDir . $fileName)) {
                                $error = 'Could not save the uploaded image.';
                            } else {
                                $newImage = $fileName;
                            }
                        }
                    }
                }

                if ($error === '') {
                    $update = $conn->prepare("UPDATE admins SET name = ?, email = ?, phone = ?, profile_image = ? WHERE id = ?");
                    $update->bind_param("ssssi", $name, $email, $phone, $newImage, $admin['id']);
                    if ($update->execute()) {
                        $oldImage = $admin['profile_image'] ?? '';
                        if ($newImage !== $oldImage && $oldImage !== '') {
                            $oldPath = __DIR__ . '/../images/' . basename($oldImage);
                            if (is_file($oldPath)) @unlink($oldPath);
                        }
                        $_SESSION['admin'] = $email;
                        $_SESSION['admin_name'] = $name;
                        $success = 'Profile details updated successfully.';
                    } else {
                        $error = 'Unable to update profile. Please try again.';
                    }
                    $update->close();
                }
            }
        }
    } elseif (isset($_POST['change_password'])) {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        if (!password_verify($currentPassword, $admin['password'])) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($newPassword) < 8) {
            $error = 'New password must be at least 8 characters.';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'New password and confirm password do not match.';
        } elseif (password_verify($newPassword, $admin['password'])) {
            $error = 'New password must be different from your current password.';
        } else {
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE admins SET password = ? WHERE id = ?");
            $update->bind_param("si", $hash, $admin['id']);
            if ($update->execute()) $success = 'Password changed successfully.';
            else $error = 'Unable to change password. Please try again.';
            $update->close();
        }
    }

    // Reload the current admin row after a successful profile/password update.
    $stmt = $conn->prepare("SELECT id, name, email, password, phone, profile_image, created_at FROM admins WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $admin['id']);
    $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if (empty($_SESSION['profile_csrf'])) $_SESSION['profile_csrf'] = bin2hex(random_bytes(32));
$csrfToken = $_SESSION['profile_csrf'];
$profileName = $admin['name'] ?: 'Admin';
$profileImage = !empty($admin['profile_image']) ? '../images/' . rawurlencode(basename($admin['profile_image'])) : '';
$joinedDate = !empty($admin['created_at']) ? date('d M Y', strtotime($admin['created_at'])) : '—';

function profileCount(mysqli $conn, string $table): int {
    $allowed = ['products', 'orders', 'users'];
    if (!in_array($table, $allowed, true)) return 0;
    $result = $conn->query("SELECT COUNT(*) AS total FROM `$table`");
    return $result ? (int)$result->fetch_assoc()['total'] : 0;
}
$productCount = profileCount($conn, 'products');
$orderCount = profileCount($conn, 'orders');
$customerCount = profileCount($conn, 'users');

include "header.php";
include "sidebar.php";
?>

<link rel="stylesheet" href="admin-style.css">
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
<style>
    /* ==========================================================
       ADMIN PROFILE — PREMIUM STYLES (Navy + Pink)
       ========================================================== */
    body { background: #f4f7f6; font-family: 'Manrope', sans-serif; }
    
    /* Layout Fix: Navbar + Sidebar overlap se bachne ke liye */
    .main-content { margin-left:290px; margin-top:15px; padding:30px 40px; min-height:calc(100vh - 75px); }

    /* ================= COVER BANNER ================= */
    .profile-hero {
        position: relative;
        border-radius: 24px;
        overflow: hidden;
        background: linear-gradient(135deg, #1f2340 0%, #2e3254 100%);
        padding: 40px 45px 100px;
        box-shadow: 0 10px 30px rgba(46, 50, 84, 0.15);
        margin-bottom: 100px;
    }

    .profile-hero::before {
        content: '';
        position: absolute;
        top: -60px;
        right: -60px;
        width: 300px;
        height: 300px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(255, 77, 109, 0.35) 0%, transparent 70%);
        pointer-events: none;
    }

    .profile-hero::after {
        content: '';
        position: absolute;
        bottom: -80px;
        left: 30%;
        width: 400px;
        height: 400px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(14, 165, 164, 0.12) 0%, transparent 70%);
        pointer-events: none;
    }

    .hero-content {
        position: relative;
        z-index: 2;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
    }

    .hero-title {
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        color: #fff;
        font-size: 1.8rem;
        letter-spacing: -0.02em;
        margin: 0;
    }

    .hero-subtitle {
        color: rgba(255, 255, 255, 0.6);
        font-size: 0.85rem;
        margin: 5px 0 0;
        font-weight: 500;
    }

    .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.15);
        padding: 8px 18px;
        border-radius: 999px;
        color: #fff;
        font-family: 'Outfit', sans-serif;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        backdrop-filter: blur(10px);
    }

    .hero-badge i { color: #ff4d6d; }

    /* ================= PROFILE CARD ================= */
    .profile-card-wrapper {
        position: relative;
        margin-top: -80px;
        z-index: 5;
        padding: 0 10px;
    }

    .admin-profile-card {
        background: #fff;
        border-radius: 24px;
        padding: 0 40px 40px;
        border: 1px solid #efe6ec;
        box-shadow: 0 20px 50px rgba(46, 50, 84, 0.08);
        position: relative;
        overflow: hidden;
    }

    .admin-profile-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, #ff4d6d, #e63950, #ff8fa0);
    }

    /* ================= PROFILE IMAGE ================= */
    .admin-profile-img {
        position: relative;
        display: inline-block;
        margin-top: -55px;
    }

    .admin-profile-img img {
        margin-top: 80px;
        width: 140px;
        height: 140px;
        border-radius: 50%;
        object-fit: cover;
        border: 5px solid #fff;
        box-shadow: 0 10px 30px rgba(46, 50, 84, 0.15);
        background: #f4f7f6;
    }

    .admin-profile-img .online-dot {
        position: absolute;
        bottom: 10px;
        right: 10px;
        width: 22px;
        height: 22px;
        background: #22c55e;
        border: 3px solid #fff;
        border-radius: 50%;
        box-shadow: 0 2px 8px rgba(34, 197, 94, 0.4);
        animation: pulseOnline 2s infinite;
    }

    @keyframes pulseOnline {
        0%, 100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.5), 0 2px 8px rgba(34, 197, 94, 0.4); }
        50% { box-shadow: 0 0 0 8px rgba(34, 197, 94, 0), 0 2px 8px rgba(34, 197, 94, 0.4); }
    }

    /* ================= ADMIN INFO ================= */
    .admin-name {
        font-family: 'Outfit', sans-serif;
        font-size: 1.8rem;
        font-weight: 800;
        color: #2e3254;
        margin: 15px 0 4px;
        letter-spacing: -0.02em;
    }

    .admin-role {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #ff4d6d;
        background: #fff2f5;
        padding: 5px 14px;
        border-radius: 999px;
        font-family: 'Outfit', sans-serif;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        margin-bottom: 30px;
    }

    .admin-role i { font-size: 0.7rem; }

    /* ================= DETAIL CARDS ================= */
    .admin-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 30px;
    }

    .detail-box {
        background: #fafbfc;
        border: 1px solid #efe6ec;
        padding: 18px 22px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        gap: 14px;
        transition: all 0.3s ease;
    }

    .detail-box:hover {
        background: #fff2f5;
        border-color: rgba(255, 77, 109, 0.2);
        transform: translateY(-2px);
    }

    .detail-box .detail-icon {
        width: 42px;
        height: 42px;
        display: grid;
        place-items: center;
        border-radius: 12px;
        background: #fff;
        color: #ff4d6d;
        font-size: 1rem;
        flex: 0 0 42px;
        box-shadow: 0 4px 10px rgba(255, 77, 109, 0.12);
    }

    .detail-box .detail-info {
        min-width: 0;
        flex: 1;
    }

    .detail-box span {
        display: block;
        color: #8a8fa3;
        font-family: 'Outfit', sans-serif;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        margin-bottom: 4px;
    }

    .detail-box h6 {
        margin: 0;
        font-family: 'Outfit', sans-serif;
        font-size: 0.95rem;
        font-weight: 700;
        color: #2e3254;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* ================= ACTION BUTTONS ================= */
    .action-buttons {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .btn-admin {
        background: linear-gradient(135deg, #ff4d6d, #e63950);
        color: #fff;
        border: none;
        padding: 12px 28px;
        border-radius: 999px;
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        font-size: 0.85rem;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        box-shadow: 0 8px 20px rgba(255, 77, 109, 0.25);
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-admin:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 25px rgba(255, 77, 109, 0.35);
        color: #fff;
    }

    .btn-logout {
        background: #fff;
        color: #2e3254;
        border: 1.5px solid #efe6ec;
        padding: 12px 28px;
        border-radius: 999px;
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        font-size: 0.85rem;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }

    .btn-logout:hover {
        background: #fee2e2;
        border-color: #dc2626;
        color: #dc2626;
        transform: translateY(-2px);
    }

    /* ================= QUICK STATS ================= */
    .quick-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 15px;
        margin-top: 20px;
    }

    .stat-mini {
        background: #fff;
        border: 1px solid #efe6ec;
        border-radius: 18px;
        padding: 20px 22px;
        display: flex;
        align-items: center;
        gap: 14px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(46, 50, 84, 0.03);
    }

    .stat-mini:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 25px rgba(46, 50, 84, 0.08);
        border-color: rgba(255, 77, 109, 0.2);
    }

    .stat-mini .stat-icon {
        width: 44px;
        height: 44px;
        display: grid;
        place-items: center;
        border-radius: 12px;
        font-size: 1.1rem;
        flex: 0 0 44px;
    }

    .stat-mini .stat-info p {
        color: #8a8fa3;
        font-family: 'Outfit', sans-serif;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        margin: 0 0 4px;
    }

    .stat-mini .stat-info h4 {
        color: #2e3254;
        font-family: 'Outfit', sans-serif;
        font-size: 1.3rem;
        font-weight: 900;
        margin: 0;
        line-height: 1;
    }

    .icon-pink { background: #fff2f5; color: #ff4d6d; }
    .icon-blue { background: #e0f2fe; color: #0284c7; }
    .icon-green { background: #dcfce7; color: #16a34a; }
    .icon-orange { background: #ffedd5; color: #ea580c; }

    /* ================= RESPONSIVE ================= */
    @media (max-width: 991px) {
        .main-content { margin-left:0 !important; padding:90px 20px 25px !important; margin-top:0; }
        .profile-hero { padding: 30px 25px 90px; margin-bottom: 90px; }
        .hero-title { font-size: 1.4rem; }
        .admin-profile-card { padding: 0 25px 30px; }
        .admin-name { font-size: 1.5rem; }
    }

    @media (max-width: 575.98px) {
        .admin-profile-img img { width: 110px; height: 110px; }
        .admin-name { font-size: 1.3rem; }
        .admin-details { grid-template-columns: 1fr; }
        .action-buttons { width: 100%; }
        .action-buttons .btn-admin,
        .action-buttons .btn-logout {
            flex: 1;
            justify-content: center;
        }
        .hero-content { flex-direction: column; align-items: flex-start; }
    }


    .profile-form-card{background:#fff;border:1px solid #efe6ec;border-radius:20px;padding:26px;margin-top:22px;box-shadow:0 8px 25px rgba(46,50,84,.04)}
    .form-section-title{font:800 1.15rem 'Outfit',sans-serif;color:#2e3254;margin:0 0 20px}
    .profile-field label{display:block;font-size:.78rem;font-weight:800;color:#56607a;margin-bottom:8px}
    .profile-field .form-control{width:100%;border:1px solid #e6e8ef;border-radius:11px;padding:12px 14px;font:500 .9rem 'Manrope',sans-serif;color:#2e3254;background:#fbfcfd;outline:none}
    .profile-field .form-control:focus{border-color:#ff4d6d;box-shadow:0 0 0 3px rgba(255,77,109,.1)}
    .profile-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}
    .profile-form-grid .full{grid-column:1/-1}
    .form-save-btn{border:0;border-radius:999px;background:linear-gradient(135deg,#ff4d6d,#e63950);color:#fff;padding:12px 23px;font:800 .8rem 'Outfit',sans-serif;letter-spacing:.04em;cursor:pointer}
    .form-note{font-size:.78rem;color:#8a8fa3;margin-top:8px}
    .profile-alert{padding:13px 16px;border-radius:12px;margin:0 0 18px;font-size:.88rem;font-weight:600}
    .profile-alert.success{background:#ecfdf3;color:#15803d;border:1px solid #bbf7d0}
    .profile-alert.error{background:#fff1f2;color:#be123c;border:1px solid #fecdd3}
    .profile-avatar-fallback{width:140px;height:140px;border-radius:50%;border:5px solid #fff;background:#fff2f5;color:#ff4d6d;display:grid;place-items:center;font-size:3.2rem;box-shadow:0 10px 30px rgba(46,50,84,.15)}
    .stat-mini .stat-info h4{font-size:1.3rem}
    @media(max-width:700px){.profile-form-grid{grid-template-columns:1fr}.profile-form-grid .full{grid-column:auto}.profile-form-card{padding:20px}.admin-profile-card{padding:0 20px 25px}}
</style>

<div class="main-content">
    <?php if ($success): ?><div class="profile-alert success"><i class="fa-solid fa-circle-check me-2"></i><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="profile-alert error"><i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="profile-hero">
        <div class="hero-content">
            <div><h1 class="hero-title">Admin Profile</h1><p class="hero-subtitle">Manage your account details and security</p></div>
            <div class="hero-badge"><i class="fa-solid fa-user-shield"></i> Administrator Account</div>
        </div>
    </div>

    <div class="profile-card-wrapper">
        <div class="admin-profile-card">
            <div class="admin-profile-img">
                <?php if ($profileImage): ?>
                    <img src="<?= htmlspecialchars($profileImage) ?>" alt="Admin profile photo">
                <?php else: ?>
                    <div class="profile-avatar-fallback"><i class="fa-solid fa-user"></i></div>
                <?php endif; ?>
            </div>
            <h2 class="admin-name"><?= htmlspecialchars($profileName) ?></h2>
            <span class="admin-role"><i class="fa-solid fa-shield-halved"></i> Super Admin</span>
            <div class="admin-details">
                <div class="detail-box"><div class="detail-icon"><i class="fa-solid fa-envelope"></i></div><div class="detail-info"><span>Email Address</span><h6><?= htmlspecialchars($admin['email']) ?></h6></div></div>
                <div class="detail-box"><div class="detail-icon"><i class="fa-solid fa-phone"></i></div><div class="detail-info"><span>Phone Number</span><h6><?= htmlspecialchars($admin['phone'] ?: 'Not added') ?></h6></div></div>
                <div class="detail-box"><div class="detail-icon"><i class="fa-solid fa-calendar-days"></i></div><div class="detail-info"><span>Joined On</span><h6><?= htmlspecialchars($joinedDate) ?></h6></div></div>
            </div>
            <div class="action-buttons">
                <a href="#edit-profile" class="btn-admin"><i class="fa-solid fa-pen"></i> Edit Profile</a>
                <a href="logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            </div>
        </div>
    </div>

    <div class="quick-stats">
        <div class="stat-mini"><div class="stat-icon icon-pink"><i class="fa-solid fa-box"></i></div><div class="stat-info"><p>Total Products</p><h4><?= number_format($productCount) ?></h4></div></div>
        <div class="stat-mini"><div class="stat-icon icon-blue"><i class="fa-solid fa-cart-shopping"></i></div><div class="stat-info"><p>Total Orders</p><h4><?= number_format($orderCount) ?></h4></div></div>
        <div class="stat-mini"><div class="stat-icon icon-orange"><i class="fa-solid fa-users"></i></div><div class="stat-info"><p>Total Customers</p><h4><?= number_format($customerCount) ?></h4></div></div>
    </div>

    <div class="profile-form-card" id="edit-profile">
        <h3 class="form-section-title"><i class="fa-solid fa-user-pen me-2" style="color:#ff4d6d"></i>Edit Profile Details</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <div class="profile-form-grid">
                <div class="profile-field"><label>Full Name</label><input class="form-control" type="text" name="name" maxlength="100" value="<?= htmlspecialchars($admin['name'] ?? '') ?>" required></div>
                <div class="profile-field"><label>Email Address</label><input class="form-control" type="email" name="email" maxlength="150" value="<?= htmlspecialchars($admin['email'] ?? '') ?>" required></div>
                <div class="profile-field"><label>Phone Number</label><input class="form-control" type="tel" name="phone" maxlength="20" value="<?= htmlspecialchars($admin['phone'] ?? '') ?>" placeholder="Enter phone number"></div>
                <div class="profile-field"><label>Profile Photo</label><input class="form-control" type="file" name="profile_image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"><div class="form-note">JPG, PNG or WEBP · Maximum 3 MB</div></div>
                <div class="full"><button class="form-save-btn" type="submit" name="update_profile"><i class="fa-solid fa-floppy-disk me-2"></i>Save Profile</button></div>
            </div>
        </form>
    </div>

    <div class="profile-form-card">
        <h3 class="form-section-title"><i class="fa-solid fa-lock me-2" style="color:#ff4d6d"></i>Change Password</h3>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <div class="profile-form-grid">
                <div class="profile-field full"><label>Current Password</label><input class="form-control" type="password" name="current_password" autocomplete="current-password" required></div>
                <div class="profile-field"><label>New Password</label><input class="form-control" type="password" name="new_password" minlength="8" autocomplete="new-password" required></div>
                <div class="profile-field"><label>Confirm New Password</label><input class="form-control" type="password" name="confirm_password" minlength="8" autocomplete="new-password" required></div>
                <div class="full"><button class="form-save-btn" type="submit" name="change_password"><i class="fa-solid fa-key me-2"></i>Update Password</button></div>
            </div>
        </form>
    </div>
</div>
