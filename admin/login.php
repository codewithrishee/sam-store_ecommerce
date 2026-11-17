
<?php
session_start();
include "../config/db.php";

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM admins WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();

        if (
            password_verify($password, $admin['password']) ||
            $password === $admin['password']
        ) {
            if ($password === $admin['password']) {
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $update = $conn->prepare(
                    "UPDATE admins SET password=? WHERE id=?"
                );
                $update->bind_param("si", $newHash, $admin['id']);
                $update->execute();
            }

            session_regenerate_id(true);
            $_SESSION['admin'] = $admin['email'];
            $_SESSION['admin_name'] = $admin['name'];

            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Invalid email or password!";
        }
    } else {
        $error = "Admin access denied!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Portal | Secure Login</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
:root {
    --primary: #10b981;
    --primary-dark: #059669;
    --dark: #101a18;
    --text: #1e293b;
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
}

.login-wrapper {
    width: 100%;
    max-width: 1050px;
    min-height: 610px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    background: #fff;
    border-radius: 22px;
    overflow: hidden;
    box-shadow: 0 25px 80px rgba(15, 23, 42, .12);
    animation: slideUp .7s ease;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(25px); }
    to { opacity: 1; transform: translateY(0); }
}

/* BRAND PANEL */

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
    box-shadow: 0 0 0 45px rgba(16,185,129,.035),
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

.brand-content > p {
    color: #a6b5af;
    font-size: 13px;
    line-height: 1.9;
    max-width: 340px;
}

.features {
    margin-top: 35px;
}

.feature {
    display: flex;
    align-items: center;
    gap: 13px;
    margin-bottom: 20px;
    font-size: 13px;
    color: #d5e1dc;
}

.feature-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: rgba(16,185,129,.12);
    color: #6ee7b7;
    display: flex;
    align-items: center;
    justify-content: center;
}

.brand-footer {
    color: #71847b;
    font-size: 11px;
    position: relative;
    z-index: 1;
}

/* LOGIN PANEL */

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
}

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

.toggle-password {
    position: absolute;
    right: 16px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #94a3a0;
}

.toggle-password:hover {
    color: var(--primary-dark);
}

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

.security-box {
    margin-top: 27px;
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
    margin-top: 30px;
}

.alert {
    font-size: 12px;
    border-radius: 10px;
}

/* RESPONSIVE */

@media(max-width: 800px) {
    .login-wrapper {
        max-width: 480px;
        grid-template-columns: 1fr;
    }

    .brand-panel {
        padding: 30px;
        min-height: 300px;
    }

    .brand-content h1 {
        font-size: 30px;
    }

    .features {
        display: none;
    }

    .form-panel {
        padding: 40px 30px;
    }
}

@media(max-width: 480px) {
    body {
        padding: 12px;
    }

    .brand-panel {
        min-height: 250px;
    }

    .form-panel {
        padding: 35px 23px;
    }

    .form-header h2 {
        font-size: 27px;
    }
}

/* =====================================
   COMPLETE RESPONSIVE FIX
===================================== */

html {
    width: 100%;
    overflow-x: hidden;
}

body {
    width: 100%;
    min-width: 320px;
    min-height: 100vh;
    overflow-x: hidden;
}

/* TABLET */
@media (max-width: 991px) {

    body {
        padding: 20px;
    }

    .login-wrapper {
        max-width: 650px;
        grid-template-columns: 1fr;
        min-height: auto;
    }

    .brand-panel {
        min-height: 260px;
        padding: 30px 35px;
    }

    .brand-content {
        margin-top: 35px;
    }

    .brand-content h1 {
        font-size: 32px;
    }

    .brand-content > p {
        max-width: 100%;
    }

    .features {
        display: flex;
        flex-wrap: wrap;
        gap: 10px 20px;
        margin-top: 20px;
    }

    .feature {
        margin-bottom: 0;
    }

    .form-panel {
        padding: 45px 50px;
    }
}

/* MOBILE */
@media (max-width: 576px) {

    body {
        padding: 12px;
        display: block;
        min-height: 100vh;
        background: #f1f5f4;
    }

    .login-wrapper {
        width: 100%;
        max-width: 100%;
        min-height: auto;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        border-radius: 18px;
        overflow: hidden;
    }

    /* BRAND SECTION */

    .brand-panel {
        width: 100%;
        min-height: auto;
        padding: 24px 22px;
    }

    .brand-icon {
        width: 42px;
        height: 42px;
        font-size: 18px;
        border-radius: 11px;
    }

    .brand h3 {
        font-size: 17px;
    }

    .brand small {
        font-size: 9px;
    }

    .brand-content {
        margin-top: 28px;
    }

    .eyebrow {
        font-size: 9px;
        margin-bottom: 13px;
    }

    .brand-content h1 {
        font-size: clamp(25px, 7vw, 32px);
        line-height: 1.25;
        margin-bottom: 12px;
        letter-spacing: -1px;
    }

    .brand-content > p {
        font-size: 12px;
        line-height: 1.7;
        margin-bottom: 0;
    }

    .features {
        display: none;
    }

    .brand-footer {
        margin-top: 24px;
        font-size: 10px;
    }

    /* FORM SECTION */

    .form-panel {
        width: 100%;
        padding: 32px 22px 25px;
    }

    .form-header {
        margin-bottom: 25px;
    }

    .form-header .label {
        font-size: 10px;
        margin-bottom: 10px;
    }

    .form-header h2 {
        font-size: 27px;
        margin-bottom: 8px;
    }

    .form-header p {
        font-size: 12px;
        line-height: 1.6;
    }

    .form-label {
        font-size: 12px;
    }

    .input-wrap {
        margin-bottom: 20px;
    }

    .form-control {
        width: 100%;
        height: 52px;
        font-size: 13px;
        padding-left: 43px;
        padding-right: 43px;
    }

    .btn-login {
        width: 100%;
        height: 52px;
        font-size: 12px;
    }

    .security-box {
        margin-top: 22px;
        padding: 12px;
        gap: 10px;
    }

    .security-box p {
        font-size: 10px;
    }

    .bottom-text {
        margin-top: 23px;
        font-size: 10px;
    }
}

/* SMALL MOBILE */
@media (max-width: 360px) {

    body {
        padding: 8px;
    }

    .brand-panel {
        padding: 20px 17px;
    }

    .form-panel {
        padding: 28px 17px 22px;
    }

    .brand-content h1 {
        font-size: 25px;
    }

    .form-header h2 {
        font-size: 24px;
    }

    .form-control {
        font-size: 12px;
    }

    .btn-login {
        font-size: 11px;
    }
}

/* LANDSCAPE MOBILE */
@media (max-height: 600px) and (max-width: 850px) {

    body {
        display: block;
    }

    .login-wrapper {
        margin: 10px auto;
    }

    .brand-panel {
        min-height: auto;
    }

    .brand-content {
        margin-top: 20px;
    }

    .form-panel {
        padding-top: 30px;
        padding-bottom: 30px;
    }
}

</style>
</head>

<body>

<div class="login-wrapper">

    <!-- ADMIN BRANDING -->
    <section class="brand-panel">

        <div class="brand">
            <div class="brand-icon">
                <i class="fa-solid fa-store"></i>
            </div>

            <div>
                <h3>STORE ADMIN</h3>
                <small>MANAGEMENT PORTAL</small>
            </div>
        </div>

        <div class="brand-content">

            <div class="eyebrow">
                <i class="fa-solid fa-circle me-2"></i>
                ADMINISTRATION SYSTEM
            </div>

            <h1>Control Your<br><span>Entire Store.</span></h1>

            <p>
                Manage your e-commerce operations from one
                powerful dashboard. Everything you need,
                right where you need it.
            </p>

            <div class="features">

                <div class="feature">
                    <div class="feature-icon">
                        <i class="fa-solid fa-box"></i>
                    </div>
                    Product Management
                </div>

                <div class="feature">
                    <div class="feature-icon">
                        <i class="fa-solid fa-cart-shopping"></i>
                    </div>
                    Orders & Inventory
                </div>

                <div class="feature">
                    <div class="feature-icon">
                        <i class="fa-solid fa-chart-pie"></i>
                    </div>
                    Sales & Analytics
                </div>

            </div>
        </div>

        <div class="brand-footer">
            © <?php echo date('Y'); ?> Admin Management System
        </div>

    </section>

    <!-- LOGIN FORM -->
    <section class="form-panel">

        <div class="form-header">
            <div class="label">SECURE ADMIN ACCESS</div>
            <h2>Welcome Back!</h2>
            <p>Sign in to manage your e-commerce store.</p>
        </div>

        <?php if(isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fa-solid fa-circle-exclamation me-2"></i>
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div>
                <label class="form-label">Email Address</label>

                <div class="input-wrap">
                    <i class="fa-regular fa-envelope left-icon"></i>

                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        placeholder="Enter admin email"
                        autocomplete="username"
                        required
                    >
                </div>
            </div>

            <div>
                <label class="form-label">Password</label>

                <div class="input-wrap">
                    <i class="fa-solid fa-lock left-icon"></i>

                    <input
                        type="password"
                        name="password"
                        id="password"
                        class="form-control"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required
                    >

                    <i class="fa-solid fa-eye toggle-password"
                       id="togglePassword"></i>
                </div>
            </div>

            <button type="submit" name="login" class="btn-login">
                Sign In to Admin Panel
                <i class="fa-solid fa-arrow-right ms-2"></i>
            </button>

        </form>

        <div class="security-box">
            <i class="fa-solid fa-shield-halved"></i>
            <p>
                <strong>Protected Admin Environment</strong><br>
                Authorized administrators only. Your session
                is protected with secure authentication.
            </p>
        </div>

        <div class="bottom-text">
            <i class="fa-solid fa-lock me-1"></i>
            Secure Admin Login Portal
        </div>

    </section>

</div>

<script>
const toggle = document.getElementById("togglePassword");
const password = document.getElementById("password");

toggle.addEventListener("click", function () {
    const visible = password.type === "password";

    password.type = visible ? "text" : "password";

    this.classList.toggle("fa-eye");
    this.classList.toggle("fa-eye-slash");
});
</script>

</body>
</html>
