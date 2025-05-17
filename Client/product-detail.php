<?php
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;

// Lấy id sản phẩm từ query string
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = Product::with(['category', 'brand', 'productVariants'])->find($productId);

if (!$product) {
    echo '<div style="padding:40px;text-align:center;">Sản phẩm không tồn tại.</div>';
    return;
}

// Lấy giá cao nhất/thấp nhất
$maxPriceOld = $product->productVariants->max('price_old');
$minPriceNew = $product->productVariants->min('price_new');
$discount = $maxPriceOld > 0 ? round((($maxPriceOld - $minPriceNew) / $maxPriceOld) * 100) : 0;

// Lấy các thuộc tính variant (size, màu...)
$variants = $product->productVariants;

// Lấy sản phẩm liên quan (ưu tiên cùng danh mục, nếu không có thì cùng thương hiệu)
$relatedQuery = Product::with(['productVariants'])
    ->where('id', '!=', $product->id)
    ->where(function($q) use ($product) {
        if ($product->category_id) {
            $q->where('category_id', $product->category_id);
        } else if ($product->brand_id) {
            $q->where('brand_id', $product->brand_id);
        }
    })
    ->limit(8);
$relatedProducts = $relatedQuery->get();
?>

<div class="product-detail-container">
    <div class="product-detail-main">
        <div class="product-detail-image">
            <img src="<?php echo BASE_URL . '/public/' . $product->image; ?>" alt="<?php echo $product->name; ?>">
            <?php if ($discount > 0) { ?>
                <span class="discount-label">-<?php echo $discount; ?>%</span>
            <?php } ?>
        </div>
        <div class="product-detail-info">
            <h1><?php echo $product->name; ?></h1>
            <div class="product-detail-prices">
                <?php if ($maxPriceOld > 0) { ?>
                    <span class="old-price"><?php echo number_format($maxPriceOld, 0, ',', '.'); ?> VNĐ</span>
                <?php } ?>
                <span class="new-price"><?php echo number_format($minPriceNew, 0, ',', '.'); ?> VNĐ</span>
            </div>
            <div class="product-detail-meta">
                <span>Danh mục: <b><?php echo $product->category->name ?? '-'; ?></b></span> |
                <span>Thương hiệu: <b><?php echo $product->brand->name ?? '-'; ?></b></span>
            </div>
            <div class="product-detail-short">
                <?php echo nl2br($product->detail); ?>
            </div>
            <?php if (count($variants) > 1) { ?>
                <div class="product-variants">
                    <b>Chọn phân loại:</b>
                    <ul id="variant-list">
                        <?php foreach ($variants as $variant) { ?>
                            <li class="variant-item">
                                <label>
                                    <input type="radio" name="variant" value="<?php echo $variant->id; ?>" style="display:none;">
                                    <div class="variant-info">
                                        <div class="variant-attributes">
                                            <?php echo $variant->size ? 'Size: ' . $variant->size . ' ' : ''; ?>
                                            <?php echo $variant->color ? 'Màu: ' . $variant->color . ' ' : ''; ?>
                                        </div>
                                        <div class="variant-price">
                                            <?php if ($variant->price_old && $variant->price_old > $variant->price_new) { ?>
                                                <span class="old-price"><?php echo number_format($variant->price_old, 0, ',', '.'); ?> VNĐ</span>
                                            <?php } ?>
                                            <span class="new-price"><?php echo number_format($variant->price_new, 0, ',', '.'); ?> VNĐ</span>
                                        </div>
                                    </div>
                                </label>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            <?php } ?>
            <div class="quantity-group">
                <button type="button" class="qty-btn" id="qty-minus">-</button>
                <input type="number" id="cart-qty" value="1" min="1" max="99" />
                <button type="button" class="qty-btn" id="qty-plus">+</button>
            </div>
            <button class="add-to-cart-detail">Thêm vào giỏ</button>
        </div>
    </div>
    <div class="product-detail-description">
        <h2>Mô tả sản phẩm</h2>
        <div><?php echo nl2br($product->description); ?></div>
    </div>
    <div class="related-products-section">
        <h2>Sản phẩm liên quan</h2>
        <div class="products-grid">
            <?php foreach ($relatedProducts as $rel) { 
                $maxOld = $rel->productVariants->max('price_old');
                $minNew = $rel->productVariants->min('price_new');
                $relDiscount = $maxOld > 0 ? round((($maxOld - $minNew) / $maxOld) * 100) : 0;
            ?>
            <div class="product-card">
                <div class="product-image">
                    <?php if ($relDiscount > 0) { ?>
                        <span class="discount-label">-<?php echo $relDiscount; ?>%</span>
                    <?php } ?>
                    <a href="?id=<?php echo $rel->id; ?>">
                        <img src="<?php echo BASE_URL . '/public/' . $rel->image; ?>" alt="<?php echo $rel->name; ?>">
                    </a>
                </div>
                <div class="product-info">
                    <h3><?php echo $rel->name; ?></h3>
                    <div class="product-price-block">
                        <?php if ($maxOld > 0) { ?>
                            <span class="old-price"><?php echo number_format($maxOld, 0, ',', '.'); ?> VNĐ</span>
                        <?php } ?>
                        <span class="new-price"><?php echo number_format($minNew, 0, ',', '.'); ?> VNĐ</span>
                    </div>
                    <a href="?page=product-detail&id=<?php echo $rel->id; ?>" class="add-to-cart-s" style="text-align:center;display:block;">Xem chi tiết</a>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
</div>

<div id="cart-popup" style="display:none;">
    <div class="cart-popup-content">
        <i class="fas fa-check-circle"></i>
        Đã thêm vào giỏ hàng!
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Variant chọn bằng click li
    const variantList = document.getElementById('variant-list');
    if (variantList) {
        const items = variantList.querySelectorAll('.variant-item');
        items.forEach(function(item, idx) {
            item.addEventListener('click', function() {
                // Bỏ active các li khác
                items.forEach(i => i.classList.remove('active'));
                item.classList.add('active');
                // Set checked cho radio
                const radio = item.querySelector('input[type="radio"]');
                if (radio) radio.checked = true;
            });
        });
    }
    const addToCartBtn = document.querySelector('.add-to-cart-detail');
    if (!addToCartBtn) return;
    addToCartBtn.addEventListener('click', function(e) {
        e.preventDefault();
        const productId = <?php echo (int)$product->id; ?>;
        let variantId = '';
        const checkedVariant = document.querySelector('input[name="variant"]:checked');
        if (checkedVariant) {
            variantId = checkedVariant.value;
        } else if (document.querySelector('input[name="variant"]')) {
            alert('Vui lòng chọn phân loại sản phẩm!');
            return;
        } else if (<?php echo count($variants); ?> === 1) {
            variantId = <?php echo (int)($variants[0]->id ?? 0); ?>;
        }
        // Lấy số lượng
        let qty = parseInt(document.getElementById('cart-qty').value) || 1;
        if (qty < 1) qty = 1;
        fetch('/PTH_WEB/Client/add-to-cart.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'add_to_cart=1&product_id=' + productId + '&variant_id=' + variantId + '&quantity=' + qty
        })
        .then(res => res.text())
        .then(data => {
            console.log(data);
            showCartPopup('Đã thêm vào giỏ hàng!');
            var cartCountElem = document.querySelector('.cart-count');
            if(cartCountElem && !isNaN(parseInt(data))) {
                cartCountElem.textContent = data;
            }
        });
    });
    document.getElementById('qty-minus').addEventListener('click', function() {
        var qtyInput = document.getElementById('cart-qty');
        var val = parseInt(qtyInput.value) || 1;
        if (val > 1) qtyInput.value = val - 1;
    });
    document.getElementById('qty-plus').addEventListener('click', function() {
        var qtyInput = document.getElementById('cart-qty');
        var val = parseInt(qtyInput.value) || 1;
        if (val < 99) qtyInput.value = val + 1;
    });
});

function showCartPopup(msg) {
    var popup = document.getElementById('cart-popup');
    if (!popup) return;
    popup.querySelector('.cart-popup-content').innerHTML = '<i class="fas fa-check-circle"></i> ' + msg;
    popup.style.display = 'block';
    setTimeout(function() {
        popup.style.display = 'none';
    }, 1800);
}
</script>

<style>
#variant-list .active{
    border:1.2px solid #d4af37; 
    background-color: rgba(212, 175, 55, 0.05);
}
.product-detail-container {
    max-width: 1100px;
    margin: 40px auto 0 auto;
    padding: 24px;
    background: #fafbfc;
    border-radius: 16px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.06);
    margin-top: 50px;
    margin-bottom: 50px;
}
.product-detail-main {
    display: flex;
    gap: 40px;
    margin-bottom: 32px;
}
.product-detail-image {
    flex: 0 0 380px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.04);
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 380px;
    overflow: hidden;
}
.product-detail-image img {
    width: 90%;
    height: 90%;
    object-fit: contain;
    display: block;
}
.discount-label {
    position: absolute;
    top: 18px;
    right: 18px;
    background: #ff4444;
    color: #fff;
    padding: 7px 14px;
    border-radius: 10px;
    font-size: 15px;
    font-weight: 700;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    z-index: 2;
}
.product-detail-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    align-items: flex-start;
    padding-top: 18px;
}
.product-detail-info h1 {
    font-size: 2rem;
    margin: 0 0 18px 0;
    color: #222;
    font-weight: 700;
}
.product-detail-prices {
    margin-bottom: 14px;
}
.old-price {
    color: #b0b0b0;
    text-decoration: line-through;
    font-size: 17px;
    margin-right: 10px;
}
.new-price {
    color: #ff4444;
    font-size: 22px;
    font-weight: 700;
}
.product-detail-meta {
    margin-bottom: 14px;
    color: #666;
    font-size: 15px;
}
.product-detail-short {
    margin-bottom: 18px;
    color: #333;
    font-size: 16px;
}
.product-variants {
    margin-bottom: 18px;
    font-size: 15px;
}
.product-variants ul {
    padding: 0;
    margin: 8px 0 0 0;
    list-style: none;
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}
.product-variants li {
    background: #f1f3f6;
    border-radius: 6px;
    font-size: 14px;
}
.add-to-cart-s {
    padding: 12px 0;
    width: 220px;
    background: linear-gradient(90deg, #007bff 60%, #0056b3 100%);
    color: #fff;
    border: none;
    border-radius: 999px;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    margin-top: 10px;
    transition: background 0.2s, box-shadow 0.2s;
    box-shadow: 0 2px 8px rgba(0,123,255,0.08);
}
.add-to-cart-s:hover {
    background: linear-gradient(90deg, #0056b3 60%, #007bff 100%);
    box-shadow: 0 4px 16px rgba(0,123,255,0.13);
}
.product-detail-description {
    margin: 32px 0 0 0;
    background: #fff;
    border-radius: 10px;
    padding: 24px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.04);
}
.product-detail-description h2 {
    font-size: 1.3rem;
    margin-bottom: 12px;
    color: #222;
}
.related-products-section {
    margin-top: 40px;
}
.related-products-section h2 {
    font-size: 1.2rem;
    margin-bottom: 18px;
    color: #222;
}
.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 18px;
    margin-bottom: 0;
}
.product-card {
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    transition: transform 0.2s, box-shadow 0.2s;
    width: 180px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    align-items: center;
}
.product-card:hover {
    transform: translateY(-4px) scale(1.03);
    box-shadow: 0 6px 18px rgba(0,0,0,0.10);
}
.product-image {
    width: 100%;
    aspect-ratio: 1/1;
    background: #f7f7f7;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
}
.product-image img {
    width: 90%;
    height: 90%;
    object-fit: contain;
    display: block;
    margin: 0 auto;
}
.product-info {
    width: 100%;
    padding: 12px 10px 10px 10px;
    display: flex;
    flex-direction: column;
    align-items: center;
}
.product-info h3 {
    font-size: 14px;
    margin: 0 0 8px 0;
    color: #222;
    font-weight: 600;
    text-align: center;
    line-height: 1.3;
    min-height: 32px;
    max-height: 32px;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}
.product-price-block {
    margin-bottom: 8px;
    text-align: center;
}
.old-price {
    color: #b0b0b0;
    text-decoration: line-through;
    font-size: 12px;
    margin-right: 5px;
}
.new-price {
    color: #ff4444;
    font-size: 15px;
    font-weight: 700;
}
.add-to-cart-s {
    width: 100%;
    padding: 8px 0;
    background: linear-gradient(90deg, #007bff 60%, #0056b3 100%);
    color: #fff;
    border: none;
    border-radius: 999px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    margin-top: 6px;
    transition: background 0.2s, box-shadow 0.2s;
    box-shadow: 0 2px 8px rgba(0,123,255,0.08);
    text-align: center;
    text-decoration: none;
}
.add-to-cart-s:hover {
    background: linear-gradient(90deg, #0056b3 60%, #007bff 100%);
    box-shadow: 0 4px 16px rgba(0,123,255,0.13);
}
@media (max-width: 900px) {
    .product-detail-main {
        flex-direction: column;
        gap: 24px;
    }
    .product-detail-image {
        min-height: 220px;
        flex-basis: 220px;
    }
    .product-card, .products-grid {
        width: 100%;
        min-width: 0;
    }
}
.add-to-cart-detail{
    padding: 12px 0;
    width: 220px;
    background: #d4af37;
    color: #fff;
    border: none;
    border-radius: 999px;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    margin-top: 10px;
    transition: background 0.2s, box-shadow 0.2s;
    box-shadow: 0 2px 8px rgba(0,123,255,0.08);
}

.add-to-cart-detail:hover {
    background: #b38f2a;
    box-shadow: 0 4px 16px rgba(0,123,255,0.13);
}
.product-variants input[type="radio"] {
    display: none;
}
.variant-item {
    margin-bottom: 10px;
}

.variant-item label {
    display: block;
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.variant-item label:hover {
    border-color: #d4af37; 
    background-color: rgba(212, 175, 55, 0.05);
    
    color: #fff;
}

.variant-item input[type="radio"]:checked + .variant-info {
    color: #d4af37;
}

.variant-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.variant-attributes {
    font-size: 0.95rem;
    color: #333;
}

.variant-price {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 5px;
}

.old-price {
    font-size: 0.85rem;
    color: #999;
    text-decoration: line-through;
}

.new-price {
    font-size: 1.1rem;
    font-weight: 600;
    color:rgb(0, 0, 0);
}

.variant-item input[type="radio"]:checked + .variant-info .variant-attributes,
.variant-item input[type="radio"]:checked + .variant-info .new-price {
    color:#333;
}

.variant-item input[type="radio"]:checked + .variant-info .old-price {
    color:#999;
}

#cart-popup {
    position: fixed;
    top: 30px;
    right: 30px;
    z-index: 9999;
    display: none;
}
.cart-popup-content {
    background: #fff;
    color: #222;
    border-radius: 10px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.13);
    padding: 18px 32px;
    font-size: 18px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 12px;
    border-left: 5px solid #28a745;
}
.cart-popup-content i {
    color: #28a745;
    font-size: 24px;
}
.quantity-group {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 18px;
}
.qty-btn {
    width: 36px;
    height: 36px;
    border: none;
    background: #f1f3f6;
    color: #222;
    font-size: 22px;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.2s;
}
.qty-btn:hover {
    background: #e7f1ff;
}
#cart-qty {
    width: 48px;
    height: 36px;
    text-align: center;
    font-size: 18px;
    border: 1.5px solid #e0e0e0;
    border-radius: 8px;
    outline: none;
}
</style>
