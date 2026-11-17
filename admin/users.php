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

/* ================= DELETE USER ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $id = (int) $_POST['user_id'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: users.php?deleted=1");
    exit;
}

/* ================= BLOCK / UNBLOCK ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_status'])) {
    $id = (int) $_POST['user_id'];
    $stmt = $conn->prepare("UPDATE users SET status = IF(status = 'active', 'blocked', 'active') WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: users.php?updated=1");
    exit;
}

/* ================= SEARCH + FILTER ================= */
$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? 'all';

$where  = [];
$params = [];
$types  = '';

if ($search !== '') {
    $where[]  = "(name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $term     = "%{$search}%";
    $params   = array_merge($params, [$term, $term, $term]);
    $types   .= 'sss';
}

if (in_array($status, ['active', 'blocked'], true)) {
    $where[] = "status = ?";
    $params[] = $status;
    $types   .= 's';
}

$whereSQL = $where ? "WHERE " . implode(" AND ", $where) : "";

/* ================= PAGINATION ================= */
$perPage = 10;
$page    = max(1, (int)($_GET['page'] ?? 1));

$countStmt = $conn->prepare("SELECT COUNT(*) AS total FROM users $whereSQL");
if ($types !== '') {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$totalUsers = (int)$countStmt->get_result()->fetch_assoc()['total'];
$countStmt->close();

$totalPages = max(1, (int)ceil($totalUsers / $perPage));
$page       = min($page, $totalPages);
$offset     = ($page - 1) * $perPage;

$sql = "SELECT id, name, email, phone, image, status, created_at FROM users $whereSQL ORDER BY id DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);

$listParams   = $params;
$listTypes    = $types . 'ii';
$listParams[] = $perPage;
$listParams[] = $offset;

$stmt->bind_param($listTypes, ...$listParams);
$stmt->execute();
$users = $stmt->get_result();

/* ================= STATS ================= */
$stats = $conn->query("
    SELECT
        COUNT(*)                AS total,
        SUM(status = 'active')  AS active,
        SUM(status = 'blocked') AS blocked
    FROM users
")->fetch_assoc();

include "sidebar.php";
include "header.php";
?>

<!-- MAIN LAYOUT WRAPPER -->
<div class="um-wrapper">

    <!-- PAGE HEADER -->
    <div class="um-header">
        <div>
            <h2 class="um-title">User Management</h2>
            <p class="um-subtitle">Manage your registered customers</p>
        </div>
        <div class="um-header-total">
            <i class="fa-solid fa-users"></i>
            <span><?= (int)$stats['total'] ?> Total Users</span>
        </div>
    </div>

    <!-- ALERTS -->
    <?php if (isset($_GET['deleted'])): ?>
        <div class="um-alert um-alert-success">
            <i class="fa-solid fa-circle-check"></i> User deleted successfully.
        </div>
    <?php elseif (isset($_GET['updated'])): ?>
        <div class="um-alert um-alert-success">
            <i class="fa-solid fa-circle-check"></i> User status updated successfully.
        </div>
    <?php endif; ?>

    <!-- STAT CARDS -->
    <div class="um-stats">
        <div class="um-stat">
            <div class="um-stat-icon pink"><i class="fa-solid fa-users"></i></div>
            <div class="um-stat-body">
                <span>Total Users</span>
                <strong><?= (int)$stats['total'] ?></strong>
            </div>
        </div>
        <div class="um-stat">
            <div class="um-stat-icon green"><i class="fa-solid fa-user-check"></i></div>
            <div class="um-stat-body">
                <span>Active Users</span>
                <strong><?= (int)$stats['active'] ?></strong>
            </div>
        </div>
        <div class="um-stat">
            <div class="um-stat-icon red"><i class="fa-solid fa-user-lock"></i></div>
            <div class="um-stat-body">
                <span>Blocked Users</span>
                <strong><?= (int)$stats['blocked'] ?></strong>
            </div>
        </div>
    </div>

    <!-- USERS CARD -->
    <div class="um-card">

        <form method="GET" class="um-filters">
            <div class="um-search-wrap">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="search" name="search" class="um-input" placeholder="Search name, email or phone..." value="<?= e($search) ?>">
            </div>
            <select name="status" class="um-select">
                <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>All Users</option>
                <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="blocked" <?= $status === 'blocked' ? 'selected' : '' ?>>Blocked</option>
            </select>
            <button type="submit" class="um-btn um-btn-primary"><i class="fa-solid fa-filter"></i> Filter</button>
            <a href="users.php" class="um-btn um-btn-ghost"><i class="fa-solid fa-rotate-left"></i> Reset</a>
        </form>

        <div class="table-responsive">
            <table class="um-table">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Registered</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($users->num_rows > 0): ?>
                    <?php while ($user = $users->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div class="um-customer">
                                    <?php if (!empty($user['image'])): ?>
                                        <img src="../images/<?= e(basename($user['image'])) ?>" alt="<?= e($user['name']) ?>">
                                    <?php else: ?>
                                        <div class="um-avatar"><?= e(strtoupper(substr($user['name'] ?: 'U', 0, 1))) ?></div>
                                    <?php endif; ?>
                                    <div class="um-customer-info">
                                        <strong><?= e($user['name']) ?></strong>
                                        <small><?= e($user['email']) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><?= e($user['phone'] ?: '—') ?></td>
                            <td><?= $user['created_at'] ? date('d M Y', strtotime($user['created_at'])) : '—' ?></td>
                            <td>
                                <span class="um-badge <?= $user['status'] === 'active' ? 'um-badge-active' : 'um-badge-blocked' ?>">
                                    <span class="um-dot"></span>
                                    <?= e(ucfirst($user['status'])) ?>
                                </span>
                            </td>
                            <td>
                                <div class="um-actions">
                                    <a href="view-user.php?id=<?= (int)$user['id'] ?>" class="um-action-btn view" title="View User">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <form method="POST" class="um-action-form">
                                        <input type="hidden" name="user_id" value="<?= (int)$user['id'] ?>">
                                        <button type="submit" name="toggle_status" class="um-action-btn <?= $user['status'] === 'active' ? 'block' : 'unblock' ?>" title="<?= $user['status'] === 'active' ? 'Block User' : 'Unblock User' ?>">
                                            <i class="fa-solid <?= $user['status'] === 'active' ? 'fa-ban' : 'fa-unlock' ?>"></i>
                                        </button>
                                    </form>
                                    <form method="POST" class="um-action-form" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                        <input type="hidden" name="user_id" value="<?= (int)$user['id'] ?>">
                                        <button type="submit" name="delete_user" class="um-action-btn delete" title="Delete User">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">
                            <div class="um-empty">
                                <i class="fa-solid fa-users"></i>
                                <strong>No users found</strong>
                                <span>Try changing your search or filter.</span>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- PAGINATION -->
        <?php if ($totalPages > 1): ?>
            <div class="um-pagination">
                <?php if ($page > 1): ?>
                    <a href="?<?= http_build_query(['search' => $search, 'status' => $status, 'page' => $page - 1]) ?>" class="um-page-arrow"><i class="fa-solid fa-chevron-left"></i></a>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a class="um-page <?= $page === $i ? 'current' : '' ?>" href="?<?= http_build_query(['search' => $search, 'status' => $status, 'page' => $i]) ?>"><?= $i ?></a>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="?<?= http_build_query(['search' => $search, 'status' => $status, 'page' => $page + 1]) ?>" class="um-page-arrow"><i class="fa-solid fa-chevron-right"></i></a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<style>
    /* ==========================================================
       LAYOUT FIX: .um-wrapper replaces .main-content
       ========================================================== */
    body {
        overflow-x: hidden; /* Prevent unwanted horizontal scroll */
    }
    
    .um-wrapper {
        margin-left: 290px; /* Exactly matches sidebar width */
        margin-top: 70px;   /* Exactly matches header height */
        padding: 30px 40px;
        width: calc(100% - 290px);
        min-height: calc(100vh - 75px);
        box-sizing: border-box;
    }

    .um-wrapper * {
        box-sizing: border-box;
    }

    /* Header */
    .um-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 15px;
        margin-bottom: 22px;
        flex-wrap: wrap;
    }
    .um-title {
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        color: #2e3254;
        margin: 0;
        font-size: 1.5rem;
        letter-spacing: -0.01em;
    }
    .um-subtitle {
        color: #8a8fa3;
        margin: 6px 0 0;
        font-size: 0.88rem;
    }
    .um-header-total {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #fff;
        border: 1px solid #efe6ec;
        border-radius: 999px;
        padding: 9px 16px;
        font-family: 'Outfit', sans-serif;
        font-size: 0.82rem;
        font-weight: 800;
        color: #ff4d6d;
        box-shadow: 0 4px 15px rgba(46, 50, 84, 0.03);
        white-space: nowrap;
    }

    /* Alerts */
    .um-alert {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        border-radius: 12px;
        font-size: 0.88rem;
        font-weight: 600;
        margin-bottom: 20px;
    }
    .um-alert-success {
        background: #e8f8ef;
        color: #159653;
        border: 1px solid #c8ecd6;
    }

    /* Stats */
    .um-stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
        margin-bottom: 22px;
        width: 100%;
    }
    .um-stat {
        background: #fff;
        border: 1px solid #efe6ec;
        border-radius: 20px;
        padding: 20px 22px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: 0 4px 15px rgba(46, 50, 84, 0.03);
        transition: transform .3s, box-shadow .3s;
        min-width: 0;
        overflow: hidden;
    }
    .um-stat:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(46, 50, 84, 0.06);
    }
    .um-stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: grid;
        place-items: center;
        font-size: 1.15rem;
        flex: 0 0 48px;
    }
    .um-stat-icon.pink  { background: #fff2f5; color: #ff4d6d; }
    .um-stat-icon.green { background: #e8f8ef; color: #159653; }
    .um-stat-icon.red   { background: #fff0f0; color: #d83b4b; }
    
    .um-stat-body { min-width: 0; }
    .um-stat-body span {
        display: block;
        font-family: 'Outfit', sans-serif;
        font-size: 0.72rem;
        font-weight: 700;
        color: #8a8fa3;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .um-stat-body strong {
        display: block;
        font-family: 'Outfit', sans-serif;
        font-size: 1.55rem;
        font-weight: 800;
        color: #2e3254;
        margin-top: 4px;
        line-height: 1;
    }

    /* Card */
    .um-card {
        background: #fff;
        border-radius: 20px;
        border: 1px solid #efe6ec;
        box-shadow: 0 4px 15px rgba(46, 50, 84, 0.03);
        padding: 26px;
        width: 100%;
    }

    /* Filters */
    .um-filters {
        display: flex;
        gap: 12px;
        margin-bottom: 22px;
        flex-wrap: wrap;
        align-items: center;
        width: 100%;
    }
    .um-search-wrap {
        position: relative;
        flex: 1 1 260px;
        min-width: 0;
    }
    .um-search-wrap i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #8a8fa3;
        font-size: 0.9rem;
        pointer-events: none;
    }
    .um-input, .um-select {
        height: 44px;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        background: #fff;
        font-size: 0.88rem;
        color: #2e3254;
        transition: border-color .25s, box-shadow .25s;
        box-sizing: border-box;
    }
    .um-input {
        width: 100%;
        padding: 0 14px 0 40px;
    }
    .um-select {
        padding: 0 14px;
        flex: 0 0 160px;
    }
    .um-input:focus, .um-select:focus {
        outline: none;
        border-color: #ff4d6d;
        box-shadow: 0 0 0 4px #fff2f5;
    }
    .um-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        height: 44px;
        padding: 0 20px;
        border-radius: 999px;
        font-family: 'Outfit', sans-serif;
        font-size: 0.85rem;
        font-weight: 800;
        cursor: pointer;
        border: none;
        text-decoration: none;
        transition: all .3s;
        white-space: nowrap;
        flex: 0 0 auto;
    }
    .um-btn-primary {
        background: linear-gradient(135deg, #ff4d6d, #e63950);
        color: #fff;
        box-shadow: 0 8px 20px rgba(255, 77, 109, 0.25);
    }
    .um-btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 25px rgba(255, 77, 109, 0.35);
    }
    .um-btn-ghost {
        background: #f3f4f8;
        color: #56607a;
    }
    .um-btn-ghost:hover {
        background: #e9ebf2;
        color: #2e3254;
    }

    /* Table */
    .table-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .um-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        min-width: 720px;
    }
    .um-table thead th {
        background: #fafbfc;
        font-family: 'Outfit', sans-serif;
        font-size: 0.72rem;
        font-weight: 800;
        text-transform: uppercase;
        color: #8a8fa3;
        padding: 14px 16px;
        text-align: left;
        border-bottom: 1px solid #efe6ec;
        white-space: nowrap;
    }
    .um-table thead th:first-child { border-top-left-radius: 12px; }
    .um-table thead th:last-child  { border-top-right-radius: 12px; }
    .um-table tbody td {
        padding: 16px;
        font-size: 0.88rem;
        color: #56607a;
        border-bottom: 1px solid #f3f1f5;
        vertical-align: middle;
    }
    .um-table tbody tr:hover { background: #fafbfc; }
    .um-table tbody tr:last-child td { border-bottom: none; }

    .um-customer {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 200px;
    }
    .um-customer img, .um-avatar {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        object-fit: cover;
        flex: 0 0 42px;
    }
    .um-avatar {
        display: grid;
        place-items: center;
        background: linear-gradient(135deg, #ff4d6d, #e63950);
        color: #fff;
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        font-size: 1rem;
    }
    .um-customer-info { min-width: 0; }
    .um-customer-info strong {
        display: block;
        font-family: 'Outfit', sans-serif;
        font-size: 0.9rem;
        font-weight: 700;
        color: #2e3254;
        line-height: 1.2;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        max-width: 200px;
    }
    .um-customer-info small {
        display: block;
        color: #8a8fa3;
        font-size: 0.78rem;
        margin-top: 3px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        max-width: 200px;
    }

    /* Badges & Actions */
    .um-badge {
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
    .um-dot { width: 6px; height: 6px; border-radius: 50%; display: inline-block; }
    .um-badge-active  { background: #e8f8ef; color: #159653; }
    .um-badge-active .um-dot  { background: #159653; }
    .um-badge-blocked { background: #fff0f0; color: #d83b4b; }
    .um-badge-blocked .um-dot { background: #d83b4b; }

    .um-actions { display: flex; gap: 7px; justify-content: flex-end; }
    .um-action-form { margin: 0; }
    .um-action-btn {
        width: 34px; height: 34px;
        border: none; border-radius: 9px;
        display: grid; place-items: center;
        text-decoration: none; cursor: pointer;
        background: #f3f4f8; font-size: 0.8rem; transition: all .25s;
    }
    .um-action-btn:hover { transform: translateY(-2px); }
    .um-action-btn.view    { color: #0284c7; background: #e0f2fe; }
    .um-action-btn.view:hover    { background: #0284c7; color: #fff; }
    .um-action-btn.block   { color: #d83b4b; background: #fff0f0; }
    .um-action-btn.block:hover   { background: #d83b4b; color: #fff; }
    .um-action-btn.unblock { color: #159653; background: #e8f8ef; }
    .um-action-btn.unblock:hover { background: #159653; color: #fff; }
    .um-action-btn.delete  { color: #dc5665; background: #fee2e2; }
    .um-action-btn.delete:hover  { background: #dc2626; color: #fff; }

    /* Empty State */
    .um-empty { text-align: center; padding: 60px 20px; }
    .um-empty i { font-size: 2.2rem; color: #d7dae5; display: block; margin-bottom: 12px; }
    .um-empty strong { font-family: 'Outfit', sans-serif; font-size: 1.1rem; font-weight: 800; color: #2e3254; display: block; margin-bottom: 6px; }
    .um-empty span { color: #8a8fa3; font-size: 0.88rem; }

    /* Pagination */
    .um-pagination { display: flex; justify-content: flex-end; gap: 7px; margin-top: 24px; flex-wrap: wrap; }
    .um-page, .um-page-arrow {
        width: 36px; height: 36px; display: grid; place-items: center;
        border-radius: 10px; font-family: 'Outfit', sans-serif;
        font-weight: 800; font-size: 0.85rem; text-decoration: none;
        transition: all .25s;
    }
    .um-page { background: #f3f4f8; color: #56607a; }
    .um-page:hover { background: #fff2f5; color: #ff4d6d; }
    .um-page.current { background: linear-gradient(135deg, #ff4d6d, #e63950); color: #fff; }
    .um-page-arrow { background: #f3f4f8; color: #56607a; }
    .um-page-arrow:hover { background: #ff4d6d; color: #fff; }

    /* RESPONSIVE */
    @media (max-width: 1199px) {
        .um-wrapper {
            margin-left: 260px;
            width: calc(100% - 260px);
            padding: 26px 30px;
        }
    }
    @media (max-width: 991px) {
        .um-wrapper {
            margin-left: 0;
            margin-top: 68px;
            width: 100%;
            padding: 22px 20px;
        }
        .um-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 767px) {
        .um-wrapper { padding: 18px 14px; }
        .um-header { flex-direction: column; align-items: flex-start; }
        .um-stats { grid-template-columns: 1fr; }
        .um-card { padding: 18px; }
        .um-filters { gap: 10px; }
        .um-search-wrap { flex: 1 1 100%; }
        .um-select { flex: 1 1 100%; }
        .um-btn { flex: 1 1 auto; justify-content: center; }
        .um-customer-info strong, .um-customer-info small { max-width: 140px; }
        .um-pagination { justify-content: center; }
    }
</style>

<?php $stmt->close(); ?>