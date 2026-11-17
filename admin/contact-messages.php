<?php
session_start();
if (empty($_SESSION['admin']) && empty($_SESSION['admin_name'])) { header("Location: login.php"); exit; }
require_once "../config/db.php";
include "header.php";
include "sidebar.php";
function e($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
$filter = $_GET['status'] ?? 'all';
$search = trim($_GET['q'] ?? '');
$where = [];
$params = [];
$types = '';
if (in_array($filter, ['new','read','replied'], true)) { $where[] = 'status = ?'; $params[] = $filter; $types .= 's'; }
if ($search !== '') { $where[] = '(name LIKE ? OR email LIKE ? OR subject LIKE ?)'; $like = "%$search%"; array_push($params,$like,$like,$like); $types .= 'sss'; }
$sql = "SELECT id,name,email,subject,status,created_at FROM contact_messages" . ($where ? ' WHERE '.implode(' AND ',$where) : '') . " ORDER BY created_at DESC";
$stmt=$conn->prepare($sql); if($params){$stmt->bind_param($types,...$params);} $stmt->execute(); $messages=$stmt->get_result();
$counts=['all'=>0,'new'=>0,'read'=>0,'replied'=>0];
$res=$conn->query("SELECT status, COUNT(*) total FROM contact_messages GROUP BY status");
while($r=$res->fetch_assoc()){ $counts[$r['status']] = (int)$r['total']; }
$counts['all']=$counts['new']+$counts['read']+$counts['replied'];
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Contact Messages</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link rel="stylesheet" href="admin-style.css"><link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet"><style>
body{background:#f4f7f6;font-family:Manrope,sans-serif}.main-content{margin-left:290px;margin-top:75px;padding:30px 40px;min-height:calc(100vh - 75px)}.page-card,.table-card{background:#fff;border:1px solid #efe6ec;border-radius:20px;box-shadow:0 5px 20px rgba(46,50,84,.04);overflow:hidden}.page-title{font:800 1.65rem Outfit,sans-serif;color:#2e3254}.muted{color:#8a8fa3}.stat{padding:20px;border:1px solid #efe6ec;border-radius:16px;background:#fff}.stat b{font:800 1.5rem Outfit;color:#2e3254}.stat span{display:block;color:#8a8fa3;font-size:.78rem;font-weight:700;text-transform:uppercase}.form-control,.form-select{border-radius:11px;border:1px solid #efe6ec;padding:11px 14px}.btn-pink{background:linear-gradient(135deg,#ff4d6d,#e63950);color:white;border:0;border-radius:30px;padding:10px 20px;font-weight:800}.table thead th{background:#faf8fa;color:#697087;font-size:.75rem;text-transform:uppercase;letter-spacing:.04em;padding:16px}.table td{padding:16px;vertical-align:middle;color:#454b65}.status{padding:6px 11px;border-radius:30px;font-size:.72rem;font-weight:800;text-transform:capitalize}.new{background:#fff0f2;color:#e63950}.read{background:#eaf3ff;color:#2872bd}.replied{background:#e8f8ef;color:#16834b}@media(max-width:991px){.main-content{margin-left:0!important;padding:85px 18px 25px!important;margin-top:0}.page-title{font-size:1.35rem}}
</style></head><body><main class="main-content"><div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4"><div><h2 class="page-title mb-1">Contact Messages</h2><p class="muted mb-0">Manage customer enquiries and replies</p></div></div>
<div class="row g-3 mb-4"><?php foreach(['all'=>'Total Messages','new'=>'New Messages','read'=>'Read Messages','replied'=>'Replied'] as $k=>$label): ?><div class="col-6 col-xl-3"><div class="stat"><span><?=e($label)?></span><b><?=number_format($counts[$k])?></b></div></div><?php endforeach; ?></div>
<div class="table-card"><div class="p-3 p-md-4 border-bottom"><form class="row g-2"><div class="col-md-5"><input class="form-control" name="q" value="<?=e($search)?>" placeholder="Search name, email or subject"></div><div class="col-md-3"><select class="form-select" name="status"><option value="all">All messages</option><?php foreach(['new','read','replied'] as $s): ?><option value="<?=$s?>" <?=$filter===$s?'selected':''?>><?=ucfirst($s)?></option><?php endforeach;?></select></div><div class="col-md-2"><button class="btn-pink w-100">Search</button></div><div class="col-md-2"><a href="contact-messages.php" class="btn btn-light border rounded-pill w-100 py-2">Reset</a></div></form></div>
<div class="table-responsive"><table class="table mb-0"><thead><tr><th>Customer</th><th>Subject</th><th>Date</th><th>Status</th><th>Action</th></tr></thead><tbody><?php if($messages->num_rows): while($m=$messages->fetch_assoc()): ?><tr><td><strong><?=e($m['name'])?></strong><div class="small muted"><?=e($m['email'])?></div></td><td><?=e($m['subject'])?></td><td><?=date('d M Y, h:i A',strtotime($m['created_at']))?></td><td><span class="status <?=e($m['status'])?>"><?=e($m['status'])?></span></td><td><a class="btn btn-sm btn-outline-dark rounded-pill px-3" href="view-message.php?id=<?=(int)$m['id']?>">View</a></td></tr><?php endwhile; else: ?><tr><td colspan="5" class="text-center py-5 muted">No contact messages found.</td></tr><?php endif;?></tbody></table></div></div></main></body></html>
