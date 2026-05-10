<?php
session_start();
// If already logged in go to home
if (isset($_SESSION['user_id'])) {
    header("Location: pages/home.php");
    exit();
}
require_once 'includes/db.php';

// Fetch all products for display
$stmt = $pdo->prepare("
    SELECT p.*, c.name as cat_name, c.type as cat_type 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.stock > 0 
    ORDER BY p.id DESC
");
$stmt->execute();
$products = $stmt->fetchAll();

// Fetch categories
$cats = $pdo->query("SELECT * FROM categories ORDER BY type, name")->fetchAll();

// Filter by type
$type = $_GET['type'] ?? '';
$search = $_GET['search'] ?? '';

// Re-fetch with filters
$sql = "SELECT p.*, c.name as cat_name, c.type as cat_type 
        FROM products p 
        JOIN categories c ON p.category_id = c.id 
        WHERE p.stock > 0";
$params = [];
if ($type) { $sql .= " AND c.type = ?"; $params[] = $type; }
if ($search) { $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GreenLife — Plant Seeds & Small Plants</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f9fafb; }

        /* TOP BAR */
        .top-bar {
            background: #1b5e20;
            color: white;
            padding: 8px 20px;
            font-size: 13px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* NAVBAR */
        .navbar-main {
            background: white;
            padding: 12px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .brand-name {
            font-size: 28px;
            font-weight: 800;
            color: #1b5e20;
            text-decoration: none;
        }
        .brand-name span { color: #66bb6a; }
        .nav-actions { display: flex; gap: 12px; align-items: center; }
        .btn-register {
            background: #1b5e20;
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 15px;
            text-decoration: none;
            transition: all 0.3s;
        }
        .btn-register:hover {
            background: #2e7d32;
            color: white;
            transform: translateY(-2px);
        }
        .btn-login-link {
            color: #1b5e20;
            font-weight: 600;
            text-decoration: none;
            font-size: 15px;
            padding: 10px 20px;
            border: 2px solid #1b5e20;
            border-radius: 25px;
            transition: all 0.3s;
        }
        .btn-login-link:hover {
            background: #e8f5e9;
            color: #1b5e20;
        }

        /* SEARCH BAR */
        .search-bar {
            flex: 1;
            max-width: 500px;
            margin: 0 20px;
        }
        .search-bar input {
            border: 2px solid #e0e0e0;
            border-radius: 25px 0 0 25px;
            padding: 10px 20px;
            font-size: 14px;
            border-right: none;
        }
        .search-bar input:focus {
            outline: none;
            border-color: #2e7d32;
        }
        .search-bar button {
            background: #1b5e20;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 0 25px 25px 0;
            cursor: pointer;
        }

        /* HERO */
        .hero {
            background: linear-gradient(135deg, #1b5e20 0%, #2e7d32 50%, #388e3c 100%);
            color: white;
            padding: 80px 30px;
            text-align: center;
        }
        .hero h1 {
            font-size: 48px;
            font-weight: 800;
            margin-bottom: 15px;
        }
        .hero p {
            font-size: 20px;
            opacity: 0.9;
            margin-bottom: 30px;
        }
        .hero-btns { display: flex; gap: 15px; justify-content: center; flex-wrap: wrap; }
        .btn-hero-primary {
            background: white;
            color: #1b5e20;
            padding: 14px 35px;
            border-radius: 30px;
            font-weight: 700;
            font-size: 16px;
            text-decoration: none;
            transition: all 0.3s;
        }
        .btn-hero-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
            color: #1b5e20;
        }
        .btn-hero-secondary {
            background: transparent;
            color: white;
            padding: 14px 35px;
            border-radius: 30px;
            font-weight: 700;
            font-size: 16px;
            text-decoration: none;
            border: 2px solid white;
            transition: all 0.3s;
        }
        .btn-hero-secondary:hover {
            background: white;
            color: #1b5e20;
        }

        /* FEATURES STRIP */
        .features-strip {
            background: #f1f8e9;
            padding: 20px 30px;
            display: flex;
            justify-content: center;
            gap: 40px;
            flex-wrap: wrap;
            border-bottom: 2px solid #c8e6c9;
        }
        .feature-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            color: #1b5e20;
            font-weight: 600;
        }
        .feature-item i { font-size: 22px; }

        /* TABS */
        .type-tabs {
            background: white;
            padding: 20px 30px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            border-bottom: 1px solid #e0e0e0;
            position: sticky;
            top: 65px;
            z-index: 99;
        }
        .type-tab {
            padding: 8px 20px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            border: 2px solid #e0e0e0;
            color: #666;
            transition: all 0.2s;
        }
        .type-tab:hover, .type-tab.active {
            background: #1b5e20;
            color: white;
            border-color: #1b5e20;
        }

        /* PRODUCT SECTION */
        .products-section {
            padding: 30px;
        }
        .section-title {
            font-size: 26px;
            font-weight: 700;
            color: #1b5e20;
            margin-bottom: 5px;
        }
        .section-subtitle {
            color: #777;
            font-size: 14px;
            margin-bottom: 25px;
        }

        /* PRODUCT CARD */
        .product-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 2px 15px rgba(0,0,0,0.07);
            transition: all 0.3s;
            height: 100%;
            position: relative;
        }
        .product-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 8px 30px rgba(27,94,32,0.15);
        }
        .product-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            object-position: center;
        }
        .product-img-placeholder {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, #e8f5e9, #c8e6c9);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 60px;
        }
        .product-body { padding: 15px; }
        .cat-badge {
            background: #e8f5e9;
            color: #2e7d32;
            font-size: 11px;
            padding: 3px 10px;
            border-radius: 20px;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 8px;
        }
        .product-name {
            font-size: 16px;
            font-weight: 700;
            color: #1b5e20;
            margin-bottom: 8px;
        }
        .product-price {
            font-size: 20px;
            font-weight: 800;
            color: #e65100;
            margin-bottom: 6px;
        }
        .product-stock {
            font-size: 12px;
            color: #888;
            margin-bottom: 12px;
        }
        .btn-add-cart {
            width: 100%;
            background: #1b5e20;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: block;
            text-align: center;
            transition: all 0.2s;
        }
        .btn-add-cart:hover {
            background: #2e7d32;
            color: white;
        }

        /* LOGIN MODAL OVERLAY */
        .login-required-overlay {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
        }
        .login-required-overlay.show { display: flex; }
        .login-popup {
            background: white;
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            max-width: 380px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .login-popup .icon { font-size: 60px; margin-bottom: 15px; }
        .login-popup h3 { color: #1b5e20; font-weight: 700; margin-bottom: 10px; }
        .login-popup p { color: #666; font-size: 14px; margin-bottom: 25px; }
        .popup-btns { display: flex; gap: 10px; flex-direction: column; }

        /* FOOTER */
        .footer {
            background: #1b5e20;
            color: white;
            padding: 40px 30px 20px;
            margin-top: 60px;
        }
        .footer h5 { font-weight: 700; margin-bottom: 15px; color: #a5d6a7; }
        .footer a { color: #c8e6c9; text-decoration: none; font-size: 14px; display: block; margin-bottom: 6px; }
        .footer a:hover { color: white; }
        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.2);
            padding-top: 20px;
            margin-top: 30px;
            text-align: center;
            font-size: 13px;
            color: #a5d6a7;
        }

        /* SHOWING COUNT */
        .result-count {
            background: #e8f5e9;
            color: #1b5e20;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .hero h1 { font-size: 30px; }
            .features-strip { gap: 15px; }
            .search-bar { max-width: 100%; margin: 10px 0; order: 3; width: 100%; }
            .navbar-main { flex-wrap: wrap; }
        }
    </style>
</head>
<body>

<!-- TOP BAR -->
<div class="top-bar">
    <span><i class="bi bi-truck"></i> Free delivery on orders above ₹499 | Delivering across Karnataka</span>
    <span><i class="bi bi-telephone"></i> Support: 9100800552</span>
</div>

<!-- NAVBAR -->
<nav class="navbar-main">
    <a href="landing.php" class="brand-name">🌱 Green<span>Life</span></a>

    <!-- Search -->
    <form class="search-bar d-flex" method="GET" action="landing.php">
        <?php if($type): ?>
            <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">
        <?php endif; ?>
        <input type="text" name="search" class="form-control"
               placeholder="Search seeds, plants..."
               value="<?= htmlspecialchars($search) ?>">
        <button type="submit"><i class="bi bi-search"></i></button>
    </form>

    <!-- Nav Actions -->
    <div class="nav-actions">
        <a href="index.php?from=landing" class="btn-login-link"><i class="bi bi-person"></i> Login</a>
        <a href="register.php" class="btn-register"><i class="bi bi-person-plus"></i> Register</a>
    </div>
</nav>

<?php if (!$type && !$search): ?>
<!-- HERO -->
<div class="hero">
    <div style="font-size:60px; margin-bottom:15px;">🌱</div>
    <h1>Grow Your Green Dream</h1>
    <p>Premium quality seeds and small plants delivered to your doorstep across Karnataka</p>
    <div class="hero-btns">
        <a href="#products" class="btn-hero-primary">🛒 Shop Now</a>
        <a href="register.php" class="btn-hero-secondary">✨ Join Free</a>
    </div>
</div>

<!-- FEATURES STRIP -->
<div class="features-strip">
    <div class="feature-item"><i class="bi bi-truck"></i> Free Delivery Above ₹499</div>
    <div class="feature-item"><i class="bi bi-shield-check"></i> 100% Organic Seeds</div>
    <div class="feature-item"><i class="bi bi-star-fill"></i> Verified Quality</div>
    <div class="feature-item"><i class="bi bi-geo-alt"></i> Karnataka Only</div>
    <div class="feature-item"><i class="bi bi-arrow-repeat"></i> Easy Returns</div>
</div>
<?php endif; ?>

<!-- TYPE TABS -->
<div class="type-tabs">
    <a href="landing.php" class="type-tab <?= (!$type) ? 'active' : '' ?>">🌿 All</a>
    <a href="landing.php?type=flowers" class="type-tab <?= ($type=='flowers') ? 'active' : '' ?>">🌸 Flowers</a>
    <a href="landing.php?type=vegetables" class="type-tab <?= ($type=='vegetables') ? 'active' : '' ?>">🥦 Vegetables</a>
    <a href="landing.php?type=fruits" class="type-tab <?= ($type=='fruits') ? 'active' : '' ?>">🍎 Fruits</a>
    <a href="landing.php?type=plants" class="type-tab <?= ($type=='plants') ? 'active' : '' ?>">🪴 Plants</a>
</div>

<!-- PRODUCTS SECTION -->
<div class="products-section" id="products">
    <h2 class="section-title">
        <?php
        if ($search) echo 'Search Results for "' . htmlspecialchars($search) . '"';
        elseif ($type == 'flowers') echo '🌸 Flower Seeds';
        elseif ($type == 'vegetables') echo '🥦 Vegetable Seeds';
        elseif ($type == 'fruits') echo '🍎 Fruit Seeds';
        elseif ($type == 'plants') echo '🪴 Small Plants';
        else echo '🌱 All Products';
        ?>
    </h2>
    <p class="section-subtitle">Browse and add to cart — Register or Login to purchase</p>
    <div class="result-count">
        Showing <?= count($products) ?> product<?= count($products) != 1 ? 's' : '' ?>
    </div>

    <div class="row g-4">
        <?php if (empty($products)): ?>
            <div class="col-12 text-center py-5">
                <div style="font-size:60px;">🌱</div>
                <h4 style="color:#1b5e20;">No products found</h4>
                <p style="color:#888;">Try a different category or search term</p>
                <a href="landing.php" class="btn-add-cart" style="width:auto; display:inline-block; padding:10px 30px;">Browse All</a>
            </div>
        <?php else: ?>
            <?php foreach ($products as $p):
                $icon = '🌱';
                if ($p['cat_type'] == 'flowers') $icon = '🌸';
                elseif ($p['cat_type'] == 'vegetables') $icon = '🥦';
                elseif ($p['cat_type'] == 'fruits') $icon = '🍎';
                elseif ($p['cat_type'] == 'plants') $icon = '🪴';

                $imgPath = "assets/images/products/" . $p['image'];
                $hasImg = $p['image'] && file_exists($imgPath);
            ?>
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="product-card">
                    <?php if ($hasImg): ?>
                        <img src="<?= htmlspecialchars($imgPath) ?>"
                             alt="<?= htmlspecialchars($p['name']) ?>"
                             class="product-img">
                    <?php else: ?>
                        <div class="product-img-placeholder"><?= $icon ?></div>
                    <?php endif; ?>
                    <div class="product-body">
                        <span class="cat-badge"><?= htmlspecialchars($p['cat_name']) ?></span>
                        <div class="product-name"><?= htmlspecialchars($p['name']) ?></div>
                        <div class="product-price">₹<?= number_format($p['price'], 2) ?></div>
                        <div class="product-stock">
                            <i class="bi bi-box-seam"></i>
                            <?= $p['stock'] ?> packets available
                        </div>
                        <!-- Clicking this shows login popup -->
                        <a href="javascript:void(0)" onclick="showLoginPopup()"
                           class="btn-add-cart">
                            <i class="bi bi-cart-plus"></i> Add to Cart
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- LOGIN REQUIRED POPUP -->
<div class="login-required-overlay" id="loginOverlay">
    <div class="login-popup">
        <div class="icon">🔐</div>
        <h3>Login Required!</h3>
        <p>Please register or login to add items to your cart and place orders.</p>
        <div class="popup-btns">
            <a href="register.php" class="btn-add-cart" style="padding:12px;">
                <i class="bi bi-person-plus"></i> Register Now — It's Free!
            </a>
            <a href="index.php?from=landing" class="btn-add-cart"style="background:#2e7d32; padding:12px;">
    <i class="bi bi-box-arrow-in-right"></i> Login</a>
            <button onclick="hideLoginPopup()"
                    style="background:none; border:none; color:#888;
                           font-size:14px; cursor:pointer; margin-top:5px;">
                Continue Browsing
            </button>
        </div>
    </div>
</div>

<!-- FOOTER -->
<footer class="footer">
    <div class="row">
        <div class="col-md-4 mb-4">
            <h5>🌱 GreenLife</h5>
            <p style="color:#c8e6c9; font-size:14px;">
                Premium quality seeds and small plants delivered across Karnataka.
                Grow your green dream with us!
            </p>
        </div>
        <div class="col-md-2 mb-4">
            <h5>Quick Links</h5>
            <a href="landing.php">Home</a>
            <a href="landing.php?type=flowers">Flowers</a>
            <a href="landing.php?type=vegetables">Vegetables</a>
            <a href="landing.php?type=fruits">Fruits</a>
            <a href="landing.php?type=plants">Plants</a>
        </div>
        <div class="col-md-3 mb-4">
            <h5>Account</h5>
            <a href="register.php">Register</a>
           <a href="index.php?from=landing">Login</a>
        </div>
        <div class="col-md-3 mb-4">
            <h5>Contact Us</h5>
            <p style="color:#c8e6c9; font-size:14px;">
                <i class="bi bi-telephone"></i> 9100800552<br>
                <i class="bi bi-envelope"></i> support@greenlife.com<br>
                <i class="bi bi-geo-alt"></i> Karnataka, India
            </p>
        </div>
    </div>
    <div class="footer-bottom">
        © 2026 GreenLife. All rights reserved. | Delivering Green Happiness Across Karnataka 🌱
    </div>
</footer>

<script>
function showLoginPopup() {
    document.getElementById('loginOverlay').classList.add('show');
}
function hideLoginPopup() {
    document.getElementById('loginOverlay').classList.remove('show');
}
// Close popup on outside click
document.getElementById('loginOverlay').addEventListener('click', function(e) {
    if (e.target === this) hideLoginPopup();
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>