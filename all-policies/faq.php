<?php
include __DIR__ . "/../include/header.php";
?>

<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

<style>
/* Universal Policy CSS + FAQ Specific */
:root { --ink: #2e3254; --ink-2: #252a4a; --ink-3: #1f2340; --brand: #ff4d6d; --brand-2: #e63950; --brand-soft: #fff2f5; --line: #efe6ec; --muted: #8a8fa3; --ease: cubic-bezier(.2, .8, .2, 1); }
body { font-family: 'Manrope', system-ui, sans-serif; background: #faf7f9; }
.legal-hero { position: relative; padding: 80px 0 70px; background: linear-gradient(135deg, var(--ink-3) 0%, var(--ink-2) 100%); overflow: hidden; border-bottom: 1px solid rgba(255,255,255,.05); }
.legal-hero::before { content: ''; position: absolute; width: 500px; height: 500px; background: radial-gradient(circle, rgba(255,77,109,.15) 0%, transparent 70%); top: -150px; left: -150px; }
.hero-content { position: relative; z-index: 2; text-align: center; }
.hero-content h1 { font-family: 'Outfit', sans-serif; font-weight: 900; color: #fff; font-size: clamp(2rem, 4vw, 3rem); margin-bottom: 15px; text-shadow: 0 10px 30px rgba(0,0,0,.3); }
.hero-content p { color: rgba(255,255,255,.7); font-size: 1rem; }

.faq-page { padding: 60px 0 80px; margin-top: -40px; position: relative; z-index: 10; }
.faq-wrap { max-width: 950px; margin: auto; }
.faq-list { display: grid; gap: 16px; }
.faq-item { background: #fff; border: 1px solid var(--line); border-radius: 16px; padding: 24px 28px; box-shadow: 0 5px 18px rgba(46,50,84,.03); transition: all .3s var(--ease); position: relative; overflow: hidden; }
.faq-item::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 4px; background: var(--brand); opacity: 0; transition: opacity .3s; }
.faq-item:hover { transform: translateY(-3px); box-shadow: 0 12px 25px rgba(46,50,84,.08); border-color: rgba(255,77,109,.2); }
.faq-item:hover::before { opacity: 1; }
.faq-item h2 { font-family: 'Outfit', sans-serif; font-size: 1.1rem; font-weight: 800; color: var(--ink); margin: 0 0 10px; display: flex; align-items: flex-start; gap: 12px; }
.faq-item h2 i { color: var(--brand); font-size: 1rem; margin-top: 3px; }
.faq-item p { color: #56607a; line-height: 1.7; margin: 0; font-size: .95rem; padding-left: 28px; }

.faq-contact { text-align: center; margin-top: 40px; padding: 40px; background: #fff; border-radius: 20px; border: 1px solid var(--line); box-shadow: 0 10px 30px rgba(46,50,84,.05); }
.faq-contact h3 { font-family: 'Outfit', sans-serif; font-weight: 800; color: var(--ink); font-size: 1.4rem; margin-bottom: 8px; }
.faq-contact p { color: var(--muted); margin-bottom: 20px; }
.faq-contact a { display: inline-flex; align-items: center; gap: 8px; padding: 12px 28px; border-radius: 999px; background: linear-gradient(135deg, var(--brand), var(--brand-2)); color: #fff; text-decoration: none; font-family: 'Outfit', sans-serif; font-weight: 800; font-size: .9rem; letter-spacing: .05em; text-transform: uppercase; box-shadow: 0 10px 24px rgba(255,77,109,.3); transition: all .3s var(--ease); }
.faq-contact a:hover { transform: translateY(-3px); box-shadow: 0 14px 32px rgba(255,77,109,.4); color: #fff; filter: brightness(1.05); }

@media(max-width: 767.98px) { .legal-hero { padding: 60px 0 50px; } .faq-page { padding: 30px 0 50px; margin-top: -20px; } .faq-item { padding: 20px; } .faq-item h2 { font-size: 1rem; } .faq-item p { padding-left: 0; margin-top: 8px; } .faq-contact { padding: 30px 20px; } }
</style>

<section class="legal-hero">
    <div class="container hero-content">
        <h1>Frequently Asked Questions</h1>
        <p>Quick answers to common questions about shopping with SamPets.</p>
    </div>
</section>

<section class="faq-page">
    <div class="container">
        <div class="faq-wrap">
            <div class="faq-list">
                <section class="faq-item">
                    <h2><i class="fa-solid fa-circle-question"></i> 1. How can I place an order?</h2>
                    <p>Browse the shop, open the product you want, add it to your cart and follow the checkout instructions.</p>
                </section>
                <section class="faq-item">
                    <h2><i class="fa-solid fa-circle-question"></i> 2. How can I contact customer support?</h2>
                    <p>Visit our Contact Us page and submit your name, email, subject and message. Include your order number if your question is about an order.</p>
                </section>
                <section class="faq-item">
                    <h2><i class="fa-solid fa-circle-question"></i> 3. How can I check my order status?</h2>
                    <p>Use the order-tracking facility if it is available on the website, or contact our support team with your order number.</p>
                </section>
                <section class="faq-item">
                    <h2><i class="fa-solid fa-circle-question"></i> 4. Can I cancel my order?</h2>
                    <p>Cancellation depends on the current order status. Please contact us as soon as possible and review our Cancellation Policy.</p>
                </section>
                <section class="faq-item">
                    <h2><i class="fa-solid fa-circle-question"></i> 5. Can I return or exchange a product?</h2>
                    <p>Return and exchange requests are reviewed under our Return &amp; Exchange Policy and applicable consumer rights. Contact us before sending an item back.</p>
                </section>
                <section class="faq-item">
                    <h2><i class="fa-solid fa-circle-question"></i> 6. How are refunds processed?</h2>
                    <p>Approved refunds are generally sent to the original payment method where possible. The time taken to reflect may depend on the payment provider or bank. See our Refund Policy.</p>
                </section>
                <section class="faq-item">
                    <h2><i class="fa-solid fa-circle-question"></i> 7. How much time does delivery take?</h2>
                    <p>Delivery estimates and any applicable shipping charges should be shown during checkout or in your order confirmation. See our Shipping Policy for details.</p>
                </section>
                <section class="faq-item">
                    <h2><i class="fa-solid fa-circle-question"></i> 8. What should I do if my order arrives damaged or incorrect?</h2>
                    <p>Contact us promptly with your order number and clear photographs of the parcel and item so our team can review the issue.</p>
                </section>
                <section class="faq-item">
                    <h2><i class="fa-solid fa-circle-question"></i> 9. Is my personal information safe?</h2>
                    <p>We take reasonable steps to protect information submitted through our website. Read our Privacy Policy to understand how information is handled.</p>
                </section>
            </div>
            
            <div class="faq-contact">
                <h3>Still have questions?</h3>
                <p>Our team is ready to help with your enquiry.</p>
                <a href="contact.php">Contact Us <i class="fa-solid fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
</section>
<?php
include __DIR__ . "/../include/footer.php";
?>