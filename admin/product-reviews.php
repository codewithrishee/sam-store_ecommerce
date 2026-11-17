<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

if (!isset($conn) || !($conn instanceof mysqli)) {
    die('Database connection failed.');
}

if (empty($_SESSION['reviews_csrf'])) {
    $_SESSION['reviews_csrf'] = bin2hex(random_bytes(32));
}

function h($value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/* Handle review actions before any page output. */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['reviews_csrf'], $token)) {
        http_response_code(403);
        exit('Invalid request token. Please refresh the page and try again.');
    }

    $reviewId = filter_input(INPUT_POST, 'review_id', FILTER_VALIDATE_INT);
    $action = $_POST['action'] ?? '';

    if ($reviewId && in_array($action, ['approve', 'reject', 'pending', 'delete'], true)) {
        if ($action === 'delete') {
            $stmt = $conn->prepare('DELETE FROM product_reviews WHERE id = ?');
            $stmt->bind_param('i', $reviewId);
        } else {
            $newStatus = [
                'approve' => 'approved',
                'reject' => 'rejected',
                'pending' => 'pending'
            ][$action];
            $stmt = $conn->prepare('UPDATE product_reviews SET status = ? WHERE id = ?');
            $stmt->bind_param('si', $newStatus, $reviewId);
        }

        if (!$stmt || !$stmt->execute()) {
            $error = 'Unable to update this review. Please try again.';
            $_SESSION['reviews_flash_error'] = $error;
        } else {
            $_SESSION['reviews_flash_success'] = $action === 'delete'
                ? 'Review deleted successfully.'
                : 'Review status updated successfully.';
        }
        if ($stmt) $stmt->close();
    }

    $queryString = $_SERVER['QUERY_STRING'] ?? '';
    header('Location: product-reviews.php' . ($queryString ? '?' . $queryString : ''));
    exit;
}

$successMessage = $_SESSION['reviews_flash_success'] ?? '';
$errorMessage = $_SESSION['reviews_flash_error'] ?? '';
unset($_SESSION['reviews_flash_success'], $_SESSION['reviews_flash_error']);

$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? '';
$rating = isset($_GET['rating']) ? (int)$_GET['rating'] : 0;
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

$where = [];
if ($search !== '') {
    $safeSearch = $conn->real_escape_string($search);
    $where[] = "(pr.customer_name LIKE '%{$safeSearch}%' OR pr.customer_email LIKE '%{$safeSearch}%' OR pr.review_title LIKE '%{$safeSearch}%' OR pr.review_message LIKE '%{$safeSearch}%' OR p.product_name LIKE '%{$safeSearch}%')";
}
if (in_array($status, ['pending', 'approved', 'rejected'], true)) {
    $where[] = "pr.status = '" . $conn->real_escape_string($status) . "'";
}
if ($rating >= 1 && $rating <= 5) {
    $where[] = 'pr.rating = ' . $rating;
}
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$countResult = $conn->query("SELECT COUNT(*) AS total FROM product_reviews pr LEFT JOIN products p ON p.id = pr.product_id {$whereSql}");
$totalReviews = (int)($countResult ? ($countResult->fetch_assoc()['total'] ?? 0) : 0);
$totalPages = max(1, (int)ceil($totalReviews / $perPage));
if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $perPage;
}

$reviews = $conn->query("SELECT pr.*, p.product_name, p.image1 FROM product_reviews pr LEFT JOIN products p ON p.id = pr.product_id {$whereSql} ORDER BY pr.created_at DESC, pr.id DESC LIMIT {$perPage} OFFSET {$offset}");

$statsResult = $conn->query("SELECT COUNT(*) AS total, SUM(status = 'pending') AS pending, SUM(status = 'approved') AS approved, SUM(status = 'rejected') AS rejected FROM product_reviews");
$stats = $statsResult ? $statsResult->fetch_assoc() : ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0];

include 'header.php';
include 'sidebar.php';
?>

<style>
.reviews-page{margin-left:290px;padding:106px 30px 50px;background:#f4f7f6;min-height:100vh;font-family:'Manrope',sans-serif}
.reviews-page .page-heading{display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:24px;flex-wrap:wrap}
.reviews-page .page-title{font-family:'Outfit',sans-serif;color:#2e3254;font-weight:800;margin:0;font-size:1.7rem}
.reviews-page .sub-title{color:#8990a4;font-size:.88rem;margin-top:5px}
.review-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px;margin-bottom:22px}
.review-stat,.review-table-card{background:#fff;border:1px solid #edf0f4;border-radius:18px;box-shadow:0 8px 28px rgba(31,35,64,.05)}
.review-stat{padding:18px 20px}.review-stat span{font-size:.75rem;color:#8990a4;font-weight:700;text-transform:uppercase;letter-spacing:.05em}.review-stat strong{display:block;color:#2e3254;font:800 1.7rem 'Outfit',sans-serif;margin-top:5px}
.review-filters{display:flex;gap:10px;flex-wrap:wrap;padding:18px 20px;border-bottom:1px solid #f0f1f5}
.review-filters input,.review-filters select{border:1px solid #e4e7ee;border-radius:10px;padding:10px 12px;color:#39405c;background:#fff;min-height:42px}
.review-filters input{flex:1;min-width:200px}.review-btn{border:0;border-radius:10px;padding:10px 16px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;gap:7px;cursor:pointer}
.review-btn-primary{background:#ff4d6d;color:#fff}.review-btn-light{background:#f2f3f7;color:#4b526c}
.review-table-card{overflow:hidden}.review-table-wrap{overflow-x:auto}.review-table{width:100%;border-collapse:collapse;min-width:900px}.review-table th{background:#fafbfc;color:#8a90a3;font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;padding:15px 16px;text-align:left;white-space:nowrap}.review-table td{padding:16px;border-top:1px solid #f0f1f5;color:#596078;font-size:.86rem;vertical-align:middle}.review-product{display:flex;align-items:center;gap:10px;min-width:160px}.review-product img{width:48px;height:48px;border-radius:10px;object-fit:cover;background:#f4f5f8}.review-product strong{color:#2e3254;display:block;max-width:190px}.review-product small{color:#9aa0b0}.review-customer strong{display:block;color:#2e3254}.review-customer small{color:#9aa0b0}.review-stars{color:#f5a623;white-space:nowrap;letter-spacing:1px}.review-message{max-width:260px;white-space:normal;line-height:1.5}.review-status{display:inline-block;padding:6px 10px;border-radius:30px;font-size:.7rem;font-weight:800;text-transform:capitalize}.status-pending{background:#fff4d8;color:#b7791f}.status-approved{background:#dcfce7;color:#16834a}.status-rejected{background:#fee2e2;color:#c0394b}.review-actions{display:flex;gap:6px;flex-wrap:wrap}.review-actions form{display:inline}.action-approve{background:#e7f8ee;color:#16834a}.action-reject{background:#fff0f0;color:#c0394b}.action-pending{background:#eef1ff;color:#505db5}.action-delete{background:#f2f3f7;color:#626a7e}.review-alert{padding:12px 16px;border-radius:12px;margin-bottom:16px;font-size:.88rem}.review-alert-success{background:#e9f9ef;color:#167344}.review-alert-error{background:#fff0f0;color:#b42335}.review-empty{text-align:center;padding:48px 20px;color:#9298a8}.review-pagination{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:16px 20px;flex-wrap:wrap}.review-pagination a{padding:7px 12px;border-radius:8px;background:#f2f3f7;color:#4b526c;text-decoration:none;font-weight:700;font-size:.8rem}.review-pagination a.current{background:#ff4d6d;color:#fff}
@media(max-width:991px){.reviews-page{margin-left:0;padding:82px 18px 35px}.review-stats{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:575px){.reviews-page{padding-left:12px;padding-right:12px}.review-stats{gap:10px}.review-stat{padding:14px}.review-stat strong{font-size:1.4rem}.review-filters{padding:14px}.review-filters input,.review-filters select{width:100%;min-width:0}}
</style>

<main class="reviews-page">
    <div class="page-heading">
        <div>
            <h1 class="page-title">Product Reviews</h1>
            <div class="sub-title">Manage customer feedback and publish approved reviews.</div>
        </div>
        <span class="review-btn review-btn-light"><i class="fa-regular fa-star"></i> <?= number_format((int)$stats['total']); ?> Total Reviews</span>
    </div>

    <?php if ($successMessage): ?><div class="review-alert review-alert-success"><?= h($successMessage); ?></div><?php endif; ?>
    <?php if ($errorMessage): ?><div class="review-alert review-alert-error"><?= h($errorMessage); ?></div><?php endif; ?>

    <section class="review-stats">
        <div class="review-stat"><span>Total Reviews</span><strong><?= number_format((int)$stats['total']); ?></strong></div>
        <div class="review-stat"><span>Pending</span><strong><?= number_format((int)$stats['pending']); ?></strong></div>
        <div class="review-stat"><span>Approved</span><strong><?= number_format((int)$stats['approved']); ?></strong></div>
        <div class="review-stat"><span>Rejected</span><strong><?= number_format((int)$stats['rejected']); ?></strong></div>
    </section>

    <section class="review-table-card">
        <form class="review-filters" method="GET">
            <input type="search" name="search" placeholder="Search customer, product or review..." value="<?= h($search); ?>">
            <select name="status">
                <option value="">All Statuses</option>
                <option value="pending" <?= $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="approved" <?= $status === 'approved' ? 'selected' : ''; ?>>Approved</option>
                <option value="rejected" <?= $status === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
            </select>
            <select name="rating">
                <option value="0">All Ratings</option>
                <?php for ($r = 5; $r >= 1; $r--): ?>
                    <option value="<?= $r; ?>" <?= $rating === $r ? 'selected' : ''; ?>><?= $r; ?> Star<?= $r > 1 ? 's' : ''; ?></option>
                <?php endfor; ?>
            </select>
            <button class="review-btn review-btn-primary" type="submit"><i class="fa-solid fa-filter"></i> Filter</button>
            <a class="review-btn review-btn-light" href="product-reviews.php">Reset</a>
        </form>

        <div class="review-table-wrap">
            <table class="review-table">
                <thead><tr><th>#</th><th>Product</th><th>Customer</th><th>Rating</th><th>Review</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                <?php if ($reviews && $reviews->num_rows > 0): $serial = $offset + 1; ?>
                    <?php while ($review = $reviews->fetch_assoc()): ?>
                        <tr>
                            <td><?= $serial++; ?></td>
                            <td>
                                <div class="review-product">
                                    <?php if (!empty($review['image1'])): ?>
                                        <img src="../images/<?= h(basename($review['image1'])); ?>" alt="">
                                    <?php else: ?>
                                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='48' height='48'%3E%3Crect width='100%25' height='100%25' fill='%23f1f2f6'/%3E%3C/svg%3E" alt="">
                                    <?php endif; ?>
                                    <div><strong><?= h($review['product_name'] ?: 'Product removed'); ?></strong><small>Product ID: <?= (int)$review['product_id']; ?></small></div>
                                </div>
                            </td>
                            <td><div class="review-customer"><strong><?= h($review['customer_name']); ?></strong><small><?= h($review['customer_email']); ?></small></div></td>
                            <td><span class="review-stars" aria-label="<?= (int)$review['rating']; ?> out of 5 stars"><?php for ($s=1; $s<=5; $s++) echo $s <= (int)$review['rating'] ? '★' : '☆'; ?></span></td>
                            <td><div class="review-message"><strong><?= h($review['review_title']); ?></strong><br><?= h($review['review_message']); ?></div></td>
                            <td><?= h(date('d M Y', strtotime($review['created_at']))); ?></td>
                            <td><span class="review-status status-<?= h($review['status']); ?>"><?= h($review['status']); ?></span></td>
                            <td>
                                <div class="review-actions">
                                    <?php if ($review['status'] !== 'approved'): ?>
                                        <form method="POST"><input type="hidden" name="csrf_token" value="<?= h($_SESSION['reviews_csrf']); ?>"><input type="hidden" name="review_id" value="<?= (int)$review['id']; ?>"><button class="review-btn action-approve" name="action" value="approve" title="Approve"><i class="fa-solid fa-check"></i></button></form>
                                    <?php endif; ?>
                                    <?php if ($review['status'] !== 'rejected'): ?>
                                        <form method="POST"><input type="hidden" name="csrf_token" value="<?= h($_SESSION['reviews_csrf']); ?>"><input type="hidden" name="review_id" value="<?= (int)$review['id']; ?>"><button class="review-btn action-reject" name="action" value="reject" title="Reject"><i class="fa-solid fa-xmark"></i></button></form>
                                    <?php endif; ?>
                                    <?php if ($review['status'] !== 'pending'): ?>
                                        <form method="POST"><input type="hidden" name="csrf_token" value="<?= h($_SESSION['reviews_csrf']); ?>"><input type="hidden" name="review_id" value="<?= (int)$review['id']; ?>"><button class="review-btn action-pending" name="action" value="pending" title="Move to pending"><i class="fa-regular fa-clock"></i></button></form>
                                    <?php endif; ?>
                                    <form method="POST" onsubmit="return confirm('Permanently delete this review?');"><input type="hidden" name="csrf_token" value="<?= h($_SESSION['reviews_csrf']); ?>"><input type="hidden" name="review_id" value="<?= (int)$review['id']; ?>"><button class="review-btn action-delete" name="action" value="delete" title="Delete"><i class="fa-solid fa-trash"></i></button></form>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="8"><div class="review-empty"><i class="fa-regular fa-comment-dots fa-2x mb-3"></i><div class="fw-bold">No reviews found</div><small>Customer reviews will appear here when submitted.</small></div></td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="review-pagination">
                <small class="text-muted">Showing <?= $totalReviews ? $offset + 1 : 0; ?>–<?= min($offset + $perPage, $totalReviews); ?> of <?= $totalReviews; ?> reviews</small>
                <div class="d-flex gap-2 align-items-center">
                    <?php $baseParams = $_GET; unset($baseParams['page']); ?>
                    <?php if ($page > 1): ?><a href="?<?= h(http_build_query($baseParams + ['page' => $page - 1])); ?>">Previous</a><?php endif; ?>
                    <a class="current" href="#">Page <?= $page; ?> / <?= $totalPages; ?></a>
                    <?php if ($page < $totalPages): ?><a href="?<?= h(http_build_query($baseParams + ['page' => $page + 1])); ?>">Next</a><?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </section>
</main>
