<?php
session_start();
include "config/db.php";

// Login check
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Secure email
$email = $_SESSION['user'];

// Fetch user (prepared statement)
$stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "User not found";
    exit;
}

// UPDATE PROFILE
if (isset($_POST['update'])) {

    $name  = htmlspecialchars(trim($_POST['name']));
    $phone = htmlspecialchars(trim($_POST['phone']));

    $imageName = $user['image']; // default old image

    // IMAGE UPLOAD
    if (!empty($_FILES['images']['name'])) {

        $file = $_FILES['images'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

        // Validate type
        if (!in_array($file['type'], $allowedTypes)) {
            echo "<script>alert('Only JPG, PNG, WEBP allowed');</script>";
        } 
        // Validate size (2MB)
        elseif ($file['size'] > 2 * 1024 * 1024) {
            echo "<script>alert('Image must be less than 2MB');</script>";
        } 
        else {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $imageName = time() . "_" . uniqid() . "." . $ext;

            $target = "images/" . $imageName;

            if (move_uploaded_file($file['tmp_name'], $target)) {

                // Delete old image
                if (!empty($user['image']) && file_exists("images/" . $user['image'])) {
                    unlink("images/" . $user['image']);
                }
            }
        }
    }

    // Update query (prepared)
    $stmt = $conn->prepare("UPDATE users SET name=?, phone=?, image=? WHERE email=?");
    $stmt->bind_param("ssss", $name, $phone, $imageName, $email);
    $stmt->execute();

    echo "<script>
        alert('Profile updated successfully');
        window.location='profile.php';
    </script>";
    exit;
}

include "include/header.php";
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
/* BODY */
body{
    background:#f4f6f9;
}

/* CARD */
.profile-card{
    max-width:480px;
    margin:60px auto;
    background:#fff;
    border-radius:16px;
    padding:30px;
    box-shadow:0 10px 30px rgba(0,0,0,0.08);
    transition:0.3s;
}

/* IMAGE */
.profile-img{
    width:130px;
    height:130px;
    object-fit:cover;
    border-radius:50%;
    border:5px solid #fff;
    box-shadow:0 8px 20px rgba(0,0,0,0.15);
}

/* INPUT */
.form-control{
    border-radius:10px;
    height:46px;
    font-size:14px;
}

/* BUTTON */
.btn-success{
    background:#ff0099;
    border:none;
    border-radius:10px;
    height:46px;
    font-weight:500;
}

.btn-success:hover{
    background:#e60086;
}

.btn-danger{
    border-radius:10px;
    height:46px;
}


/* 🔥 TABLET */
@media(max-width:768px){

    .profile-card{
        margin:30px 15px;
        padding:20px;
    }

    .profile-img{
        width:100px;
        height:100px;
    }

    .form-control{
        height:42px;
        font-size:13px;
    }

    .btn-success,
    .btn-danger{
        height:42px;
        font-size:14px;
    }

}


/* 📱 SMALL MOBILE */
@media(max-width:480px){

    .profile-card{
        margin:20px 10px;
        padding:18px;
        border-radius:12px;
    }

    .profile-img{
        width:85px;
        height:85px;
    }

    .form-control{
        height:40px;
        font-size:13px;
    }

    .btn-success,
    .btn-danger{
        height:40px;
        font-size:13px;
    }

}
</style>
</head>

<body>

<div class="container">
<div class="profile-card">

<h4 class="text-center mb-4 fw-semibold">My Profile</h4>

<?php
$img = !empty($user['image']) 
    ? "images/" . htmlspecialchars($user['image']) 
    : "https://via.placeholder.com/120";
?>

<div class="text-center mb-3">
    <img src="<?= $img ?>" class="profile-img mb-2">
</div>

<form method="POST" enctype="multipart/form-data">

<input type="file" name="images" class="form-control mb-3">

<label class="fw-semibold">Full Name</label>
<input type="text" name="name"
value="<?= htmlspecialchars($user['name']) ?>"
class="form-control mb-3" required>

<label class="fw-semibold">Phone Number</label>
<input type="text" name="phone"
value="<?= htmlspecialchars($user['phone']) ?>"
class="form-control mb-3" required>

<label class="fw-semibold">Email Address</label>
<input type="email"
value="<?= htmlspecialchars($user['email']) ?>"
class="form-control mb-4" readonly>

<button name="update" class="btn  w-100 mb-2">
Update Profile
</button>

<a href="logout.php" class="btn  w-100">
Logout
</a>

</form>

</div>
</div>

</body>
</html>