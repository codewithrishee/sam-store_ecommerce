<?php
session_start();
if (empty($_SESSION['admin']) && empty($_SESSION['admin_name'])) { header("Location: login.php"); exit; }
require_once "../config/db.php";
function e($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
$id=(int)($_GET['id'] ?? $_POST['id'] ?? 0); if($id<1){header('Location: contact-messages.php');exit;}
$notice=''; $error='';
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['send_reply'])){
    $reply=trim($_POST['reply'] ?? '');
    if($reply===''){ $error='Please write a reply first.'; }
    else {
        $adminId=(int)($_SESSION['admin_id'] ?? 0); $stmt=$conn->prepare("INSERT INTO contact_replies (contact_message_id, admin_id, reply, email_status) VALUES (?, NULLIF(?,0), ?, 'pending')");
        $stmt->bind_param('iis',$id,$adminId,$reply);
        if($stmt->execute()){
            $up=$conn->prepare("UPDATE contact_messages SET status='replied' WHERE id=?");$up->bind_param('i',$id);$up->execute();$up->close();
            $notice='Reply saved successfully. Email delivery is not enabled yet; configure Gmail SMTP when the project goes live.';
        } else {$error='Could not save reply. Please try again.';}
        $stmt->close();
    }
}
if(isset($_GET['mark_read'])){$up=$conn->prepare("UPDATE contact_messages SET status='read' WHERE id=? AND status='new'");$up->bind_param('i',$id);$up->execute();$up->close();header('Location: view-message.php?id='.$id);exit;}
$stmt=$conn->prepare("SELECT * FROM contact_messages WHERE id=? LIMIT 1");$stmt->bind_param('i',$id);$stmt->execute();$message=$stmt->get_result()->fetch_assoc();$stmt->close();if(!$message){header('Location: contact-messages.php');exit;}
$replies=$conn->prepare("SELECT * FROM contact_replies WHERE contact_message_id=? ORDER BY sent_at ASC, id ASC");$replies->bind_param('i',$id);$replies->execute();$replyRows=$replies->get_result();
include 'header.php'; include 'sidebar.php';
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>View Contact Message</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link rel="stylesheet" href="admin-style.css"><link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet"><style>body{background:#f4f7f6;font-family:Manrope,sans-serif}.main-content{margin-left:290px;margin-top:75px;padding:30px 40px;min-height:calc(100vh - 75px)}.card-box{background:#fff;border:1px solid #efe6ec;border-radius:20px;padding:25px;box-shadow:0 5px 20px rgba(46,50,84,.04);margin-bottom:20px}.page-title{font:800 1.6rem Outfit;color:#2e3254}.muted{color:#8a8fa3}.form-control{border-radius:12px;border:1px solid #efe6ec;padding:12px}.btn-pink{background:linear-gradient(135deg,#ff4d6d,#e63950);color:#fff;border:0;border-radius:30px;padding:11px 22px;font-weight:800}.reply-item{background:#faf8fa;border:1px solid #efe6ec;border-radius:14px;padding:18px;margin-top:12px;white-space:pre-wrap}.message-body{white-space:pre-wrap;line-height:1.8;color:#424862;background:#fafbfc;border-radius:14px;padding:20px}@media(max-width:991px){.main-content{margin-left:0!important;padding:85px 18px 25px!important;margin-top:0}}</style></head><body><main class="main-content"><div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4"><div><h2 class="page-title mb-1">Contact Enquiry #<?= (int)$message['id']?></h2><p class="muted mb-0">Received <?=date('d M Y, h:i A',strtotime($message['created_at']))?></p></div><a href="contact-messages.php" class="btn btn-light border rounded-pill px-4">Back to Messages</a></div>
<?php if($notice):?><div class="alert alert-success"><?=e($notice)?></div><?php endif;?><?php if($error):?><div class="alert alert-danger"><?=e($error)?></div><?php endif;?>
<div class="card-box"><div class="d-flex justify-content-between flex-wrap gap-2 mb-3"><h5 class="page-title fs-5 mb-0"><?=e($message['subject'])?></h5><span class="badge text-bg-light border"><?=e($message['status'])?></span></div><div class="row g-3 mb-3"><div class="col-md-6"><small class="muted">CUSTOMER NAME</small><div class="fw-bold"><?=e($message['name'])?></div></div><div class="col-md-6"><small class="muted">EMAIL ADDRESS</small><div><a href="mailto:<?=e($message['email'])?>"><?=e($message['email'])?></a></div></div></div><small class="muted">MESSAGE</small><div class="message-body mt-2"><?=e($message['message'])?></div><?php if($message['status']==='new'):?><a href="view-message.php?id=<?=$id?>&mark_read=1" class="btn btn-sm btn-outline-secondary rounded-pill mt-3">Mark as Read</a><?php endif;?></div>
<div class="card-box"><h5 class="page-title fs-5">Reply to Customer</h5><p class="muted small">Your reply will be saved in the conversation. Email sending is not configured in this local version.</p><form method="POST"><input type="hidden" name="id" value="<?=$id?>"><textarea name="reply" class="form-control mb-3" rows="5" placeholder="Write your response..." required></textarea><button type="submit" name="send_reply" class="btn-pink">Save Reply</button></form></div>
<div class="card-box"><h5 class="page-title fs-5">Reply History</h5><?php if($replyRows->num_rows):while($r=$replyRows->fetch_assoc()):?><div class="reply-item"><div class="d-flex justify-content-between flex-wrap gap-2 mb-2"><strong>Admin Reply</strong><small class="muted"><?=date('d M Y, h:i A',strtotime($r['sent_at']))?></small></div><?=e($r['reply'])?><div class="small muted mt-2">Email status: <?=e($r['email_status'])?></div></div><?php endwhile;else:?><p class="muted mb-0">No replies yet.</p><?php endif;?></div></main></body></html>
