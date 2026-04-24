<?php
$base   = '../';
$active = 'home';
require_once '../includes/session.php';
require_once '../includes/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - GreenLife</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<?php require_once '../includes/header.php'; ?>

<div class="container mt-4">

    <!-- Welcome Bar -->
    <div style="background:#e8f5e9; border-left:4px solid #2e7d32;
                padding:10px 20px; border-radius:8px; color:#1b5e20;
                font-weight:500; margin-bottom:20px;">
        👋 Welcome back, <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong>!
        Start exploring our plant seeds collection. 🌿
    </div>

    <!-- HERO SECTION -->
    <div class="hero-section mb-5">
        <h1>🌿 Grow Your Own Green World</h1>
        <p class="mb-4">
            Premium quality seeds for flowers, vegetables and fruits
            — delivered across Karnataka
        </p>
        <a href="products.php" class="btn-hero">🛒 Shop Now</a>
    </div>

    <!-- CATEGORIES -->
    <h2 class="section-title mb-4">Browse by Category</h2>
    <div class="row g-4 mb-5">
        <div class="col-md-4 col-12">
            <a href="products.php?type=flowers" class="category-card">
                <div class="category-icon">🌸</div>
                <h5>Flowering Plants</h5>
                <p>Winter, Summer, Rainy season — Indoor & Outdoor</p>
            </a>
        </div>
        <div class="col-md-4 col-12">
            <a href="products.php?type=vegetables" class="category-card">
                <div class="category-icon">🥦</div>
                <h5>Vegetables</h5>
                <p>Leafy greens, plant vegetables, creepers — seasonal</p>
            </a>
        </div>
        <div class="col-md-4 col-12">
            <a href="products.php?type=fruits" class="category-card">
                <div class="category-icon">🍎</div>
                <h5>Fruits</h5>
                <p>Seasonal and variety fruit seeds for your garden</p>
            </a>
        </div>
    </div>

    <!-- WHY CHOOSE US -->
    <h2 class="section-title mb-4">Why Choose GreenLife?</h2>
    <div class="row g-4 mb-5">
        <div class="col-md-3 col-6">
            <div class="feature-card">
                <div class="feature-icon mb-2">🌱</div>
                <h6 style="color:#1b5e20;">Premium Seeds</h6>
                <small class="text-muted">High germination rate guaranteed</small>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="feature-card">
                <div class="feature-icon mb-2">🚚</div>
                <h6 style="color:#1b5e20;">Fast Delivery</h6>
                <small class="text-muted">Delivered within 7 days in Karnataka</small>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="feature-card">
                <div class="feature-icon mb-2">💰</div>
                <h6 style="color:#1b5e20;">10% Discount</h6>
                <small class="text-muted">Order 10+ seed packets and save more</small>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="feature-card">
                <div class="feature-icon mb-2">📞</div>
                <h6 style="color:#1b5e20;">24/7 Support</h6>
                <small class="text-muted">Helpline: +91 98765 43210</small>
            </div>
        </div>
    </div>

</div>

<?php require_once '../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<style>
    .hero-section {
        background: linear-gradient(135deg, #1b5e20 0%, #388e3c 50%, #66bb6a 100%);
        color: white; padding: 80px 20px; text-align: center;
        border-radius: 0 0 30px 30px;
    }
    .hero-section h1 { font-size: 42px; font-weight: 700; }
    @media (max-width: 576px) {
        .hero-section { padding: 40px 15px; }
        .hero-section h1 { font-size: 24px; }
    }
    .btn-hero {
        background: white; color: #1b5e20; font-weight: 600;
        padding: 12px 30px; border-radius: 25px; border: none;
        font-size: 16px; text-decoration: none;
    }
    .btn-hero:hover { background: #f1f8e9; color: #1b5e20; }
    .category-card {
        background: white; border-radius: 15px; padding: 30px 20px;
        text-align: center; box-shadow: 0 3px 15px rgba(0,0,0,0.08);
        transition: transform 0.2s; text-decoration: none;
        color: inherit; display: block;
        border-bottom: 4px solid transparent;
    }
    .category-card:hover {
        transform: translateY(-5px);
        border-bottom: 4px solid #66bb6a; color: inherit;
    }
    .category-icon { font-size: 50px; margin-bottom: 10px; }
    .category-card h5 { color: #1b5e20; font-weight: 600; }
    .category-card p { color: #777; font-size: 13px; }
    .feature-card {
        background: white; border-radius: 12px; padding: 25px;
        text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    }
    .feature-icon { font-size: 36px; color: #2e7d32; }
</style>
</body>
</html>