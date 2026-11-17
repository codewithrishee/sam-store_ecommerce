
<?php
session_start();
include "config/db.php";

if(isset($_POST['register'])){

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Check existing email
    $stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $check = $stmt->get_result();

    if($check->num_rows > 0){

        $_SESSION['msg'] = "Email Already Registered";
        $_SESSION['icon'] = "error";

        header("location:register.php");
        exit;

    } else {

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare(
            "INSERT INTO users(name,email,password) VALUES(?,?,?)"
        );

        $stmt->bind_param("sss", $name, $email, $hash);

        if($stmt->execute()){

            $_SESSION['msg'] = "Account Created Successfully";
            $_SESSION['icon'] = "success";

            header("location:login.php");
            exit;

        } else {

            $_SESSION['msg'] = "Registration Failed";
            $_SESSION['icon'] = "error";

            header("location:register.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Create Account | E-Commerce</title>

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

.register-wrapper {
    width: 100%;
    max-width: 1050px;
    min-height: 650px;
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

/* LEFT BRANDING PANEL */

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
    transform: rotate(-8deg);
    animation: floating 3s ease-in-out infinite;
}

@keyframes floating {
    0%,100% { transform: translateY(0) rotate(-8deg); }
    50% { transform: translateY(-10px) rotate(-8deg); }
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

/* RIGHT FORM PANEL */

.form-panel {
    padding: 55px 60px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.form-header {
    margin-bottom: 30px;
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
    line-height: 1.6;
}

.form-label {
    font-size: 12px;
    font-weight: 700;
    color: #33443c;
    margin-bottom: 9px;
}

/* INPUT FIELDS */

.input-wrap {
    position: relative;
    margin-bottom: 19px;
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
    height: 52px;
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

/* PASSWORD */

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

/* REGISTER BUTTON */

.btn-register {
    width: 100%;
    height: 53px;
    border: none;
    border-radius: 10px;
    background: var(--primary);
    color: white;
    font-size: 13px;
    font-weight: 700;
    transition: .3s;
    margin-top: 5px;
    box-shadow: 0 7px 18px rgba(16,185,129,.2);
}

.btn-register:hover {
    background: var(--primary-dark);
    transform: translateY(-2px);
    color: white;
    box-shadow: 0 10px 22px rgba(16,185,129,.3);
}

/* LOGIN LINK */

.login-link {
    text-align: center;
    margin-top: 24px;
    font-size: 12px;
    color: #84918b;
}

.login-link a {
    color: var(--primary-dark);
    font-weight: 700;
    text-decoration: none;
}

.login-link a:hover {
    text-decoration: underline;
}

/* SECURITY BOX */

.security-box {
    margin-top: 24px;
    padding: 13px;
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
    margin-top: 23px;
}

/* TABLET */

@media(max-width: 991px) {

    body {
        padding: 20px;
    }

    .register-wrapper {
        max-width: 650px;
        grid-template-columns: 1fr;
    }

    .brand-panel {
        min-height: 300px;
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

/* MOBILE - HIDE LEFT PANEL */

@media(max-width: 576px) {

    body {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
        padding: 15px;
    }

    .register-wrapper {
        width: 100%;
        max-width: 430px;
        min-height: auto;
        display: block;
        border-radius: 18px;
    }

    /* LEFT SIDE COMPLETELY HIDDEN */

    .brand-panel {
        display: none !important;
    }

    /* ONLY REGISTRATION FORM */

    .form-panel {
        width: 100%;
        padding: 35px 23px;
    }

    .form-header {
        margin-bottom: 25px;
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

    .btn-register {
        height: 52px;
    }

    .security-box {
        margin-top: 20px;
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

<?php if(isset($_SESSION['msg'])): ?>

<script>
Swal.fire({
    icon: <?= json_encode($_SESSION['icon'] ?? 'info') ?>,
    title: <?= json_encode($_SESSION['msg']) ?>,
    showConfirmButton: false,
    timer: 2000
});
</script>

<?php
unset($_SESSION['msg']);
unset($_SESSION['icon']);
endif;
?>

<div class="register-wrapper">

    <!-- LEFT BRANDING PANEL (DESKTOP ONLY) -->

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
                JOIN OUR COMMUNITY
            </div>

            <h1>Discover More.<br>
                <span>Shop Better.</span>
            </h1>

            <p>
                Create your account and enjoy a seamless
                shopping experience. Discover amazing
                products and explore more possibilities.
            </p>

            <div class="shopping-visual">

                <div class="floating-item item-one">
                    <i class="fa-solid fa-gift text-success me-2"></i>
                    Exclusive Offers
                </div>

                <div class="shopping-icon">
                    <i class="fa-solid fa-bag-shopping"></i>
                </div>

                <div class="floating-item item-two">
                    <i class="fa-solid fa-heart text-danger me-2"></i>
                    Your Wishlist
                </div>

            </div>

        </div>

        <div class="brand-footer">
            © <?= date('Y') ?> All Rights Reserved.
        </div>

    </div>


    <!-- REGISTER FORM -->

    <div class="form-panel">

        <div class="form-header">

            <div class="label">CREATE YOUR ACCOUNT</div>

            <h2>Get Started!</h2>

            <p>
                Register today and start your shopping journey.
            </p>

        </div>


        <form method="POST">

            <!-- FULL NAME -->

            <div>
                <label class="form-label">Full Name</label>

                <div class="input-wrap">

                    <i class="fa-regular fa-user left-icon"></i>

                    <input
                        type="text"
                        name="name"
                        class="form-control"
                        placeholder="Enter your full name"
                        autocomplete="name"
                        maxlength="100"
                        required
                    >

                </div>
            </div>


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
                        autocomplete="email"
                        maxlength="254"
                        required
                    >

                </div>
            </div>


            <!-- PASSWORD -->

            <div>
                <label class="form-label">Create Password</label>

                <div class="input-wrap">

                    <i class="fa-solid fa-lock left-icon"></i>

                    <input
                        type="password"
                        name="password"
                        id="regPass"
                        class="form-control"
                        placeholder="Create a strong password"
                        autocomplete="new-password"
                        minlength="8"
                        required
                    >

                    <i class="fa-solid fa-eye toggle-password"
                       id="regEye"
                       onclick="toggleRegPass()"></i>

                </div>
            </div>


            <!-- SUBMIT -->

            <button type="submit" name="register" class="btn-register">

                Create Account
                <i class="fa-solid fa-arrow-right ms-2"></i>

            </button>

        </form>


        <!-- LOGIN LINK -->

        <div class="login-link">

            Already have an account?

            <a href="login.php">Sign In</a>

        </div>


        <!-- SECURITY -->

        <div class="security-box">

            <i class="fa-solid fa-shield-halved"></i>

            <p>
                <strong>Your information is safe</strong><br>
                Your password is securely encrypted before storage.
            </p>

        </div>


        <div class="bottom-text">

            <i class="fa-solid fa-lock me-1"></i>
            Secure Registration Portal

        </div>

    </div>

</div>


<script>

function toggleRegPass() {

    const password = document.getElementById("regPass");
    const eye = document.getElementById("regEye");

    if(password.type === "password") {

        password.type = "text";
        eye.classList.replace("fa-eye", "fa-eye-slash");

    } else {

        password.type = "password";
        eye.classList.replace("fa-eye-slash", "fa-eye");

    }
}

</script>

</body>
</html>
