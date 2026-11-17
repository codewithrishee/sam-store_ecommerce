<?php
include __DIR__ . "/../include/header.php";
?>

<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

<style>
/* ==========================================================
   PRIVACY POLICY — PREMIUM STYLES (Navy + Pink Theme)
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
    background: #faf7f9; /* Soft pink tint for whole page */
}

/* ================= HERO BANNER ================= */
.legal-hero {
    position: relative;
    padding: 90px 0 80px;
    background: linear-gradient(135deg, var(--ink-3) 0%, var(--ink-2) 100%);
    overflow: hidden;
    border-bottom: 1px solid rgba(255, 255, 255, .05);
}

.legal-hero::before {
    content: '';
    position: absolute;
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, rgba(255, 77, 109, .15) 0%, transparent 70%);
    top: -150px;
    left: -150px;
    pointer-events: none;
}

.legal-hero::after {
    content: '';
    position: absolute;
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, rgba(255, 255, 255, .05) 0%, transparent 70%);
    bottom: -150px;
    right: -100px;
    pointer-events: none;
}

.hero-content {
    position: relative;
    z-index: 2;
    text-align: center;
}

.hero-content h1 {
    font-family: 'Outfit', sans-serif;
    font-weight: 900;
    color: #fff;
    font-size: clamp(2rem, 4vw, 3rem);
    letter-spacing: -.02em;
    margin-bottom: 15px;
    text-shadow: 0 10px 30px rgba(0,0,0,.3);
}

.hero-content p {
    color: rgba(255, 255, 255, .7);
    font-size: 1rem;
    margin-bottom: 20px;
}

.last-updated-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255, 255, 255, .1);
    border: 1px solid rgba(255, 255, 255, .15);
    padding: 6px 16px;
    border-radius: 999px;
    color: rgba(255, 255, 255, .9);
    font-family: 'Outfit', sans-serif;
    font-size: .75rem;
    font-weight: 700;
    letter-spacing: .05em;
    text-transform: uppercase;
}

.last-updated-badge i {
    color: var(--brand);
}

/* ================= MAIN LAYOUT ================= */
.privacy-page {
    padding: 60px 0 80px;
}

/* LEFT SIDEBAR (Sticky TOC) */
.toc-sidebar {
    position: sticky;
    top: 100px;
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 20px;
    padding: 24px;
    box-shadow: 0 10px 30px rgba(46, 50, 84, 0.05);
}

.toc-title {
    font-family: 'Outfit', sans-serif;
    font-weight: 800;
    font-size: 1.1rem;
    color: var(--ink);
    margin-bottom: 18px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--line);
    display: flex;
    align-items: center;
    gap: 10px;
}

.toc-title i {
    color: var(--brand);
}

.toc-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.toc-list li {
    margin-bottom: 5px;
}

.toc-list a {
    display: block;
    color: #56607a;
    text-decoration: none;
    font-size: .88rem;
    font-weight: 600;
    padding: 10px 14px;
    border-radius: 10px;
    transition: all .25s var(--ease);
    border-left: 3px solid transparent;
}

.toc-list a:hover {
    background: var(--brand-soft);
    color: var(--brand);
    border-left-color: var(--brand);
    padding-left: 18px;
}

/* RIGHT CONTENT (Main Card) */
.privacy-wrapper {
    background: #fff;
    border-radius: 24px;
    padding: 50px;
    box-shadow: 0 10px 40px rgba(46, 50, 84, 0.06);
    border: 1px solid var(--line);
}

/* Intro Text Highlight */
.privacy-intro {
    background: var(--brand-soft);
    border-left: 4px solid var(--brand);
    padding: 20px 24px;
    border-radius: 0 16px 16px 0;
    color: var(--ink);
    font-size: 1.02rem;
    line-height: 1.8;
    font-weight: 500;
    margin-bottom: 40px;
}

/* Headings */
.privacy-content h3 {
    font-family: 'Outfit', sans-serif;
    font-size: 1.4rem;
    font-weight: 800;
    color: var(--ink);
    margin: 40px 0 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    padding-bottom: 10px;
    border-bottom: 1px dashed var(--line);
}

.privacy-content h3 i {
    width: 36px;
    height: 36px;
    display: grid;
    place-items: center;
    background: var(--brand-soft);
    color: var(--brand);
    border-radius: 10px;
    font-size: 1rem;
}

/* Paragraphs & Lists */
.privacy-content p {
    color: #56607a;
    font-size: .96rem;
    line-height: 1.8;
    margin-bottom: 16px;
}

.privacy-content ul {
    list-style: none;
    padding-left: 0;
    margin-bottom: 24px;
}

.privacy-content ul li {
    position: relative;
    padding-left: 32px;
    margin-bottom: 12px;
    color: #56607a;
    font-size: .96rem;
    line-height: 1.7;
}

/* Custom Pink Checkmark for Lists */
.privacy-content ul li::before {
    content: '\f00c';
    font-family: 'Font Awesome 6 Free';
    font-weight: 900;
    position: absolute;
    left: 0;
    top: 2px;
    width: 20px;
    height: 20px;
    display: grid;
    place-items: center;
    background: var(--brand-soft);
    color: var(--brand);
    border-radius: 50%;
    font-size: .6rem;
}

/* ================= MOBILE RESPONSIVE ================= */
@media (max-width: 991.98px) {
    .toc-sidebar {
        position: relative;
        top: 0;
        margin-bottom: 30px;
    }
    
    .privacy-wrapper {
        padding: 30px 24px;
    }
}

@media (max-width: 575.98px) {
    .legal-hero { padding: 60px 0 50px; }
    .privacy-page { padding: 30px 0 50px; }
    .privacy-wrapper { padding: 25px 20px; border-radius: 20px; }
    .privacy-content h3 { font-size: 1.25rem; }
    .privacy-intro { padding: 16px 20px; font-size: .95rem; }
}
</style>

<!-- ================= HERO BANNER ================= -->
<section class="legal-hero">
    <div class="container hero-content">
        <h1>Privacy Policy</h1>
        <p>Your privacy matters to us. Learn how we collect and protect your information.</p>
        <div class="last-updated-badge">
            <i class="fa-solid fa-clock-rotate-left"></i>
            Last Updated: November 2026
        </div>
    </div>
</section>

<!-- ================= MAIN SECTION ================= -->
<section class="privacy-page">
    <div class="container">
        <div class="row g-4">
            
            <!-- LEFT: STICKY TABLE OF CONTENTS -->
            <div class="col-lg-4 col-xl-3">
                <div class="toc-sidebar">
                    <h5 class="toc-title"><i class="fa-solid fa-list-ul"></i> Table of Contents</h5>
                    <ul class="toc-list">
                        <li><a href="#sec-1">1. Information We Collect</a></li>
                        <li><a href="#sec-2">2. How We Use Your Info</a></li>
                        <li><a href="#sec-3">3. Cookies</a></li>
                        <li><a href="#sec-4">4. Data Protection</a></li>
                        <li><a href="#sec-5">5. Sharing of Information</a></li>
                        <li><a href="#sec-6">6. Third-Party Links</a></li>
                        <li><a href="#sec-7">7. Your Rights</a></li>
                        <li><a href="#sec-8">8. Changes to Policy</a></li>
                        <li><a href="#sec-9">9. Contact Us</a></li>
                    </ul>
                </div>
            </div>

            <!-- RIGHT: MAIN CONTENT -->
            <div class="col-lg-8 col-xl-9">
                <div class="privacy-wrapper">
                    <div class="privacy-content">

                        <p class="privacy-intro">
                            Welcome to our website. We respect your privacy and are
                            committed to protecting the personal information you share
                            with us while using our website and services.
                        </p>

                        <h3 id="sec-1"><i class="fa-solid fa-folder-open"></i> 1. Information We Collect</h3>
                        <p>We may collect the following information when you use our website:</p>
                        <ul>
                            <li>Name and contact details.</li>
                            <li>Email address and phone number, when provided.</li>
                            <li>Shipping and billing information when placing an order.</li>
                            <li>Messages and enquiries submitted through our contact form.</li>
                            <li>Basic website usage information through cookies and similar technologies.</li>
                        </ul>

                        <h3 id="sec-2"><i class="fa-solid fa-bullseye"></i> 2. How We Use Your Information</h3>
                        <p>We may use your information to:</p>
                        <ul>
                            <li>Process and manage customer orders.</li>
                            <li>Respond to questions and enquiries.</li>
                            <li>Provide customer support.</li>
                            <li>Improve our website and shopping experience.</li>
                            <li>Send important updates related to your orders or enquiries.</li>
                        </ul>

                        <h3 id="sec-3"><i class="fa-solid fa-cookie-bite"></i> 3. Cookies</h3>
                        <p>
                            Our website may use cookies to improve website functionality,
                            remember user preferences and understand website usage.
                            You can manage cookies through your browser settings.
                        </p>

                        <h3 id="sec-4"><i class="fa-solid fa-shield-halved"></i> 4. Data Protection</h3>
                        <p>
                            We take reasonable measures to protect the information
                            submitted through our website against unauthorized access,
                            alteration, disclosure or destruction. However, no online
                            transmission or storage system can be guaranteed to be
                            completely secure.
                        </p>

                        <h3 id="sec-5"><i class="fa-solid fa-share-nodes"></i> 5. Sharing of Information</h3>
                        <p>
                            We do not sell your personal information. Information may
                            be shared with service providers when necessary to operate
                            our website, process orders or provide services, or where
                            disclosure is required by applicable law.
                        </p>

                        <h3 id="sec-6"><i class="fa-solid fa-link"></i> 6. Third-Party Links</h3>
                        <p>
                            Our website may contain links to third-party websites.
                            We are not responsible for their privacy practices or
                            content. Please review their privacy policies separately.
                        </p>

                        <h3 id="sec-7"><i class="fa-solid fa-user-shield"></i> 7. Your Rights</h3>
                        <p>
                            Subject to applicable law, you may request access to,
                            correction of or deletion of your personal information.
                            You can contact us using the details on our Contact Us page.
                        </p>

                        <h3 id="sec-8"><i class="fa-solid fa-rotate"></i> 8. Changes to This Policy</h3>
                        <p>
                            We may update this Privacy Policy from time to time.
                            Any changes will be published on this page with an
                            updated revision date.
                        </p>

                        <h3 id="sec-9"><i class="fa-solid fa-envelope"></i> 9. Contact Us</h3>
                        <p>
                            If you have questions about this Privacy Policy or how
                            your information is handled, please contact us through
                            our Contact Us page.
                        </p>

                    </div>
                </div>
            </div>

        </div>
    </div>
</section>


<?php
include __DIR__ . "/../include/footer.php";
?>