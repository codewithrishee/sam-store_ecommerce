<?php
include "include/header.php"
?>
<link rel="stylesheet" href="style.css">
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

<style>
    /* ==========================================================
       ABOUT PAGE — PREMIUM STYLES (Navy + Pink Theme)
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
    }

    /* ================= BANNER ================= */
    .about-banner {
        position: relative;
        padding: 120px 0 100px;
        overflow: hidden;
        background: linear-gradient(135deg, var(--ink-3) 0%, var(--ink-2) 100%);
        border-bottom: 1px solid rgba(255, 255, 255, .05);
    }

    /* Decorative Glows */
    .about-banner::before {
        content: '';
        position: absolute;
        width: 500px;
        height: 500px;
        background: radial-gradient(circle, rgba(255, 77, 109, .15) 0%, transparent 70%);
        top: -150px;
        left: -150px;
        pointer-events: none;
    }

    .about-banner::after {
        content: '';
        position: absolute;
        width: 600px;
        height: 600px;
        background: radial-gradient(circle, rgba(255, 255, 255, .05) 0%, transparent 70%);
        bottom: -200px;
        right: -200px;
        pointer-events: none;
    }

    /* Banner Text */
    .banner-content {
        position: relative;
        z-index: 2;
    }

    .banner-content h1 {
        font-family: 'Outfit', sans-serif;
        font-weight: 900;
        color: #fff;
        font-size: clamp(2.2rem, 5vw, 3.5rem);
        letter-spacing: -.03em;
        margin-bottom: 12px;
        text-shadow: 0 10px 30px rgba(0, 0, 0, .3);
    }

    .banner-content p {
        font-family: 'Outfit', sans-serif;
        font-size: .85rem;
        font-weight: 600;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, .6);
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }

    .banner-content a {
        color: rgba(255, 255, 255, .8);
        text-decoration: none;
        transition: color .3s;
    }

    .banner-content a:hover {
        color: var(--brand);
    }

    .banner-content b {
        color: var(--brand);
        font-weight: 800;
    }

    /* Floating Images */
    .pet-img {
        position: absolute;
        right: 5%;
        bottom: 0;
        z-index: 2;
        animation: floatY 4s ease-in-out infinite;
    }

    .pet-img img {
        height: clamp(200px, 30vw, 320px);
        max-width: 100%;
        filter: drop-shadow(0 20px 40px rgba(0, 0, 0, .4));
    }

    .bone-img {
        position: absolute;
        left: 5%;
        bottom: 20px;
        z-index: 2;
        animation: floatY 5s ease-in-out infinite reverse;
    }

    .bone-img img {
        height: 120px;
        width: auto;
        opacity: .8;
        filter: drop-shadow(0 10px 20px rgba(0, 0, 0, .2));
    }

    @keyframes floatY {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-15px); }
    }

    /* ================= ABOUT SECTION ================= */
    .about-section {
        background: #fff;
        padding: 6rem 0;
        position: relative;
    }

    .about-img {
        border-radius: 24px;
        box-shadow: 0 20px 40px rgba(46, 50, 84, 0.12);
        transition: transform .5s var(--ease);
    }

    .about-img:hover {
        transform: translateY(-5px) scale(1.01);
    }

    .about-title {
        font-family: 'Outfit', sans-serif;
        color: var(--ink);
        font-weight: 900;
        font-size: clamp(1.6rem, 3vw, 2.2rem);
        letter-spacing: -.02em;
        margin-bottom: 20px;
        position: relative;
        padding-bottom: 15px;
    }

    .about-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 60px;
        height: 4px;
        background: linear-gradient(90deg, var(--brand), #ffb3bf);
        border-radius: 99px;
    }

    .about-section p {
        color: #56607a;
        font-size: 1.05rem;
        line-height: 1.7;
    }

    /* List Items */
    .about-list {
        list-style: none;
        padding: 0;
        margin-top: 25px;
    }

    .about-list li {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
        font-family: 'Outfit', sans-serif;
        font-size: .95rem;
        font-weight: 600;
        color: var(--ink);
    }

    .about-list i {
        width: 28px;
        height: 28px;
        display: grid;
        place-items: center;
        background: var(--brand-soft);
        color: var(--brand);
        border-radius: 50%;
        font-size: .9rem;
        box-shadow: 0 4px 10px rgba(255, 77, 109, .15);
    }

    /* Explore Button */
    .about-btn {
        background: linear-gradient(135deg, var(--brand), var(--brand-2));
        color: #fff;
        border: none;
        border-radius: 999px;
        padding: 14px 36px;
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        font-size: .88rem;
        letter-spacing: .05em;
        text-transform: uppercase;
        box-shadow: 0 10px 24px rgba(255, 77, 109, .3);
        transition: all .35s var(--ease);
        margin-top: 30px;
    }

    .about-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 16px 32px rgba(255, 77, 109, .4);
        color: #fff;
        filter: brightness(1.05);
    }

    /* ================= WHY CHOOSE US ================= */
    .why-choose {
        background: #faf7f9; /* Soft pink tint */
        padding: 6rem 0;
        border-top: 1px solid var(--line);
        border-bottom: 1px solid var(--line);
    }

    .service-heading {
        font-family: 'Outfit', sans-serif;
        color: var(--ink);
        font-size: clamp(1.6rem, 3vw, 2.2rem);
        font-weight: 900;
        letter-spacing: -.02em;
        margin-bottom: 10px;
    }

    .underline {
        width: 60px;
        height: 4px;
        background: linear-gradient(90deg, var(--brand), #ffb3bf);
        border-radius: 99px;
        margin: 0 auto 20px;
    }

    /* Premium Cards */
    .choose-card {
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 24px;
        padding: 40px 24px;
        height: 100%;
        transition: all .4s var(--ease);
        position: relative;
        overflow: hidden;
    }

    .choose-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(90deg, var(--brand), var(--brand-2));
        opacity: 0;
        transition: opacity .3s;
    }

    .choose-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(46, 50, 84, 0.08);
        border-color: rgba(255, 77, 109, .2);
    }

    .choose-card:hover::before {
        opacity: 1;
    }

    .choose-card img {
        background: var(--brand-soft);
        padding: 16px;
        border-radius: 50%;
        margin-bottom: 20px;
        transition: transform .4s var(--ease);
        object-fit: contain;
    }

    .choose-card:hover img {
        transform: scale(1.1) rotate(-5deg);
    }

    .choose-card h5 {
        font-family: 'Outfit', sans-serif;
        color: var(--ink);
        font-weight: 800;
        font-size: 1.15rem;
        margin-bottom: 12px;
    }

    .choose-card p {
        color: var(--muted);
        font-size: .9rem;
        line-height: 1.6;
        margin: 0;
    }

    /* ================= RESPONSIVE ================= */
    @media (max-width: 991.98px) {
        .about-banner { padding: 100px 0 80px; }
        .pet-img { right: 2%; opacity: .8; }
        .bone-img { display: none; }
        .about-section { padding: 4rem 0; }
        
        .about-title::after {
            left: 50%;
            transform: translateX(-50%);
        }
        .about-section .row { flex-direction: column-reverse; text-align: center; }
        .about-list li { justify-content: center; }
        .about-img { margin-top: 30px; margin-bottom: 30px; }
    }

    @media (max-width: 767.98px) {
        .about-banner { padding: 80px 0 60px; }
        .pet-img { display: none; /* Hide pet image on small screens to keep it clean */ }
        .banner-content h1 { font-size: 2rem; }
        .why-choose { padding: 4rem 0; }
    }
</style>

<!--sticky boxes-->
<div class="floating-btns">
    <div class="floating-btns">
        <div class="box">
            <img src="images/buy.png" alt="">
            <span class="text-white">Buy Now</span>
        </div>
        <div class="box2">
            <img src="images/23.png" alt="">
            <span class="text-white">Support</span>
        </div>
    </div>
</div>

<!-- ================= BANNER ================= -->
<section class="about-banner">
    <div class="container text-center banner-content">
        <h1>About Us</h1>
        <p>
            <a href="/project3/">Home</a>
            <span><i class="fa-solid fa-chevron-right" style="font-size: .6rem;"></i></span>
            <b>About Us</b>
        </p>
    </div>

    <!-- <div class="pet-img">
        <img src="images/11.png" class="img-fluid" alt="Pet Image">
    </div>

    <div class="bone-img">
        <img src="images/bone.png" alt="Bone Image">
    </div> -->
</section>

<!-- ================= ABOUT ================= -->
<section class="about-section">
    <div class="container">
        <div class="row align-items-center">

            <div class="col-md-6">
                <img src="images/vij.png" class="img-fluid about-img w-100" alt="About SAM Pets">
            </div>

            <div class="col-md-6 ps-md-5 mt-4 mt-md-0">
                <h3 class="about-title">Welcome to SAM PETS STORE</h3>

                <p class="mt-3">
                    SAM PETS STORE is your one-stop destination for high-quality, stylish and comfortable pet
                    accessories.
                    Our mission is to add joy and comfort to every pet’s life.
                </p>

                <ul class="about-list mt-4">
                    <li><i class="fa-solid fa-check"></i> High Quality Pet Products</li>
                    <li><i class="fa-solid fa-check"></i> Trusted by Thousands</li>
                    <li><i class="fa-solid fa-check"></i> Affordable Prices</li>
                    <li><i class="fa-solid fa-check"></i> Fast & Safe Delivery</li>
                </ul>

                <a href="/project3/shop" class="btn about-btn">Explore Products</a>
            </div>

        </div>
    </div>
</section>

<!-- ================= WHY US ================= -->
<section class="why-choose">
    <div class="container text-center">
        <div class="d-flex flex-column align-items-center mb-5">
            <h2 class="service-heading">Why Choose Us</h2>
            <div class="underline"></div>
        </div>
        
        <div class="row g-4">
            <?php
            // Make sure $conn is available (included in header)
            $res = mysqli_query($conn, "SELECT * FROM why_choose WHERE status=1");
            while ($row = mysqli_fetch_assoc($res)) {
                ?>
                <div class="col-lg-3 col-md-6">
                    <div class="choose-card text-center">
                        <img src="images/<?= $row['image']; ?>" width="60" alt="<?= htmlspecialchars($row['title']); ?>">
                        <h5><?php echo htmlspecialchars($row['title']); ?></h5>
                        <p><?php echo htmlspecialchars($row['description']); ?></p>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</section>

<?php
include "include/footer.php"
?>