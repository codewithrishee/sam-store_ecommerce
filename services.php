<?php
include "config/db.php";
include "include/header.php"
?>

<link rel="stylesheet" href="style.css">
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

<style>
/* ==========================================================
   SERVICES PAGE — PREMIUM STYLES (Navy + Pink Theme)
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

/* ================= SERVICES SECTION ================= */
.services-section {
    background: #fff;
    padding: 6rem 0;
}

/* Section Heading */
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
    margin-bottom: 20px; /* Align left on desktop */
}

/* Premium Service Cards */
.service-card {
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 24px;
    padding: 40px 30px;
    height: 100%;
    transition: all .4s var(--ease);
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.service-card::before {
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

.service-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(46, 50, 84, 0.08);
    border-color: rgba(255, 77, 109, .2);
}

.service-card:hover::before {
    opacity: 1;
}

/* Service Image (Circle) */
.service-card img {
    width: 80px;
    height: 80px;
    object-fit: contain;
    background: var(--brand-soft);
    padding: 18px;
    border-radius: 50%;
    margin: 0 auto 24px auto;
    transition: transform .4s var(--ease);
    display: block;
}

.service-card:hover img {
    transform: scale(1.1) rotate(-5deg);
}

/* Card Content */
.service-card h5 {
    font-family: 'Outfit', sans-serif;
    color: var(--ink);
    font-weight: 800;
    font-size: 1.25rem;
    margin-bottom: 12px;
    text-align: center;
}

.service-card p {
    color: var(--muted);
    font-size: .92rem;
    line-height: 1.6;
    text-align: center;
    flex-grow: 1; /* Pushes the link to the bottom */
    margin-bottom: 20px;
}

/* Read More Link */
.service-card a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-family: 'Outfit', sans-serif;
    font-weight: 800;
    font-size: .85rem;
    letter-spacing: .05em;
    text-transform: uppercase;
    color: var(--brand);
    text-decoration: none;
    margin-top: auto; /* Pushes to bottom */
    transition: color .3s, gap .3s;
}

.service-card a::after {
    content: '\f061'; /* FontAwesome arrow-right */
    font-family: 'Font Awesome 6 Free';
    font-weight: 900;
    font-size: .8rem;
    transition: transform .3s var(--ease);
}

.service-card a:hover {
    color: var(--brand-2);
}

.service-card a:hover::after {
    transform: translateX(5px);
}

/* ================= RESPONSIVE ================= */
@media (max-width: 991.98px) {
    .about-banner { padding: 100px 0 80px; }
    .pet-img { right: 2%; opacity: .8; }
    .bone-img { display: none; }
    .services-section { padding: 4rem 0; }
    
    .underline {
        margin: 0 auto 20px; /* Center on mobile */
    }
    
    .d-flex.justify-content-between {
        justify-content: center !important;
        text-align: center;
    }
}

@media (max-width: 767.98px) {
    .about-banner { padding: 80px 0 60px; }
    .pet-img { display: none; }
    .banner-content h1 { font-size: 2rem; }
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
    <h1>Services / What we Do</h1>
    <p>
      <a href="index.php">Home</a> 
      <span><i class="fa-solid fa-chevron-right" style="font-size: .6rem;"></i></span> 
      <b>Services / What we Do</b>
    </p>
  </div>

  <!-- <div class="pet-img">
    <img src="images/11.png" class="img-fluid" alt="Pet Image">
  </div>

  <div class="bone-img">
    <img src="images/bone.png" alt="Bone Image">
  </div> -->
</section>

<!-- ================= SERVICES SECTION ================= -->
<section class="services-section">
  <div class="container">

    <div class="d-flex justify-content-between align-items-center mb-5 flex-wrap">
      <div>
        <h2 class="fw-bold service-heading">Our Services</h2>
        <div class="underline"></div>
      </div>
    </div>

    <div class="row g-4 justify-content-center">

<?php
$services = mysqli_query($conn,"SELECT * FROM services WHERE status=1");

if(mysqli_num_rows($services) > 0){
    while($row = mysqli_fetch_assoc($services)){
?>

<div class="col-md-6 col-lg-4 col-xl-3">
  <div class="service-card">
    <img src="images/<?= htmlspecialchars($row['image']); ?>" alt="<?= htmlspecialchars($row['title']); ?>">
    <h5><?= htmlspecialchars($row['title']); ?></h5>
    <p><?= htmlspecialchars($row['description']); ?></p>
    <a href="<?= htmlspecialchars($row['link']); ?>">Read More</a>
  </div>
</div>

<?php 
    } 
} else {
    echo '<div class="col-12 text-center"><h5 class="text-muted">No services available at the moment.</h5></div>';
}
?>

    </div>
  </div>
</section>

<?php
include "include/footer.php"
?>