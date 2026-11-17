<?php
session_start();
include "config/db.php";
$success = "";
$error = "";
function clean($value) { return htmlspecialchars((string)($value ?? ""), ENT_QUOTES, "UTF-8"); }

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $subject = trim($_POST["subject"] ?? "");
    $message = trim($_POST["message"] ?? "");

    if ($name === "" || $email === "" || $subject === "" || $message === "") {
        $error = "Please fill in all fields.";
    } elseif (mb_strlen($name) > 150 || mb_strlen($email) > 190 || mb_strlen($subject) > 255) {
        $error = "One or more fields exceed the allowed length.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message, status) VALUES (?, ?, ?, ?, 'new')");
        if ($stmt) {
            $stmt->bind_param("ssss", $name, $email, $subject, $message);
            if ($stmt->execute()) {
                $success = "Thank you! Your message has been received. We'll get back to you soon.";
                $_POST = [];
            } else { $error = "We couldn't submit your message. Please try again."; }
            $stmt->close();
        } else { $error = "Database error. Please try again later."; }
    }
}
include "include/header.php";
?>

<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

<style>
/* ==========================================================
   CONTACT PAGE — PREMIUM STYLES (Navy + Pink Theme)
   ========================================================== */
:root {
    --ink: #2e3254;
    --ink-2: #252a4a;
    --ink-3: #1f2340;
    --brand: #ff4d6d;
    --brand-2: #e63950;
    --brand-soft: #fff2f5;
    --line: #efe6ec;
    --muted: #8a8fa3;
    --ease: cubic-bezier(.2, .8, .2, 1);
}

body {
    font-family: 'Manrope', system-ui, sans-serif;
    background: #faf7f9; /* Soft pink tint */
}

/* ================= HERO BANNER ================= */
.contact-hero {
    position: relative;
    padding: 90px 0 80px;
    background: linear-gradient(135deg, var(--ink-3) 0%, var(--ink-2) 100%);
    overflow: hidden;
    border-bottom: 1px solid rgba(255, 255, 255, .05);
}

.contact-hero::before {
    content: '';
    position: absolute;
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, rgba(255, 77, 109, .15) 0%, transparent 70%);
    top: -150px;
    right: -100px;
    pointer-events: none;
}

.hero-text {
    position: relative;
    z-index: 2;
    text-align: center;
}

.hero-text h1 {
    font-family: 'Outfit', sans-serif;
    font-weight: 900;
    color: #fff;
    font-size: clamp(2rem, 4vw, 3rem);
    letter-spacing: -.02em;
    margin-bottom: 12px;
    text-shadow: 0 10px 30px rgba(0,0,0,.3);
}

.hero-text p {
    color: rgba(255, 255, 255, .7);
    max-width: 550px;
    margin: 0 auto;
    font-size: 1rem;
}

/* ================= MAIN LAYOUT ================= */
.contact-page {
    padding: 0 0 80px;
    margin-top: -40px; /* Overlap effect */
    position: relative;
    z-index: 10;
}

.contact-wrapper {
    max-width: 1150px;
    margin: auto;
    display: grid;
    grid-template-columns: .9fr 1.5fr;
    background: #fff;
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(46, 50, 84, .12);
    border: 1px solid var(--line);
}

/* ================= LEFT SIDE (INFO) ================= */
.contact-info {
    padding: 50px 40px;
    color: #fff;
    background: linear-gradient(145deg, var(--ink-2) 0%, var(--ink-3) 100%);
    position: relative;
    overflow: hidden;
}

/* Decorative circle in info card */
.contact-info::after {
    content: '';
    position: absolute;
    bottom: -50px;
    left: -50px;
    width: 200px;
    height: 200px;
    background: radial-gradient(circle, rgba(255, 77, 109, .2) 0%, transparent 70%);
    pointer-events: none;
}

.contact-info h3 {
    font-family: 'Outfit', sans-serif;
    color: #fff;
    font-size: 1.6rem;
    font-weight: 800;
    margin-bottom: 12px;
}

.contact-info > p {
    color: rgba(255,255,255,.7);
    font-size: .92rem;
    line-height: 1.8;
    margin-bottom: 35px;
}

.contact-detail {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    margin-bottom: 28px;
    position: relative;
    z-index: 2;
}

.contact-detail .contact-icon {
    width: 46px;
    height: 46px;
    flex: 0 0 46px;
    display: grid;
    place-items: center;
    border-radius: 14px;
    color: #fff;
    background: rgba(255, 255, 255, .05);
    border: 1px solid rgba(255, 255, 255, .1);
    font-size: 1.1rem;
    transition: all .3s var(--ease);
}

.contact-detail:hover .contact-icon {
    background: linear-gradient(135deg, var(--brand), var(--brand-2));
    border-color: transparent;
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(255, 77, 109, .3);
}

.contact-detail small {
    display: block;
    color: rgba(255,255,255,.5);
    font-size: .75rem;
    font-weight: 600;
    letter-spacing: .05em;
    text-transform: uppercase;
    margin-bottom: 4px;
}

.contact-detail strong,
.contact-detail a {
    display: block;
    color: #fff;
    font-size: .95rem;
    font-weight: 600;
    text-decoration: none;
    overflow-wrap: anywhere;
    transition: color .2s;
}

.contact-detail a:hover {
    color: var(--brand);
}

/* ================= RIGHT SIDE (FORM) ================= */
.contact-form {
    padding: 50px;
    background: #fff;
}

.contact-form h3 {
    font-family: 'Outfit', sans-serif;
    font-size: 1.6rem;
    font-weight: 800;
    color: var(--ink);
    margin-bottom: 8px;
}

.contact-form > p {
    color: var(--muted);
    font-size: .92rem;
    margin-bottom: 30px;
}

.contact-form label {
    font-family: 'Outfit', sans-serif;
    font-size: .82rem;
    font-weight: 700;
    color: var(--ink);
    letter-spacing: .02em;
    margin-bottom: 8px;
}

.contact-form .form-control {
    min-height: 50px;
    border: 1.5px solid var(--line);
    border-radius: 12px;
    padding: 12px 16px;
    font-family: 'Manrope', sans-serif;
    font-size: .9rem;
    color: var(--ink);
    background: #fafbfc;
    box-shadow: none;
    transition: all .3s var(--ease);
}

.contact-form .form-control::placeholder {
    color: #a0a5b8;
}

.contact-form textarea.form-control {
    min-height: 150px;
    resize: vertical;
}

.contact-form .form-control:focus {
    background: #fff;
    border-color: var(--brand);
    box-shadow: 0 0 0 4px var(--brand-soft);
}

/* Submit Button */
.contact-submit {
    border: 0;
    border-radius: 999px;
    padding: 14px 32px;
    color: #fff;
    font-family: 'Outfit', sans-serif;
    font-size: .88rem;
    font-weight: 800;
    letter-spacing: .05em;
    text-transform: uppercase;
    background: linear-gradient(135deg, var(--brand), var(--brand-2));
    box-shadow: 0 10px 24px rgba(255, 77, 109, .25);
    transition: all .35s var(--ease);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.contact-submit:hover {
    transform: translateY(-3px);
    box-shadow: 0 16px 32px rgba(255, 77, 109, .35);
    filter: brightness(1.05);
    color: #fff;
}

.contact-submit:active {
    transform: scale(.98);
}

.contact-submit i {
    font-size: .9rem;
    transition: transform .3s var(--ease);
}

.contact-submit:hover i {
    transform: translateX(4px);
}

/* ================= CUSTOM ALERTS ================= */
.custom-alert {
    padding: 16px 20px;
    border-radius: 12px;
    font-size: .9rem;
    font-weight: 600;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 12px;
    animation: fadeIn .4s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.custom-alert.success {
    background: #e8f5e9;
    color: #2e7d32;
    border: 1px solid #c8e6c9;
}

.custom-alert.danger {
    background: #ffebee;
    color: #c62828;
    border: 1px solid #ffcdd2;
}

.custom-alert i {
    font-size: 1.2rem;
}

/* ================= RESPONSIVE ================= */
@media(max-width: 991.98px) {
    .contact-wrapper {
        grid-template-columns: 1fr;
    }
    
    .contact-info {
        padding: 40px 30px;
    }
}

@media(max-width: 767.98px) {
    .contact-hero { padding: 70px 0 60px; }
    .contact-page { padding: 0 0 50px; margin-top: -20px; }
    
    .contact-form {
        padding: 30px 24px;
    }
    
    .contact-submit {
        width: 100%;
    }
}
</style>

<!-- ================= HERO BANNER ================= -->
<section class="contact-hero">
    <div class="container hero-text">
        <h1>Get In Touch</h1>
        <p>Have a question or need assistance? We'd love to hear from you. Send us a message.</p>
    </div>
</section>

<!-- ================= MAIN CONTENT ================= -->
<section class="contact-page">
    <div class="container">
        <div class="contact-wrapper">

            <!-- Contact Information -->
            <div class="contact-info">
                <h3>Let's Talk</h3>
                <p>We're here to help. Reach out to us for product enquiries, order assistance or any other questions.</p>

                <div class="contact-detail">
                    <div class="contact-icon">
                        <i class="fa-solid fa-envelope"></i>
                    </div>
                    <div>
                        <small>Email Us</small>
                        <a href="mailto:support@example.com">support@example.com</a>
                    </div>
                </div>

                <div class="contact-detail">
                    <div class="contact-icon">
                        <i class="fa-solid fa-phone"></i>
                    </div>
                    <div>
                        <small>Call Us</small>
                        <strong>Please use the contact form</strong>
                    </div>
                </div>

                <div class="contact-detail">
                    <div class="contact-icon">
                        <i class="fa-regular fa-clock"></i>
                    </div>
                    <div>
                        <small>Working Hours</small>
                        <strong>Monday - Saturday</strong>
                        <strong>10:00 AM - 7:00 PM</strong>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="contact-form">
                <h3>Send Us A Message</h3>
                <p>Fill out the form below and we'll get back to you.</p>

                <?php if ($success): ?>
                    <div class="custom-alert success">
                        <i class="fa-solid fa-circle-check"></i>
                        <div><?= clean($success) ?></div>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="custom-alert danger">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <div><?= clean($error) ?></div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label>Your Name</label>
                            <input 
                                type="text" 
                                name="name" 
                                class="form-control" 
                                placeholder="John Doe" 
                                maxlength="150" 
                                value="<?= clean($_POST['name'] ?? '') ?>" 
                                required
                            >
                        </div>

                        <div class="col-md-6">
                            <label>Email Address</label>
                            <input 
                                type="email" 
                                name="email" 
                                class="form-control" 
                                placeholder="john@example.com" 
                                maxlength="190" 
                                value="<?= clean($_POST['email'] ?? '') ?>" 
                                required
                            >
                        </div>

                        <div class="col-12">
                            <label>Subject</label>
                            <input 
                                type="text" 
                                name="subject" 
                                class="form-control" 
                                placeholder="How can we help?" 
                                maxlength="255" 
                                value="<?= clean($_POST['subject'] ?? '') ?>" 
                                required
                            >
                        </div>

                        <div class="col-12">
                            <label>Your Message</label>
                            <textarea 
                                name="message" 
                                class="form-control" 
                                placeholder="Write your message here..." 
                                required
                            ><?= clean($_POST['message'] ?? '') ?></textarea>
                        </div>

                        <div class="col-12 mt-4">
                            <button type="submit" class="contact-submit">
                                Send Message
                                <i class="fa-solid fa-arrow-right"></i>
                            </button>
                        </div>

                    </div>
                </form>

            </div>
        </div>
    </div>
</section>

<?php include "include/footer.php"; ?>