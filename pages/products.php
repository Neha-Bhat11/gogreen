<?php
$base   = '../';
$active = 'products';
require_once '../includes/session.php';
require_once '../includes/db.php';

// Get filter from URL
$type   = isset($_GET['type'])   ? $_GET['type']   : '';
$cat_id = isset($_GET['cat'])    ? $_GET['cat']    : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query
$query  = "SELECT p.*, c.name as category_name, c.type as category_type
           FROM products p
           JOIN categories c ON p.category_id = c.id
           WHERE 1=1";
$params = [];

if ($type) {
    $query .= " AND c.type = ?";
    $params[] = $type;
}
if ($cat_id) {
    $query .= " AND p.category_id = ?";
    $params[] = $cat_id;
}
if ($search) {
    $query .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " ORDER BY p.id DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get all categories for sidebar
$cats = $pdo->query("SELECT * FROM categories ORDER BY type, name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - GreenLife</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* SIDEBAR */
        .sidebar-filter {
            background: white; border-radius: 15px;
            padding: 20px; box-shadow: 0 3px 15px rgba(0,0,0,0.08);
        }
        .sidebar-filter h6 {
            color: #1b5e20; font-weight: 700;
            border-bottom: 2px solid #e8f5e9;
            padding-bottom: 8px; margin-bottom: 12px;
        }
        .filter-link {
            display: block; padding: 7px 10px;
            border-radius: 8px; color: #444;
            text-decoration: none; font-size: 14px; margin-bottom: 3px;
        }
        .filter-link:hover, .filter-link.active {
            background: #e8f5e9; color: #1b5e20; font-weight: 600;
        }

        /* PRODUCT CARD */
        .product-card {
            background: white; border-radius: 15px; overflow: hidden;
            box-shadow: 0 3px 15px rgba(0,0,0,0.08);
            transition: transform 0.2s, box-shadow 0.2s; height: 100%;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,128,0,0.15);
        }
        .product-img-placeholder {
            width: 100%; height: 180px;
            background: linear-gradient(135deg, #e8f5e9, #c8e6c9);
            display: flex; align-items: center;
            justify-content: center; font-size: 60px;
        }
        .product-body { padding: 15px; }
        .product-name { color: #1b5e20; font-weight: 600; font-size: 15px; }
        .product-price { color: #e65100; font-weight: 700; font-size: 18px; }
        .product-cat {
            background: #e8f5e9; color: #2e7d32; font-size: 11px;
            padding: 3px 8px; border-radius: 20px;
            display: inline-block; margin-bottom: 8px;
        }
        .btn-add-cart {
            background: #2e7d32; color: white; border: none;
            border-radius: 8px; padding: 7px 12px;
            font-size: 13px; width: 100%; margin-top: 8px;
            cursor: pointer;
        }
        .btn-add-cart:hover { background: #1b5e20; color: white; }
        .btn-wishlist {
            background: #fff; color: #e53935;
            border: 1px solid #e53935; border-radius: 8px;
            padding: 7px 12px; font-size: 13px;
            width: 100%; margin-top: 5px; cursor: pointer;
        }
        .btn-wishlist:hover { background: #ffebee; }

        /* TYPE TABS */
        .type-tab {
            background: white; border: 2px solid #e8f5e9;
            color: #1b5e20; border-radius: 25px; padding: 8px 20px;
            text-decoration: none; font-weight: 500; font-size: 14px;
        }
        .type-tab:hover, .type-tab.active {
            background: #1b5e20; color: white; border-color: #1b5e20;
        }

        /* Mobile sidebar hidden by default */
        @media (max-width: 991px) {
            .sidebar-filter { margin-bottom: 20px; }
        }
        @media (max-width: 576px) {
            .product-img-placeholder { height: 130px; font-size: 45px; }
            .product-name { font-size: 13px; }
            .product-price { font-size: 15px; }
            .btn-add-cart, .btn-wishlist { font-size: 11px; padding: 5px 8px; }
        }
    </style>
</head>
<body>

<?php require_once '../includes/header.php'; ?>

<div class="container mt-4">

    <!-- SEARCH BAR -->
    <form method="GET" action="products.php" class="mb-4">
        <div class="input-group">
            <input type="text" name="search" class="form-control form-control-lg"
                   placeholder="🔍 Search seeds..."
                   value="<?= htmlspecialchars($search) ?>">
            <?php if ($type): ?>
                <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">
            <?php endif; ?>
            <button class="btn btn-success" type="submit">Search</button>
            <?php if ($search || $type || $cat_id): ?>
                <a href="products.php" class="btn btn-outline-secondary">Clear</a>
            <?php endif; ?>
        </div>
    </form>

    <!-- TYPE TABS -->
    <div class="d-flex gap-2 flex-wrap mb-4">
        <a href="products.php"
           class="type-tab <?= (!$type) ? 'active' : '' ?>">🌿 All</a>
        <a href="products.php?type=flowers"
           class="type-tab <?= ($type=='flowers') ? 'active' : '' ?>">🌸 Flowers</a>
        <a href="products.php?type=vegetables"
           class="type-tab <?= ($type=='vegetables') ? 'active' : '' ?>">🥦 Vegetables</a>
        <a href="products.php?type=fruits"
           class="type-tab <?= ($type=='fruits') ? 'active' : '' ?>">🍎 Fruits</a>
    </div>

    <div class="row">
        <!-- SIDEBAR -->
        <div class="col-lg-3 mb-4">
            <div class="sidebar-filter">
                <h6>🌸 Flowers</h6>
                <?php foreach ($cats as $cat): ?>
                    <?php if ($cat['type'] == 'flowers'): ?>
                        <a href="products.php?cat=<?= $cat['id'] ?>"
                           class="filter-link <?= ($cat_id==$cat['id']) ? 'active':'' ?>">
                            › <?= htmlspecialchars($cat['name']) ?>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>

                <h6 class="mt-3">🥦 Vegetables</h6>
                <?php foreach ($cats as $cat): ?>
                    <?php if ($cat['type'] == 'vegetables'): ?>
                        <a href="products.php?cat=<?= $cat['id'] ?>"
                           class="filter-link <?= ($cat_id==$cat['id']) ? 'active':'' ?>">
                            › <?= htmlspecialchars($cat['name']) ?>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>

                <h6 class="mt-3">🍎 Fruits</h6>
                <?php foreach ($cats as $cat): ?>
                    <?php if ($cat['type'] == 'fruits'): ?>
                        <a href="products.php?cat=<?= $cat['id'] ?>"
                           class="filter-link <?= ($cat_id==$cat['id']) ? 'active':'' ?>">
                            › <?= htmlspecialchars($cat['name']) ?>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- PRODUCTS GRID -->
        <div class="col-lg-9">
            <?php if (empty($products)): ?>
                <div class="text-center py-5">
                    <div style="font-size:60px;">🌱</div>
                    <h4 style="color:#1b5e20;">No products found</h4>
                    <a href="products.php" class="btn btn-success mt-2">
                        View All Products
                    </a>
                </div>
            <?php else: ?>
                <p style="color:#777; font-size:14px;" class="mb-3">
                    Showing <strong><?= count($products) ?></strong> products
                    <?= $search ? "for \"".htmlspecialchars($search)."\"" : '' ?>
                </p>
                <div class="row g-3">
                    <?php foreach ($products as $p):
                        $icon = '🌱';
                        if ($p['category_type'] == 'flowers')    $icon = '🌸';
                        elseif ($p['category_type'] == 'vegetables') $icon = '🥦';
                        elseif ($p['category_type'] == 'fruits')  $icon = '🍎';
                    ?>
                    <div class="col-md-4 col-6">
                        <div class="product-card">
                            <a href="product_detail.php?id=<?= $p['id'] ?>">
                                <div class="product-img-placeholder">
                                    <?php
                                    $imgPath = '../assets/images/products/' . $p['image'];
                                    if ($p['image'] && file_exists($imgPath)) {
                                        echo '<img src="'.$imgPath.'" style="width:100%;
                                              height:180px; object-fit:cover;">';
                                    } else {
                                        echo $icon;
                                    }
                                    ?>
                                </div>
                            </a>
                            <div class="product-body">
                                <span class="product-cat">
                                    <?= htmlspecialchars($p['category_name']) ?>
                                </span>
                                <div class="product-name">
                                    <?= htmlspecialchars($p['name']) ?>
                                </div>
                                <div class="product-price mt-1">
                                    ₹<?= number_format($p['price'], 2) ?>
                                </div>
                                <small style="color:#999;">
                                    Stock: <?= $p['stock'] ?> packets
                                </small>
                                <button onclick="addToCart(<?= $p['id'] ?>)"
                                        class="btn-add-cart">
                                    <i class="bi bi-cart-plus"></i> Add to Cart
                                </button>
                                <button onclick="addToWishlist(<?= $p['id'] ?>)"
                                        class="btn-wishlist">
                                    <i class="bi bi-heart"></i> Wishlist
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function addToCart(productId) {
    fetch('../actions/cart_action.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=add&product_id=' + productId
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('✅ Added to cart!');
        } else {
            alert(data.message || 'Could not add to cart.');
        }
    });
}

function addToWishlist(productId) {
    fetch('../actions/wishlist_action.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=add&product_id=' + productId
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('💚 Added to wishlist!');
        } else {
            alert(data.message || 'Could not add to wishlist.');
        }
    });
}
</script>
</body>
</html>