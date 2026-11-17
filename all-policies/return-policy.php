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
.policy-content ol { padding-left: 20px; margin-bottom: 20px; }
.policy-content ol li { padding-left: 8px; }
.policy-content ol li::marker { color: var(--brand); font-weight: 800; }
@media(max-width: 767.98px) { .legal-hero { padding: 60px 0 50px; } .policy-page { padding: 30px 0 50px; margin-top: -20px; } .policy-card { padding: 25px 20px; border-radius: 16px; } }
</style>

<section class="legal-hero">
    <div class="container hero-content">
        <h1>Return &amp; Exchange Policy</h1>
        <p>Information about requesting a return or exchange from SamPets.</p>
        <div class="last-updated-badge"><i class="fa-solid fa-clock-rotate-left"></i> Last updated: November 2026</div>
    </div>
</section>

<section class="policy-page">
    <div class="container">
        <article class="policy-card">
            <div class="policy-content">
                <p>At SamPets, we want customers to be satisfied with their purchase. If there is an issue with an item, contact our support team so we can review it.</p>
                
                <div class="policy-note">
                    <strong>Store setup required:</strong> Set your return window, eligible product categories, condition requirements, pickup process and any return-shipping charges before publishing this policy.
                </div>

                <h2>1. Return or exchange requests</h2>
                <p>Requests may be considered for items that are damaged, defective, incorrect or otherwise eligible under the terms shown at the time of purchase. Approval is subject to order verification and product review.</p>

                <h2>2. Request process</h2>
                <ol>
                    <li>Contact us with your order number.</li>
                    <li>Describe the reason for the request.</li>
                    <li>Attach clear photographs or a short video if the item is damaged, defective or incorrect.</li>
                    <li>Wait for our team to confirm the next steps before sending an item back.</li>
                </ol>

                <h2>3. Product condition</h2>
                <p>Where applicable, products should be returned with their original packaging, labels, accessories and included parts. Any condition requirements or hygiene-related restrictions must be clearly disclosed before purchase and must comply with applicable law.</p>

                <h2>4. Items not eligible</h2>
                <p>List any product-specific exclusions here only if they were clearly disclosed to customers before purchase and are lawful. Statutory consumer rights are not affected.</p>

                <h2>5. Exchange availability</h2>
                <p>Exchanges depend on stock availability. If the requested replacement is unavailable, our team will discuss the available resolution with you.</p>

                <h2>6. Return shipping</h2>
                <p><strong>Specify who pays return shipping, whether pickup is available and the applicable process before publishing.</strong> These terms should be consistent with the information shown during checkout.</p>

                <h2>7. Contact</h2>
                <p>To request a return or exchange, visit <a href="contact.php">Contact Us</a> and provide your order details.</p>
            </div>
        </article>
    </div>
</section>
<?php
include __DIR__ . "/../include/footer.php";
?>