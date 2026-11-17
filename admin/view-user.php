<?php
session_start();
require_once "../config/db.php";

if (empty($_SESSION['admin_name'])) {
    header("Location: login.php");
    exit;
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $conn->prepare("
    SELECT id, name, email, phone, image, status, created_at
    FROM users
    WHERE id = ?
    LIMIT 1
");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    header("Location: users.php");
    exit;
}

include "sidebar.php";
include "header.php";
?>

<!-- MAIN LAYOUT WRAPPER -->
<div class="uv-wrapper">

    <!-- ============ PAGE HEADER ============ -->
    <div class="uv-header">
        <div>
            <h2 class="uv-title">User Details</h2>
            <p class="uv-subtitle">Complete customer profile and account information</p>
        </div>
        <a href="users.php" class="uv-back-btn">
            <i class="fa-solid fa-arrow-left"></i> Back to Users
        </a>
    </div>

    <!-- ============ PROFILE CARD ============ -->
    <div class="uv-profile-card">
        <div class="uv-profile-top">
            <?php if (!empty($user['image'])): ?>
                <img src="../images/<?= e(basename($user['image'])) ?>"
                     alt="<?= e($user['name']) ?>"
                     class="uv-avatar-img">
            <?php else: ?>
                <div class="uv-avatar">
                    <?= e(strtoupper(substr($user['name'] ?: 'U', 0, 1))) ?>
                </div>
            <?php endif; ?>

            <div class="uv-identity">
                <h3><?= e($user['name']) ?></h3>
                <p><i class="fa-solid fa-envelope"></i> <?= e($user['email']) ?></p>
                <span class="uv-badge <?= $user['status'] === 'active' ? 'uv-badge-active' : 'uv-badge-blocked' ?>">
                    <span class="uv-dot"></span>
                    <?= e(ucfirst($user['status'])) ?>
                </span>
            </div>
        </div>

        <div class="uv-divider"></div>

        <div class="uv-details-grid">
            <div class="uv-detail-item">
                <span class="uv-detail-label">
                    <i class="fa-solid fa-hashtag"></i> Customer ID
                </span>
                <span class="uv-detail-value">#<?= (int)$user['id'] ?></span>
            </div>

            <div class="uv-detail-item">
                <span class="uv-detail-label">
                    <i class="fa-solid fa-user"></i> Full Name
                </span>
                <span class="uv-detail-value"><?= e($user['name']) ?></span>
            </div>

            <div class="uv-detail-item">
                <span class="uv-detail-label">
                    <i class="fa-solid fa-envelope"></i> Email Address
                </span>
                <span class="uv-detail-value"><?= e($user['email']) ?></span>
            </div>

            <div class="uv-detail-item">
                <span class="uv-detail-label">
                    <i class="fa-solid fa-phone"></i> Phone Number
                </span>
                <span class="uv-detail-value"><?= e($user['phone'] ?: '—') ?></span>
            </div>

            <div class="uv-detail-item">
                <span class="uv-detail-label">
                    <i class="fa-solid fa-calendar-days"></i> Registration Date
                </span>
                <span class="uv-detail-value">
                    <?= $user['created_at']
                        ? date('d M Y', strtotime($user['created_at']))
                        : '—' ?>
                </span>
            </div>

            <div class="uv-detail-item">
                <span class="uv-detail-label">
                    <i class="fa-solid fa-circle-check"></i> Account Status
                </span>
                <span class="uv-detail-value">
                    <span class="uv-badge <?= $user['status'] === 'active' ? 'uv-badge-active' : 'uv-badge-blocked' ?>">
                        <span class="uv-dot"></span>
                        <?= e(ucfirst($user['status'])) ?>
                    </span>
                </span>
            </div>
        </div>
    </div>

</div>

<style>
    /* ==========================================================
       LAYOUT FIX: .uv-wrapper replaces .main-content
       ========================================================== */
    body {
        overflow-x: hidden; /* Prevent unwanted horizontal scroll */
    }
    
    .uv-wrapper {
        margin-left: 290px; /* Exactly matches sidebar width */
        margin-top: 70px;   /* Exactly matches header height */
        padding: 30px 40px;
        width: calc(100% - 290px);
        min-height: calc(100vh - 75px);
        box-sizing: border-box;
    }

    .uv-wrapper * {
        box-sizing: border-box;
    }

    /* Header */
    .uv-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 15px;
        margin-bottom: 22px;
        flex-wrap: wrap;
    }
    .uv-title {
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        color: #2e3254;
        margin: 0;
        font-size: 1.5rem;
        letter-spacing: -0.01em;
    }
    .uv-subtitle {
        color: #8a8fa3;
        margin: 6px 0 0;
        font-size: 0.88rem;
    }
    .uv-back-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        height: 42px;
        padding: 0 20px;
        border-radius: 999px;
        background: #f3f4f8;
        color: #56607a;
        text-decoration: none;
        font-family: 'Outfit', sans-serif;
        font-size: 0.85rem;
        font-weight: 800;
        transition: all .3s;
    }
    .uv-back-btn:hover {
        background: #e9ebf2;
        color: #2e3254;
        transform: translateX(-2px);
    }

    /* Profile Card */
    .uv-profile-card {
        background: #fff;
        border-radius: 20px;
        border: 1px solid #efe6ec;
        box-shadow: 0 4px 15px rgba(46, 50, 84, 0.03);
        padding: 32px;
        width: 100%;
    }

    .uv-profile-top {
        display: flex;
        align-items: center;
        gap: 22px;
        flex-wrap: wrap;
    }
    .uv-avatar,
    .uv-avatar-img {
        width: 92px;
        height: 92px;
        border-radius: 50%;
        flex: 0 0 92px;
        object-fit: cover;
    }
    .uv-avatar {
        display: grid;
        place-items: center;
        background: linear-gradient(135deg, #ff4d6d, #e63950);
        color: #fff;
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        font-size: 2rem;
        box-shadow: 0 10px 24px rgba(255, 77, 109, 0.3);
    }
    .uv-avatar-img {
        border: 3px solid #fff2f5;
        box-shadow: 0 10px 24px rgba(46, 50, 84, 0.08);
    }

    .uv-identity h3 {
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        color: #2e3254;
        margin: 0 0 6px;
        font-size: 1.4rem;
        line-height: 1.2;
    }
    .uv-identity p {
        margin: 0 0 10px;
        color: #8a8fa3;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .uv-identity p i { color: #ff4d6d; font-size: 0.85rem; }

    /* Badges */
    .uv-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 12px;
        border-radius: 999px;
        font-family: 'Outfit', sans-serif;
        font-size: 0.72rem;
        font-weight: 800;
        white-space: nowrap;
    }
    .uv-dot { width: 6px; height: 6px; border-radius: 50%; display: inline-block; }
    .uv-badge-active  { background: #e8f8ef; color: #159653; }
    .uv-badge-active .uv-dot  { background: #159653; }
    .uv-badge-blocked { background: #fff0f0; color: #d83b4b; }
    .uv-badge-blocked .uv-dot { background: #d83b4b; }

    .uv-divider {
        height: 1px;
        background: #efe6ec;
        margin: 26px 0;
    }

    /* Details Grid */
    .uv-details-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
    }
    .uv-detail-item {
        background: #fafbfc;
        border: 1px solid #efe6ec;
        border-radius: 14px;
        padding: 18px;
        min-width: 0;
    }
    .uv-detail-label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-family: 'Outfit', sans-serif;
        font-size: 0.72rem;
        font-weight: 800;
        color: #8a8fa3;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 8px;
    }
    .uv-detail-label i {
        color: #ff4d6d;
        font-size: 0.85rem;
    }
    .uv-detail-value {
        display: block;
        font-family: 'Outfit', sans-serif;
        font-size: 1rem;
        font-weight: 700;
        color: #2e3254;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* RESPONSIVE */
    @media (max-width: 1199px) {
        .uv-wrapper {
            margin-left: 260px;
            width: calc(100% - 260px);
            padding: 26px 30px;
        }
        .uv-details-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 991px) {
        .uv-wrapper {
            margin-left: 0;
            margin-top: 68px;
            width: 100%;
            padding: 22px 20px;
        }
        .uv-details-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 767px) {
        .uv-wrapper { padding: 18px 14px; }
        .uv-header { flex-direction: column; align-items: flex-start; }
        .uv-profile-card { padding: 22px; }
        .uv-details-grid { grid-template-columns: 1fr; }
        .uv-back-btn { width: 100%; justify-content: center; }
    }
</style>