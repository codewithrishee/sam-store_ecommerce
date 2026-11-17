<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ecommerce Footer</title>

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link
    href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700;800;900&display=swap"
    rel="stylesheet">

  <style>
    /* ==================================================================
   FOOTER — tokens (same as header/site)
   ================================================================== */
    :root {
      --ink: #0f172a;
      --ink-2: #1a2338;
      --ink-3: #0b1220;
      --brand: #ff5a3c;
      --brand-2: #ff2d55;
      --brand-glow: rgba(255, 90, 60, .35);
      --cream: #fbf7f2;
      --muted-lite: rgba(255, 255, 255, .55);
      --line: rgba(255, 255, 255, .08);
      --ease: cubic-bezier(.2, .8, .2, 1);
    }

    /* ==================================================================
   MAIN FOOTER SHELL
   ================================================================== */
    .main-footer {
      position: relative;
      background:
        radial-gradient(110% 90% at 0% 0%, rgba(255, 90, 60, .14) 0%, transparent 50%),
        radial-gradient(90% 70% at 100% 100%, rgba(14, 165, 164, .10) 0%, transparent 55%),
        linear-gradient(180deg, var(--ink-3) 0%, #0a111e 100%);
      color: #fff;
      font-family: 'Manrope', system-ui, -apple-system, 'Segoe UI', sans-serif;
      overflow: hidden;
      margin-top: 60px;
    }

    /* top gradient brand line */
    .main-footer::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 3px;
      background: linear-gradient(90deg, transparent 0%, var(--brand) 25%, var(--brand-2) 50%, var(--brand) 75%, transparent 100%);
      opacity: .9;
    }

    /* ==================================================================
   HEADINGS
   ================================================================== */
    .footer-heading {
      position: relative;
      font-family: 'Outfit', sans-serif;
      font-weight: 800;
      font-size: .92rem;
      letter-spacing: .12em;
      text-transform: uppercase;
      color: #fff;
      margin-bottom: 22px;
      padding-bottom: 12px;
    }

    .footer-heading::after {
      content: '';
      position: absolute;
      left: 0;
      bottom: 0;
      width: 34px;
      height: 3px;
      border-radius: 99px;
      background: linear-gradient(90deg, var(--brand), var(--brand-2));
    }

    /* ==================================================================
   CONTACT COLUMN
   ================================================================== */
    .main-footer .col-md-3>p {
      font-size: .84rem;
      line-height: 1.7;
      color: var(--muted-lite);
      margin-bottom: 12px;
    }

    .footer-email {
      color: rgba(255, 255, 255, .9);
      text-decoration: none;
      font-weight: 600;
      border-bottom: 1px dashed rgba(255, 90, 60, .5);
      padding-bottom: 1px;
      transition: color .3s var(--ease), border-color .3s var(--ease);
    }

    .footer-email:hover {
      color: var(--brand);
      border-color: var(--brand);
    }

    /* phone chip */
    .main-footer .d-flex.align-items-center.mt-3 {
      margin-top: 18px !important;
      padding: 14px 16px;
      border-radius: 14px;
      background: rgba(255, 255, 255, .04);
      border: 1px solid var(--line);
      transition: background .3s var(--ease), border-color .3s var(--ease);
    }

    .main-footer .d-flex.align-items-center.mt-3:hover {
      background: rgba(255, 90, 60, .08);
      border-color: rgba(255, 90, 60, .25);
    }

    .footer-icon {
      font-size: 1.15rem;
      width: 44px;
      height: 44px;
      display: grid;
      place-items: center;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--brand), var(--brand-2));
      color: #fff;
      box-shadow: 0 8px 22px var(--brand-glow);
      flex: 0 0 auto;
    }

    .main-footer .d-flex.align-items-center.mt-3 span {
      display: block;
      font-size: .68rem;
      letter-spacing: .12em;
      text-transform: uppercase;
      color: rgba(255, 255, 255, .5);
      font-weight: 700;
      margin-bottom: 2px;
    }

    .footer-phone {
      font-family: 'Outfit', sans-serif;
      font-weight: 800;
      font-size: .95rem;
      color: #fff !important;
      margin: 0;
      letter-spacing: .01em;
    }

    /* ==================================================================
   LINK LISTS
   ================================================================== */
    .footer-links {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .footer-links li {
      margin-bottom: 10px;
    }

    .footer-links a {
      position: relative;
      display: inline-block;
      color: var(--muted-lite);
      text-decoration: none;
      font-size: .86rem;
      font-weight: 500;
      padding-left: 0;
      transition: color .3s var(--ease), padding-left .3s var(--ease);
    }

    .footer-links a::before {
      content: '\203A';
      position: absolute;
      left: -12px;
      top: 50%;
      transform: translateY(-50%) scale(0);
      font-family: 'Outfit', sans-serif;
      font-size: .95rem;
      font-weight: 700;
      color: var(--brand);
      opacity: 0;
      transition: opacity .3s var(--ease), transform .3s var(--ease);
    }

    .footer-links a:hover {
      color: #fff;
      padding-left: 10px;
    }

    .footer-links a:hover::before {
      opacity: 1;
      transform: translateY(-50%) scale(1);
    }

    /* ==================================================================
   NEWSLETTER COLUMN
   ================================================================== */
    .main-footer .col-md-3>p:first-of-type {
      font-size: .84rem;
      line-height: 1.7;
      color: var(--muted-lite);
      margin-bottom: 18px;
    }

    /* NEWSLETTER pill */
    .newsletter-box {
      display: flex;
      align-items: center;
      background: #fff;
      border-radius: 999px;
      padding: 5px;
      box-shadow: 0 10px 26px rgba(0, 0, 0, .28);
      transition: box-shadow .35s var(--ease), transform .35s var(--ease);
      position: relative;
      overflow: hidden;
    }

    .newsletter-box:focus-within {
      transform: translateY(-2px);
      box-shadow: 0 16px 34px rgba(255, 90, 60, .28), 0 0 0 3px rgba(255, 90, 60, .18);
    }

    .newsletter-box input {
      flex: 1 1 auto;
      border: none !important;
      background: transparent !important;
      padding: 10px 16px !important;
      font-family: 'Manrope', sans-serif;
      font-size: .84rem;
      color: var(--ink);
      border-radius: 999px !important;
      width: auto !important;
      min-width: 0;
      outline: none !important;
      box-shadow: none !important;
    }

    .newsletter-box input::placeholder {
      color: #8b92a2;
      font-weight: 500;
    }

    .newsletter-box button {
      flex: 0 0 auto;
      border: none;
      background: linear-gradient(135deg, var(--brand), var(--brand-2));
      color: #fff;
      font-family: 'Outfit', sans-serif;
      font-size: .74rem;
      font-weight: 800;
      letter-spacing: .08em;
      text-transform: uppercase;
      padding: 11px 22px;
      border-radius: 999px;
      cursor: pointer;
      box-shadow: 0 6px 16px rgba(255, 45, 85, .32);
      transition: transform .3s var(--ease), box-shadow .3s var(--ease), filter .3s;
      position: relative;
      overflow: hidden;
    }

    .newsletter-box button::before {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg, transparent 30%, rgba(255, 255, 255, .3) 50%, transparent 70%);
      transform: translateX(-120%);
      transition: transform .7s var(--ease);
    }

    .newsletter-box button:hover::before {
      transform: translateX(120%);
    }

    .newsletter-box button:hover {
      transform: translateY(-1px);
      filter: brightness(1.06);
      box-shadow: 0 10px 24px rgba(255, 45, 85, .42);
    }

    .newsletter-box button:active {
      transform: scale(.97);
    }

    /* ==================================================================
   SOCIAL ICONS
   ================================================================== */
    .social-icons {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-top: 20px !important;
    }

    .social-icons i {
      margin: 0 !important;
      width: 38px;
      height: 38px;
      display: grid;
      place-items: center;
      border-radius: 50%;
      background: rgba(255, 255, 255, .06);
      border: 1px solid var(--line);
      color: rgba(255, 255, 255, .75);
      font-size: .82rem;
      cursor: pointer;
      transition: all .35s var(--ease);
    }

    .social-icons i:hover {
      background: linear-gradient(135deg, var(--brand), var(--brand-2));
      border-color: transparent;
      color: #fff;
      transform: translateY(-3px) scale(1.05);
      box-shadow: 0 10px 22px var(--brand-glow);
    }

    /* individual brand hover accent (optional polish) */
    .social-icons i.fa-facebook-f:hover {
      background: linear-gradient(135deg, #1877f2, #0b5fce);
      box-shadow: 0 10px 22px rgba(24, 119, 242, .35);
    }

    .social-icons i.fa-twitter:hover {
      background: linear-gradient(135deg, #0ea5e9, #0284c7);
      box-shadow: 0 10px 22px rgba(14, 165, 233, .35);
    }

    .social-icons i.fa-linkedin-in:hover {
      background: linear-gradient(135deg, #0a66c2, #004182);
      box-shadow: 0 10px 22px rgba(10, 102, 194, .35);
    }

    .social-icons i.fa-pinterest-p:hover {
      background: linear-gradient(135deg, #e60023, #ad081b);
      box-shadow: 0 10px 22px rgba(230, 0, 35, .35);
    }

    /* ==================================================================
   DIVIDER
   ================================================================== */
    .footer-line {
      border: 0 !important;
      height: 1px !important;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .18) 20%, rgba(255, 255, 255, .18) 80%, transparent) !important;
      opacity: 1 !important;
      margin: 0;
    }

    /* ==================================================================
   COPYRIGHT BAR
   ================================================================== */
    .main-footer .container.py-2 {
      padding-top: 20px !important;
      padding-bottom: 22px !important;
    }

    .main-footer .container.py-2 p {
      font-family: 'Outfit', sans-serif;
      font-size: .78rem;
      font-weight: 500;
      letter-spacing: .04em;
      color: rgba(255, 255, 255, .55);
      text-align: center;
    }

    .main-footer .container.py-2 p b,
    .main-footer .container.py-2 p strong {
      color: var(--brand);
      font-weight: 800;
    }

    /* ==================================================================
   RESPONSIVE
   ================================================================== */
    @media (max-width: 991.98px) {
      .main-footer .col-md-3 {
        margin-bottom: 30px;
      }

      .footer-heading {
        margin-bottom: 16px;
      }
    }

    @media (max-width: 575.98px) {
      .main-footer {
        margin-top: 30px;
      }

      .main-footer .col-md-3 {
        margin-bottom: 26px;
        text-align: center;
      }

      .footer-heading::after {
        left: 50%;
        transform: translateX(-50%);
      }

      .main-footer .d-flex.align-items-center.mt-3 {
        justify-content: center;
        text-align: left;
      }

      .newsletter-box {
        max-width: 340px;
        margin-left: auto;
        margin-right: auto;
      }

      .newsletter-box button {
        padding: 10px 16px;
        font-size: .68rem;
      }

      .social-icons {
        justify-content: center;
      }

      .footer-links a:hover {
        padding-left: 0;
      }

      .footer-links a::before {
        display: none;
      }

      .footer-links a {
        color: rgba(255, 255, 255, .7);
      }

      .footer-links a:hover {
        color: var(--brand);
      }
    }


    /* ==========================================
   FOOTER RESPONSIVE CSS
========================================== */

    /* Tablet */
    @media (max-width: 991.98px) {

      .main-footer {
        margin-top: 40px;
      }

      .main-footer>.container {
        padding-left: 24px;
        padding-right: 24px;
      }

      .main-footer .row {
        row-gap: 25px;
      }

      .main-footer .col-md-3 {
        width: 50%;
        margin-bottom: 10px;
      }

      .footer-heading {
        font-size: 0.88rem;
        margin-bottom: 16px;
      }

      .main-footer .col-md-3>p {
        font-size: 0.82rem;
      }

      .newsletter-box {
        width: 100%;
      }

      .newsletter-box input {
        min-width: 0;
      }

      .newsletter-box button {
        padding: 10px 16px;
      }
    }

    /* Mobile */
    @media (max-width: 767.98px) {

      .main-footer {
        margin-top: 30px;
      }

      .main-footer>.container {
        padding-left: 18px;
        padding-right: 18px;
      }

      .main-footer .row {
        row-gap: 22px;
      }

      .main-footer .col-md-3 {
        width: 100%;
        margin-bottom: 5px;
        text-align: left;
      }

      .footer-heading {
        font-size: 0.9rem;
        margin-bottom: 15px;
        padding-bottom: 10px;
      }

      .footer-heading::after {
        left: 0;
        transform: none;
      }

      .main-footer .col-md-3>p {
        font-size: 0.82rem;
        line-height: 1.7;
      }

      .main-footer .d-flex.align-items-center.mt-3 {
        justify-content: flex-start;
        padding: 12px;
        gap: 12px;
      }

      .footer-icon {
        width: 40px;
        height: 40px;
        font-size: 1rem;
      }

      .footer-phone {
        font-size: 0.88rem;
      }

      .footer-links li {
        margin-bottom: 9px;
      }

      .footer-links a {
        font-size: 0.84rem;
      }

      .newsletter-box {
        width: 100%;
        max-width: 100%;
        margin: 0;
      }

      .newsletter-box input {
        padding: 10px 12px !important;
        font-size: 0.78rem;
      }

      .newsletter-box button {
        padding: 10px 14px;
        font-size: 0.65rem;
        white-space: nowrap;
      }

      .social-icons {
        justify-content: flex-start;
        gap: 10px;
      }

      .social-icons i {
        width: 36px;
        height: 36px;
      }

      .main-footer .container.py-2 {
        padding: 16px 15px !important;
      }

      .main-footer .container.py-2 p {
        font-size: 0.7rem;
        line-height: 1.6;
        margin-bottom: 0;
      }
    }

    /* Small Mobile */
    @media (max-width: 380px) {

      .main-footer>.container {
        padding-left: 14px;
        padding-right: 14px;
      }

      .newsletter-box {
        padding: 4px;
      }

      .newsletter-box input {
        padding: 9px 8px !important;
        font-size: 0.72rem;
      }

      .newsletter-box button {
        padding: 9px 11px;
        font-size: 0.6rem;
      }

      .footer-heading {
        font-size: 0.84rem;
      }

      .footer-links a {
        font-size: 0.8rem;
      }
    }

    .footer-logo {
      width: 220px;
      max-width: 100%;
      height: auto;
      object-fit: contain;
      display: block;
      margin-bottom: 22px;
    }
  </style>
</head>

<body>

  <!-- ==================== FOOTER ==================== -->


  <footer class="main-footer">

    <div class="container py-5">
      <div class="row g-4">

        <!-- CONTACT US -->
        <div class="col-lg-3 col-md-6">

          <a href="index.php">
            <img src="images/logo.png" alt="SAM'S STORE" class="footer-logo">
          </a>

          <h5 class="footer-heading">Contact Us</h5>

          <p>
            <i class="fa-solid fa-location-dot me-2" style="color:#ff5a3c;"></i>
            123 Pet Care Street, Mumbai, India
          </p>

          <p>
            <i class="fa-solid fa-envelope me-2" style="color:#ff5a3c;"></i>
            <a href="mailto:sampets@support.com" class="footer-email">sampets@support.com</a>
          </p>

          <div class="d-flex align-items-center mt-3">
            <i class="fa-solid fa-headset footer-icon"></i>
            <div class="ms-3">
              <span>Call To Order</span>
              <h6 class="footer-phone">+91 12345 66789</h6>
            </div>
          </div>
        </div>

        <!-- QUICK LINKS -->
        <div class="col-lg-2 col-md-6">
          <h5 class="footer-heading">Quick Links</h5>
          <ul class="footer-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="about.php">About Us</a></li>
            <li><a href="services.php">Services</a></li>
            <li><a href="shop.php">Shop</a></li>
            <li><a href="why-us.php">Why-Us</a></li>
            <li><a href="blog.php">Blog</a></li>
          </ul>
        </div>

        <!-- USEFUL LINKS -->
        <div class="col-lg-2 col-md-6">
          <h5 class="footer-heading">Useful Links</h5>
          <ul class="footer-links">
            <li><a href="wishlist.php">Wishlist</a></li>
            <li><a href="cart.php">Cart</a></li>
            <li><a href="contact.php">Contact Us</a></li>
            <li><a href="all-policies/faq.php">FAQs</a></li>
          </ul>
        </div>


        <!-- POLICIES -->
        <div class="col-lg-2 col-md-6">
          <h5 class="footer-heading">Our Policies</h5>

          <ul class="footer-links">
            <li><a href="/project3/all-policies/privacy-policy.php">Privacy Policy</a></li>
            <li><a href="/project3/all-policies/refund-policy.php">Refund Policy</a></li>
            <li><a href="/project3/all-policies/return-policy.php">Return &amp; Exchange</a></li>
            <li><a href="/project3/all-policies/shipping-policy.php">Shipping Policy</a></li>
            <li><a href="/project3/all-policies/terms.php">Terms &amp; Conditions</a></li>
            <li><a href="/project3/all-policies/cancellation-policy.php">Cancellation Policy</a></li>
          </ul>
        </div>


        <!-- NEWSLETTER -->
        <div class="col-lg-3 col-md-12">
          <h5 class="footer-heading">Our Newsletter</h5>

          <p>
            Subscribe to the weekly newsletter for all the latest updates
            &amp; get <b style="color:#ff5a3c;">10% off</b> your bill.
          </p>

          <div class="newsletter-box">
            <input type="email" placeholder="Your email address" required>
            <button type="button">Subscribe</button>
          </div>

          <div class="social-icons">
            <i class="fab fa-facebook-f" title="Facebook"></i>
            <i class="fab fa-twitter" title="Twitter"></i>
            <i class="fab fa-linkedin-in" title="LinkedIn"></i>
            <i class="fab fa-pinterest-p" title="Pinterest"></i>
          </div>
        </div>

      </div>
    </div>

    <hr class="footer-line">

    <div class="container py-2 d-flex justify-content-center flex-wrap">
      <p class="mb-0">
        <b>SamStore</b> © 2026 Demo Store. All Rights Reserved.
      </p>
    </div>

  </footer>



  <!-- jQuery First -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

  <!-- Owl Carousel JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>


  <!--cat-->
  <script>
    $(document).on('click', '.cat-filter', function (e) {
      e.preventDefault();
      let cat_id = $(this).data('cat');

      $.ajax({
        url: 'fetch_products.php',
        type: 'POST',
        data: {
          cat_id: cat_id
        },
        success: function (res) {
          $('#product-container').html(res);
        }
      });
    });

    $(document).on('click', '.sub-filter', function (e) {
      e.preventDefault();
      let cat_id = $(this).data('cat');
      let sub_id = $(this).data('sub');

      console.log('Sending CAT:', cat_id, 'SUB:', sub_id);

      $.ajax({
        url: 'fetch_products.php',
        type: 'POST',
        data: {
          cat_id: cat_id,
          sub_id: sub_id
        },
        success: function (res) {
          console.log('Response:', res);
          $('#product-container').html(res);
        },
        error: function (xhr, status, error) {
          console.log('AJAX Error:', error);
        }
      });
    });
  </script>


  <!--ajax wishlist--->
  <script>

    document.addEventListener("DOMContentLoaded", function () {
      document.querySelectorAll(".addWishlist").forEach(button => {
        button.addEventListener("click", function (e) {
          e.preventDefault();
          let productId = this.getAttribute("data-id");
          fetch("add-to-wishlist.php", {
            method: "POST",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            body: "id=" + productId
          })
            .then(response => response.text())
            .then(data => {
              alert("Added To Wishlist ");
            });
        });
      });
    });

  </script>


  <!--heart wishlist (icon)--->
  <script>
    document.querySelectorAll(".wishlist-btn").forEach(btn => {
      btn.addEventListener("click", function () {
        let product_id = this.getAttribute("data-id");
        fetch("add_to_wishlist.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          body: "product_id=" + product_id
        })
          .then(res => res.text())
          .then(data => {
            alert(data);
          });
      });
    });
  </script>

</body>

</html>