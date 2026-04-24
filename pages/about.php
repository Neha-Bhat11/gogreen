<?php
$base   = '../';
$active = 'about';
require_once '../includes/session.php';
require_once '../includes/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - GreenLife</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .hero-about {
            background: linear-gradient(135deg, #1b5e20 0%, #388e3c 50%, #66bb6a 100%);
            color: white; padding: 60px 20px; text-align: center;
            border-radius: 0 0 30px 30px;
        }
        @media (max-width:576px) {
            .hero-about { padding: 35px 15px; }
            .hero-about h1 { font-size: 24px; }
        }
        .info-card {
            background: white; border-radius: 15px; padding: 30px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.08); height: 100%;
            border-bottom: 4px solid #66bb6a;
        }
        .info-icon { font-size: 40px; margin-bottom: 15px; }
        .stat-box {
            background: #1b5e20; color: white;
            border-radius: 15px; padding: 30px; text-align: center;
        }
        .stat-box h2 { font-size: 40px; font-weight: 700; color: #a5d6a7; }
        .mission-box {
            background: #e8f5e9; border-left: 5px solid #2e7d32;
            border-radius: 10px; padding: 25px 30px;
        }
    </style>
</head>
<body>

<?php require_once '../includes/header.php'; ?>

<!-- HERO -->
<div class="hero-about mb-5">
    <h1 style="font-weight:700;">🌍 About GreenLife</h1>
    <p style="font-size:18px; opacity:0.9;">
        We exist because clean air is not a luxury — it's a right.
    </p>
</div>

<div class="container">

    <!-- OUR STORY -->
    <div class="mission-box mb-5">
        <h3 style="color:#1b5e20; font-weight:700;">🌱 Why We Started GreenLife</h3>
        <p style="font-size:16px; line-height:1.9; color:#333; margin-top:15px;">
            We live in a time where breathing clean air has become a challenge.
            Pollution levels are rising every year, and our cities are losing
            their green cover rapidly. The simplest and most powerful solution
            to this crisis is also the most ancient one —
            <strong>planting trees.</strong>
        </p>
        <p style="font-size:16px; line-height:1.9; color:#333;">
            GreenLife was created with one mission: to make it easy for every
            household in Karnataka to grow their own plants. We provide
            high-quality seeds of <strong>flowering plants, vegetables,
            medicinal plants, and fruit trees</strong> — all available
            right at your doorstep.
        </p>
        <p style="font-size:16px; line-height:1.9; color:#333;">
            Whether you want to grow a rose in your balcony, tomatoes in
            your backyard, or a neem tree in your garden —
            GreenLife has the seed for you.
        </p>
    </div>

    <!-- WHY TREES MATTER -->
    <h2 class="section-title mb-4">🌳 Why Trees & Plants Matter</h2>
    <div class="row g-4 mb-5">
        <div class="col-md-4 col-sm-6">
            <div class="info-card">
                <div class="info-icon">💨</div>
                <h5 style="color:#1b5e20;">Purify the Air</h5>
                <p style="color:#555;">Trees absorb CO₂ and release oxygen.
                A single mature tree can absorb around 22 kg of carbon dioxide
                per year and produce enough oxygen for 2 people daily.</p>
            </div>
        </div>
        <div class="col-md-4 col-sm-6">
            <div class="info-card">
                <div class="info-icon">🌡️</div>
                <h5 style="color:#1b5e20;">Reduce Heat</h5>
                <p style="color:#555;">Urban areas with more trees can be
                2–8°C cooler. Trees provide shade, reduce surface temperatures
                and lower your home's electricity bill naturally.</p>
            </div>
        </div>
        <div class="col-md-4 col-sm-6">
            <div class="info-card">
                <div class="info-icon">🥗</div>
                <h5 style="color:#1b5e20;">Grow Your Own Food</h5>
                <p style="color:#555;">Growing your own vegetables ensures
                fresh, chemical-free food for your family. Even a small
                balcony can produce tomatoes, spinach, coriander and more.</p>
            </div>
        </div>
        <div class="col-md-4 col-sm-6">
            <div class="info-card">
                <div class="info-icon">🧘</div>
                <h5 style="color:#1b5e20;">Mental Wellbeing</h5>
                <p style="color:#555;">Studies show that being around plants
                reduces stress, anxiety and improves mood. A green environment
                at home creates peace and positivity.</p>
            </div>
        </div>
        <div class="col-md-4 col-sm-6">
            <div class="info-card">
                <div class="info-icon">💧</div>
                <h5 style="color:#1b5e20;">Save Water & Soil</h5>
                <p style="color:#555;">Plant roots hold soil together and
                prevent erosion. Trees also help recharge groundwater and
                maintain the water cycle in your local area.</p>
            </div>
        </div>
        <div class="col-md-4 col-sm-6">
            <div class="info-card">
                <div class="info-icon">🦋</div>
                <h5 style="color:#1b5e20;">Support Biodiversity</h5>
                <p style="color:#555;">Flowering plants attract bees,
                butterflies and birds. Every plant you grow at home
                contributes to a healthier local ecosystem.</p>
            </div>
        </div>
    </div>

    <!-- STATS -->
    <div class="row g-4 mb-5">
        <div class="col-md-3 col-6">
            <div class="stat-box">
                <h2>50+</h2>
                <p>Seed Varieties</p>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-box">
                <h2>30</h2>
                <p>Districts in Karnataka</p>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-box">
                <h2>7</h2>
                <p>Days Delivery</p>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-box">
                <h2>100%</h2>
                <p>Natural Seeds</p>
            </div>
        </div>
    </div>

    <!-- WHAT WE OFFER -->
    <h2 class="section-title mb-4">🌿 What We Offer</h2>
    <div class="row g-3 mb-5">
        <div class="col-md-6">
            <ul style="list-style:none; padding:0;">
                <li class="mb-2">🌸 <strong>Flowering Plants</strong>
                    — Winter, Summer, Rainy, Indoor, Outdoor</li>
                <li class="mb-2">🥦 <strong>Vegetable Seeds</strong>
                    — Leafy, Creepers, Seasonal varieties</li>
                <li class="mb-2">🍋 <strong>Fruit Seeds</strong>
                    — Seasonal and exotic varieties</li>
                <li class="mb-2">🌿 <strong>Medicinal Plants</strong>
                    — Tulsi, Neem, Aloe Vera and more</li>
            </ul>
        </div>
        <div class="col-md-6">
            <ul style="list-style:none; padding:0;">
                <li class="mb-2">🚚 <strong>Delivery within Karnataka</strong>
                    in 7 working days</li>
                <li class="mb-2">💰 <strong>10% discount</strong>
                    on orders of 10+ seed packets</li>
                <li class="mb-2">💳 <strong>Secure online payment</strong>
                    only</li>
                <li class="mb-2">📞 <strong>Helpline support</strong>
                    for all your queries</li>
            </ul>
        </div>
    </div>

</div>

<?php require_once '../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>