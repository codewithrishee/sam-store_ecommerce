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
        <h1>Refund Policy</h1>
        <p>Understand how refunds are reviewed and processed for purchases from SamPets.</p>
        <div class="last-updated-badge"><i class="fa-solid fa-clock-rotate-left"></i> Last updated: November 2026</div>
    </div>
</section>

<section class="policy-page">
    <div class="container">
        <article class="policy-card">
            <div class="policy-content">
                <p>Thank you for shopping with SamPets. We want you to have a clear and fair experience if an order has a problem. Please read this policy together with our Return &amp; Exchange, Shipping and Cancellation policies.</p>
                
                <div class="policy-note">
                    <strong>Before publishing:</strong> Add your actual refund eligibility rules, time limits, refund method and processing timeline. Do not advertise a refund period until you can consistently honour it.
                </div>

                <h2>1. When a refund may be considered</h2>
                <p>You may contact us if an item arrives damaged, defective, incorrect, missing, or if another issue covered by the applicable product or order terms occurs. Refund approval depends on reviewing the order and the information provided.</p>

                <h2>2. How to request a refund</h2>
                <ol>
                    <li>Contact us using the details on our Contact Us page.</li>
                    <li>Share your order number and the email or phone number used at checkout.</li>
                    <li>Explain the issue and provide clear photographs or other supporting information when relevant.</li>
                    <li>Keep the product and its packaging until our support team responds.</li>
                </ol>

                <h2>3. Review and decision</h2>
                <p>Our team may review order details, product condition and supporting information before deciding whether a refund, replacement or another resolution is appropriate. We will communicate the decision and any next steps.</p>

                <h2>4. Refund method and timing</h2>
                <p>Approved refunds will be issued using the original payment method where possible, or another method agreed with the customer. The time taken for the amount to appear can depend on the payment provider or bank. <strong>Insert your confirmed refund processing timeframe here before publishing.</strong></p>

                <h2>5. Non-refundable situations</h2>
                <p>Any exclusions must be clearly disclosed before purchase and must comply with applicable consumer-protection law. Do not use this section to remove rights that customers have under law.</p>

                <h2>6. Contact</h2>
                <p>For refund questions, contact SamPets through <a href="contact.php">our Contact Us page</a> and include your order number.</p>
            </div>
        </article>
    </div>
</section>


<?php
include __DIR__ . "/../include/footer.php";
?>