
<?php
include("config/db.php");

$whyChooseQuery = mysqli_query(
    $conn,
    "SELECT * FROM why_choose ORDER BY id DESC"
);

include("include/header.php");
?>

<style>
.why-page {
    font-family: 'Manrope', sans-serif;
    color: #17243b;
}

/* HERO */
.why-hero {
    background: linear-gradient(120deg, #17243b, #263b5c);
    padding: 85px 20px;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.why-hero::before,
.why-hero::after {
    content: "";
    position: absolute;
    border-radius: 50%;
    background: rgba(230,83,141,.12);
}

.why-hero::before {
    width: 260px;
    height: 260px;
    top: -120px;
    left: -70px;
}

.why-hero::after {
    width: 220px;
    height: 220px;
    bottom: -130px;
    right: -50px;
}

.why-hero h1 {
    font-family: 'Outfit', sans-serif;
    font-size: 46px;
    color: #fff;
    font-weight: 800;
    margin: 0 0 15px;
    position: relative;
}

.why-hero h1 span {
    color: #f06b9f;
}

.why-hero p {
    color: #d5dce8;
    font-size: 14px;
    margin: 0;
    position: relative;
}

.why-breadcrumb {
    margin-top: 20px;
    color: #d5dce8;
    font-size: 13px;
    position: relative;
}

.why-breadcrumb a {
    color: #f06b9f;
    text-decoration: none;
}

/* INTRO */
.why-intro {
    text-align: center;
    max-width: 700px;
    margin: auto;
    padding: 80px 20px 45px;
}

.why-label {
    color: #e6538d;
    background: #fff0f6;
    padding: 9px 19px;
    border-radius: 30px;
    display: inline-block;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 2px;
    margin-bottom: 18px;
}

.why-intro h2 {
    font-family: 'Outfit', sans-serif;
    font-size: 37px;
    font-weight: 800;
    line-height: 1.25;
    margin: 0 0 15px;
}

.why-intro h2 span {
    color: #e6538d;
}

.why-intro p {
    color: #7b8494;
    font-size: 14px;
    line-height: 1.8;
    margin: 0;
}

/* CARDS */
.why-listing {
    background: #f9f7fa;
    padding: 35px 20px 90px;
}

.why-container {
    max-width: 1200px;
    margin: auto;
}

.why-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 27px;
}

.why-card {
    background: #fff;
    border-radius: 20px;
    overflow: hidden;
    border: 1px solid #f0eaf0;
    box-shadow: 0 8px 30px rgba(23,36,59,.045);
    transition: .35s ease;
}

.why-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 18px 40px rgba(23,36,59,.10);
}

.why-card-image {
    height: 240px;
    overflow: hidden;
    background: #f4edf2;
}

.why-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform .5s ease;
}

.why-card:hover .why-card-image img {
    transform: scale(1.07);
}

.why-card-body {
    padding: 26px;
}

.why-card-body h3 {
    font-family: 'Outfit', sans-serif;
    color: #17243b;
    font-size: 21px;
    font-weight: 700;
    margin: 0 0 13px;
}

.why-card-body p {
    color: #7b8494;
    font-size: 13px;
    line-height: 1.9;
    margin: 0;
}

.why-line {
    height: 3px;
    width: 42px;
    border-radius: 5px;
    background: #e6538d;
    margin-top: 22px;
    transition: .3s;
}

.why-card:hover .why-line {
    width: 75px;
}

/* EMPTY */
.why-empty {
    grid-column: 1 / -1;
    text-align: center;
    padding: 50px 20px;
    background: #fff;
    border-radius: 18px;
    color: #7b8494;
}

/* RESPONSIVE */
@media(max-width: 991px) {
    .why-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .why-hero h1 {
        font-size: 38px;
    }
}

@media(max-width: 576px) {
    .why-hero {
        padding: 65px 18px;
    }

    .why-hero h1 {
        font-size: 31px;
    }

    .why-intro {
        padding: 60px 18px 35px;
    }

    .why-intro h2 {
        font-size: 29px;
    }

    .why-listing {
        padding: 25px 16px 65px;
    }

    .why-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }

    .why-card-image {
        height: 220px;
    }
}
</style>

<div class="why-page">

    <!-- HERO -->
    <section class="why-hero">

        <h1>
            Why <span>Choose Us?</span>
        </h1>

        <p>
            Discover what makes us special for you and your beloved pets.
        </p>

        <div class="why-breadcrumb">
            <a href="index.php">Home</a>
            &nbsp; / &nbsp;
            Why Choose Us
        </div>

    </section>

    <!-- INTRO -->
    <section class="why-intro">

        <span class="why-label">WHY WE ARE DIFFERENT</span>

        <h2>
            Because Your Pet Deserves
            <span>The Best</span>
        </h2>

        <p>
            Your pets are part of your family, and they deserve nothing
            less than the best. We focus on quality, care and trust to
            make every experience special.
        </p>

    </section>

    <!-- DYNAMIC LISTING -->
    <section class="why-listing">

        <div class="why-container">

            <div class="why-grid">

                <?php if ($whyChooseQuery && mysqli_num_rows($whyChooseQuery) > 0): ?>

                    <?php while ($why = mysqli_fetch_assoc($whyChooseQuery)): ?>

                        <div class="why-card">

                            <div class="why-card-image">

                                <img
                                    src="images/<?= htmlspecialchars($why['image']); ?>"
                                    alt="<?= htmlspecialchars($why['title']); ?>"
                                    loading="lazy"
                                >

                            </div>

                            <div class="why-card-body">

                                <h3>
                                    <?= htmlspecialchars($why['title']); ?>
                                </h3>

                                <p>
                                    <?= nl2br(htmlspecialchars($why['description'])); ?>
                                </p>

                                <div class="why-line"></div>

                            </div>

                        </div>

                    <?php endwhile; ?>

                <?php else: ?>

                    <div class="why-empty">
                        <h3>No Information Available</h3>
                        <p>Our Why Choose Us details will be updated soon.</p>
                    </div>

                <?php endif; ?>

            </div>

        </div>

    </section>

</div>

<?php include("include/footer.php"); ?>
