
<?php
session_start();
include "config/db.php";

if(isset($_POST['login'])){

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $q = $stmt->get_result();

    if($q->num_rows > 0){

        $data = $q->fetch_assoc();

        if(password_verify($password, $data['password'])){

            session_regenerate_id(true);

            $_SESSION['user'] = $data['email'];
            $_SESSION['msg'] = "Welcome Back ".$data['name']." !";

            if(isset($_SESSION['redirect_after_login'])) {

    $redirect = $_SESSION['redirect_after_login'];

    unset($_SESSION['redirect_after_login']);

    header("Location: " . $redirect);
    exit;

}

header("Location: index.php");
exit;
        }else{

            $error = "Incorrect Password";

        }

    }else{

        $error = "Email Not Registered";

    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Login | Online Shopping</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>

:root {
    --primary: #10b981;
    --primary-dark: #059669;
    --dark: #101a18;
    --muted: #94a3b8;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    min-height: 100vh;
    font-family: 'Inter', sans-serif;
    background: #f1f5f4;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 25px;
    overflow-x: hidden;
}

/* MAIN WRAPPER */

.login-wrapper {
    width: 100%;
    max-width: 1050px;
    min-height: 610px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    background: #fff;
    border-radius: 22px;
    overflow: hidden;
    box-shadow: 0 25px 80px rgba(15,23,42,.12);
    animation: slideUp .7s ease;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(25px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* LEFT BRANDING */

.brand-panel {
    background: #101a18;
    color: white;
    padding: 45px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
    overflow: hidden;
}

.brand-panel::before {
    content: "";
    position: absolute;
    width: 400px;
    height: 400px;
    border-radius: 50%;
    border: 1px solid rgba(16,185,129,.2);
    right: -180px;
    top: 120px;
    box-shadow:
        0 0 0 45px rgba(16,185,129,.035),
        0 0 0 90px rgba(16,185,129,.025);
}

.brand {
    display: flex;
    align-items: center;
    gap: 13px;
    position: relative;
    z-index: 1;
}

.brand-icon {
    width: 48px;
    height: 48px;
    border-radius: 13px;
    background: var(--primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 21px;
}

.brand h3 {
    font-size: 20px;
    font-weight: 800;
    margin: 0;
    letter-spacing: -.5px;
}

.brand small {
    display: block;
    color: #94a3b8;
    font-size: 10px;
    letter-spacing: 2px;
    margin-top: 3px;
}

.brand-content {
    position: relative;
    z-index: 1;
}

.eyebrow {
    color: #6ee7b7;
    font-size: 11px;
    letter-spacing: 2px;
    font-weight: 700;
    margin-bottom: 20px;
}

.brand-content h1 {
    font-size: 39px;
    line-height: 1.25;
    font-weight: 800;
    letter-spacing: -1.5px;
    margin-bottom: 18px;
}

.brand-content h1 span {
    color: var(--primary);
}

.brand-content p {
    color: #a6b5af;
    font-size: 13px;
    line-height: 1.9;
    max-width: 340px;
}

/* SHOPPING VISUAL */

.shopping-visual {
    height: 150px;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    margin-top: 20px;
}

.shopping-icon {
    width: 115px;
    height: 115px;
    border-radius: 35px;
    background: rgba(16,185,129,.12);
    border: 1px solid rgba(16,185,129,.3);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6ee7b7;
    font-size: 48px;
    animation: floating 3s ease-in-out infinite;
}

@keyframes floating {
    0%,100% {
        transform: translateY(0) rotate(-8deg);
    }
    50% {
        transform: translateY(-10px) rotate(-8deg);
    }
}

.floating-item {
    position: absolute;
    background: #fff;
    color: #1e293b;
    padding: 11px 15px;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 700;
    box-shadow: 0 10px 25px rgba(0,0,0,.15);
}

.item-one {
    top: 5px;
    right: 8%;
}

.item-two {
    bottom: 5px;
    left: 5%;
}

.brand-footer {
    color: #71847b;
    font-size: 11px;
    position: relative;
    z-index: 1;
}

/* RIGHT LOGIN PANEL */

.form-panel {
    padding: 65px 60px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.form-header {
    margin-bottom: 35px;
}

.form-header .label {
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 2px;
    color: var(--primary-dark);
    margin-bottom: 12px;
}

.form-header h2 {
    font-size: 30px;
    font-weight: 800;
    letter-spacing: -1px;
    color: var(--dark);
    margin-bottom: 10px;
}

.form-header p {
    color: #84918b;
    font-size: 13px;
    line-height: 1.7;
}

/* INPUTS */

.form-label {
    font-size: 12px;
    font-weight: 700;
    color: #33443c;
    margin-bottom: 9px;
}

.input-wrap {
    position: relative;
    margin-bottom: 23px;
}

.input-wrap .left-icon {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #9aa9a1;
    z-index: 2;
}

.form-control {
    height: 53px;
    border-radius: 10px;
    background: #f8faf9;
    border: 1px solid #e5ebe7;
    padding: 12px 44px;
    font-size: 13px;
    transition: .3s;
}

.form-control:focus {
    background: #fff;
    border-color: var(--primary);
    box-shadow: 0 0 0 4px rgba(16,185,129,.09);
}

/* PASSWORD TOGGLE */

.toggle-password {
    position: absolute;
    right: 16px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #94a3a0;
    z-index: 3;
}

.toggle-password:hover {
    color: var(--primary-dark);
}

/* FORGOT PASSWORD */

.forgot-link {
    display: block;
    text-align: right;
    margin-top: -10px;
    margin-bottom: 22px;
    font-size: 11px;
}

.forgot-link a {
    color: var(--primary-dark);
    font-weight: 700;
    text-decoration: none;
}

.forgot-link a:hover {
    text-decoration: underline;
}

/* LOGIN BUTTON */

.btn-login {
    width: 100%;
    height: 53px;
    border: none;
    border-radius: 10px;
    background: var(--primary);
    color: white;
    font-size: 13px;
    font-weight: 700;
    transition: .3s;
    box-shadow: 0 7px 18px rgba(16,185,129,.2);
}

.btn-login:hover {
    background: var(--primary-dark);
    transform: translateY(-2px);
    box-shadow: 0 10px 22px rgba(16,185,129,.3);
}

/* REGISTER LINK */

.register-link {
    text-align: center;
    margin-top: 25px;
    font-size: 12px;
    color: #84918b;
}

.register-link a {
    color: var(--primary-dark);
    font-weight: 700;
    text-decoration: none;
}

.register-link a:hover {
    text-decoration: underline;
}

/* SECURITY BOX */

.security-box {
    margin-top: 28px;
    padding: 14px;
    border: 1px solid #e4f1e9;
    background: #f5fbf7;
    border-radius: 10px;
    display: flex;
    gap: 12px;
    align-items: center;
}

.security-box i {
    color: var(--primary-dark);
    font-size: 19px;
}

.security-box p {
    font-size: 10px;
    color: #718078;
    line-height: 1.7;
    margin: 0;
}

.security-box strong {
    color: #33443c;
}

.bottom-text {
    text-align: center;
    color: #a1aca6;
    font-size: 10px;
    margin-top: 25px;
}

/* ERROR */

.alert {
    font-size: 12px;
    border-radius: 10px;
}

/* TABLET */

@media(max-width: 991px) {

    body {
        padding: 20px;
    }

    .login-wrapper {
        max-width: 650px;
        grid-template-columns: 1fr;
    }

    .brand-panel {
        min-height: 290px;
        padding: 30px 35px;
    }

    .brand-content {
        margin-top: 30px;
    }

    .brand-content h1 {
        font-size: 32px;
    }

    .shopping-visual {
        display: none;
    }

    .form-panel {
        padding: 45px 50px;
    }
}

/* MOBILE - HIDE BRAND PANEL */

@media(max-width: 576px) {

    body {
        padding: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
    }

    .login-wrapper {
        width: 100%;
        max-width: 430px;
        min-height: auto;
        display: block;
        border-radius: 18px;
    }

    .brand-panel {
        display: none !important;
    }

    .form-panel {
        width: 100%;
        padding: 38px 23px;
    }

    .form-header {
        margin-bottom: 27px;
    }

    .form-header h2 {
        font-size: 27px;
    }

    .form-header p {
        font-size: 12px;
    }

    .form-control {
        height: 51px;
        font-size: 13px;
    }

    .btn-login {
        height: 52px;
    }

    .security-box {
        margin-top: 22px;
    }
}

/* SMALL MOBILE */

@media(max-width: 360px) {

    body {
        padding: 10px;
    }

    .form-panel {
        padding: 30px 18px;
    }

    .form-header h2 {
        font-size: 24px;
    }

    .form-control {
        font-size: 12px;
    }
}

</style>

</head>

<body>

<div class="login-wrapper">

    <!-- LEFT BRAND PANEL -->

    <div class="brand-panel">

        <div class="brand">

            <div class="brand-icon">
                <i class="fa-solid fa-store"></i>
            </div>

            <div>
                <h3>STORE</h3>
                <small>ONLINE SHOPPING</small>
            </div>

        </div>


        <div class="brand-content">

            <div class="eyebrow">
                <i class="fa-solid fa-circle me-2"></i>
                WELCOME TO OUR STORE
            </div>

            <h1>Welcome Back.<br>
                <span>Shop More.</span>
            </h1>

            <p>
                Sign in to your account and explore
                amazing products, exclusive deals and
                a seamless shopping experience.
            </p>

            <div class="shopping-visual">

                <div class="floating-item item-one">
                    <i class="fa-solid fa-tags text-success me-2"></i>
                    Special Offers
                </div>

                <div class="shopping-icon">
                    <i class="fa-solid fa-bag-shopping"></i>
                </div>

                <div class="floating-item item-two">
                    <i class="fa-solid fa-truck-fast text-success me-2"></i>
                    Fast Delivery
                </div>

            </div>

        </div>


        <div class="brand-footer">
            © <?= date('Y') ?> All Rights Reserved.
        </div>

    </div>


    <!-- LOGIN FORM -->

    <div class="form-panel">

        <div class="form-header">

            <div class="label">YOUR ACCOUNT</div>

            <h2>Welcome Back!</h2>

            <p>
                Enter your credentials to access your account.
            </p>

        </div>


        <?php if(isset($error)): ?>

            <div class="alert alert-danger">
                <i class="fa-solid fa-circle-exclamation me-2"></i>
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>

        <?php endif; ?>


        <form method="POST">

            <!-- EMAIL -->

            <div>

                <label class="form-label">Email Address</label>

                <div class="input-wrap">

                    <i class="fa-regular fa-envelope left-icon"></i>

                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        placeholder="Enter your email address"
                        autocomplete="username"
                        maxlength="254"
                        required
                    >

                </div>

            </div>


            <!-- PASSWORD -->

            <div>

                <label class="form-label">Password</label>

                <div class="input-wrap">

                    <i class="fa-solid fa-lock left-icon"></i>

                    <input
                        type="password"
                        name="password"
                        id="loginPass"
                        class="form-control"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required
                    >

                    <i class="fa-solid fa-eye toggle-password"
                       id="loginEye"
                       onclick="toggleLoginPass()"></i>

                </div>

            </div>


            <!-- FORGOT PASSWORD -->

            <div class="forgot-link">

                <a href="forgot.php">
                    Forgot Password?
                </a>

            </div>


            <!-- LOGIN BUTTON -->

            <button type="submit" name="login" class="btn-login">

                Sign In to Your Account
                <i class="fa-solid fa-arrow-right ms-2"></i>

            </button>

        </form>


        <!-- REGISTER LINK -->

        <div class="register-link">

            Don't have an account?

            <a href="register.php">Create Account</a>

        </div>


        <!-- SECURITY -->

        <div class="security-box">

            <i class="fa-solid fa-shield-halved"></i>

            <p>
                <strong>Secure Login Experience</strong><br>
                Your account credentials are protected with
                secure password authentication.
            </p>

        </div>


        <div class="bottom-text">

            <i class="fa-solid fa-lock me-1"></i>
            Secure Customer Login Portal

        </div>

    </div>

</div>


<script>

function toggleLoginPass(){

    const password = document.getElementById("loginPass");
    const eye = document.getElementById("loginEye");

    if(password.type === "password"){

        password.type = "text";
        eye.classList.replace("fa-eye", "fa-eye-slash");

    }else{

        password.type = "password";
        eye.classList.replace("fa-eye-slash", "fa-eye");

    }

}

</script>




<?php if(isset($_SESSION['wishlist_login_required'])): ?>

<script>
document.addEventListener('DOMContentLoaded', function() {

    Swal.fire({
        icon: 'warning',
        title: 'Login Required!',
        text: 'Please login first to view your wishlist.',
        confirmButtonText: 'Okay',
        confirmButtonColor: '#10b981'
    });

});
</script>

<?php
unset($_SESSION['wishlist_login_required']);
endif;
?>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>
