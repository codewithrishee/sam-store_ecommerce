<?php
include __DIR__ . "/../include/header.php";
?>

<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

<style>
/* Universal Policy CSS */
:root { --ink: #2e3254; --ink-2: #252a4a; --ink-3: #1f2340; --brand: #ff4d6d; --brand-2: #e63950; --brand-soft: #fff2f5; --line: #efe6ec; --muted: #8a8fa3; --ease: cubic-bezier(.2, .8, .2, 1); }
body { font-family: 'Manrope', system-ui, sans-serif; background: #faf7f9; }
.legal-hero { position: relative; padding: 80px 0 70px; background: linear-gradient(135deg, var(--ink-3) 0%, var(--ink-2) 100%); overflow: hidden; border-bottom: 1px solid rgba(255,255,255,.05); }
.legal-hero::before { content: ''; position: absolute; width: 500px; height: 500px; background: radial-gradient(circle, rgba(255,77,109,.15) 0%, transparent 70%); top: -150px; left: -150px; }
.hero-content { position: relative; z-index: 2; text-align: center; }
.hero-content h1 { font-family: 'Outfit', sans-serif; font-weight: 900; color: #fff; font-size: clamp(2rem, 4vw, 3rem); margin-bottom: 15px; text-shadow: 0 10px 30px rgba(0,0,0,.3); }
.hero-content p { color: rgba(255,255,255,.7); font-size: 1rem; margin-bottom: 15px; }
.last-updated-badge { display: inline-flex; align-items: center; gap: 8px; background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.15); padding: 6px 16px; border-radius: 999px; color: rgba(255,255,255,.9); font-family: 'Outfit', sans-serif; font-size: .75rem; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; }
.last-updated-badge i { color: var(--brand); }

.policy-page { padding: 60px 0 80px; margin-top: -40px; position: relative; z-index: 10; }
.policy-card { max-width: 1000px; margin: auto; background: #fff; padding: clamp(30px, 5vw, 55px); border-radius: 24px; box-shadow: 0 20px 50px rgba(46,50,84,.08); border: 1px solid var(--line); }
.policy-content p, .policy-content li { color: #56607a; line-height: 1.8; font-size: .96rem; margin-bottom: 15px; }
.policy-content h2 { font-family: 'Outfit', sans-serif; font-size: 1.35rem; font-weight: 800; color: var(--ink); margin: 35px 0 15px; padding-left: 14px; border-left: 4px solid var(--brand); }
.policy-note { background: var(--brand-soft); border-left: 4px solid var(--brand); padding: 18px 22px; border-radius: 0 12px 12px 0; color: var(--ink); font-size: .95rem; line-height: 1.7; margin: 25px 0; }
.policy-note strong { font-family: 'Outfit', sans-serif; color: var(--brand); display: block; margin-bottom: 4px; }
.policy-content a { color: var(--brand); text-decoration: none; font-weight: 600; border-bottom: 1px dashed rgba(255,77,109,.4); transition: .3s; }
.policy-content a:hover { color: var(--brand-2); border-bottom-style: solid; }
@media(max-width: 767.98px) { .legal-hero { padding: 60px 0 50px; } .policy-page { padding: 30px 0 50px; margin-top: -20px; } .policy-card { padding: 25px 20px; border-radius: 16px; } }
</style>

<section class="legal-hero">
    <div class="container hero-content">
        <h1>Terms &amp; Conditions</h1>
        <p>Please read these terms before using the SamPets website or placing an order.</p>
        <div class="last-updated-badge"><i class="fa-solid fa-clock-rotate-left"></i> Last updated: November 2026</div>
    </div>
</section>

<section class="policy-page">
    <div class="container">
        <article class="policy-card">
            <div class="policy-content">
                <p>These Terms &amp; Conditions apply to your access to and use of the SamPets website. By using the website, you agree to these terms, subject to rights that cannot be excluded under applicable law.</p>
                
                <div class="policy-note">
                    <strong>Important:</strong> This is a starter template, not legal advice. Have the terms reviewed for your business, products and applicable Indian laws before publishing.
                </div>

                <h2>1. Website use</h2>
                <p>You agree to use this website lawfully and not to interfere with its security, operation or availability.</p>

                <h2>2. Product information</h2>
                <p>We make reasonable efforts to display product descriptions, images, prices and availability accurately. Colours and appearance may vary depending on your screen and device. If an error is identified, we will take appropriate steps in accordance with applicable law.</p>

                <h2>3. Prices and orders</h2>
                <p>Prices, taxes, delivery charges and other applicable costs will be displayed or communicated before order completion. An order is subject to confirmation and product availability. We may contact you if clarification is required.</p>

                <h2>4. Payments</h2>
                <p>Payments may be handled by third-party payment providers. Their terms and privacy practices may also apply. Do not send full card details or passwords through the contact form.</p>

                <h2>5. Cancellations, returns and refunds</h2>
                <p>Requests are handled under the applicable <a href="cancellation-policy.php">Cancellation Policy</a>, <a href="return-policy.php">Return &amp; Exchange Policy</a> and <a href="refund-policy.php">Refund Policy</a>, without limiting statutory consumer rights.</p>

                <h2>6. Intellectual property</h2>
                <p>Website text, branding, graphics and other content may be protected by intellectual-property laws. You may not copy or commercially use them without permission, except as allowed by law.</p>

                <h2>7. Third-party services and links</h2>
                <p>The website may link to third-party services. We do not control their content or policies. Your use of those services may be subject to their own terms.</p>

                <h2>8. Limitation and legal rights</h2>
                <p>Nothing in these terms excludes or limits any liability or consumer right that cannot lawfully be excluded or limited under applicable law.</p>

                <h2>9. Changes</h2>
                <p>We may update these terms from time to time. The revised version will be posted on this page with an updated date.</p>

                <h2>10. Contact</h2>
                <p>For questions about these terms, please use our <a href="contact.php">Contact Us</a> page.</p>
            </div>
        </article>
    </div>
</section>

<?php
include __DIR__ . "/../include/footer.php";
?>