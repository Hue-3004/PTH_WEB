<?php
use App\Models\Product;
use App\Models\ProductVariant;
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$cartItems = [];
$total = 0;

foreach ($cart as $item) {
    $product = Product::find($item['product_id']);
    $variant = ProductVariant::find($item['variant_id']);
    $quantity = isset($item['quantity']) ? (int)$item['quantity'] : 1;
    if ($product && $variant) {
        $cartItems[] = [
            'product' => $product,
            'variant' => $variant,
            'quantity' => $quantity
        ];
        $total += $variant->price_new * $quantity;
    }
}
?>

<div class="cart-container">
    <h2>Giỏ Hàng Của Bạn</h2>
    <?php if (count($cartItems) === 0) { ?>
        <div class="cart-empty">Chưa có sản phẩm nào trong giỏ hàng.</div>
    <?php } else { ?>
    <div class="cart-table-wrapper">
    <table class="cart-table">
        <thead>
            <tr>
                <th>Ảnh</th>
                <th>Sản phẩm</th>
                <th>Phân loại</th>
                <th>Giá</th>
                <th>Số lượng</th>
                <th>Thành tiền</th>
                <th>Xóa</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cartItems as $item) { ?>
            <tr>
                <td><img src="<?php echo BASE_URL . '/public/' . $item['product']->image; ?>" alt="<?php echo $item['product']->name; ?>" class="cart-img"></td>
                <td>
                    <div class="cart-prod-name"><?php echo $item['product']->name; ?></div>
                </td>
                <td>
                    <?php echo $item['variant']->size ? 'Size: ' . $item['variant']->size . ' ' : ''; ?>
                    <?php echo $item['variant']->color ? 'Màu: ' . $item['variant']->color . ' ' : ''; ?>
                </td>
                <td class="cart-price"><?php echo number_format($item['variant']->price_new, 0, ',', '.'); ?> VNĐ</td>
                <td>
                    <div class="cart-qty-group">
                        <button class="cart-qty-btn" data-action="minus" data-id="<?php echo $item['product']->id; ?>" data-variant="<?php echo $item['variant']->id; ?>">-</button>
                        <input type="number" class="cart-qty-input" min="1" max="99" value="<?php echo $item['quantity']; ?>" data-id="<?php echo $item['product']->id; ?>" data-variant="<?php echo $item['variant']->id; ?>">
                        <button class="cart-qty-btn" data-action="plus" data-id="<?php echo $item['product']->id; ?>" data-variant="<?php echo $item['variant']->id; ?>">+</button>
                    </div>
                </td>
                <td class="cart-price cart-row-total"><?php echo number_format($item['variant']->price_new * $item['quantity'], 0, ',', '.'); ?> VNĐ</td>
                <td><button class="remove-cart-item" data-id="<?php echo $item['product']->id; ?>" data-variant="<?php echo $item['variant']->id; ?>">&times;</button></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
    </div>
    <div class="cart-total">
        <span>Tổng cộng:</span>
        <span class="cart-total-price"><?php echo number_format($total, 0, ',', '.'); ?> VNĐ</span>
    </div>
    <div class="cart-actions">
        <a href="?page=check-out" class="checkout-btn">Thanh toán</a>
    </div>
    <?php } ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tăng/giảm số lượng
    document.querySelectorAll('.cart-qty-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var input = btn.parentElement.querySelector('.cart-qty-input');
            var val = parseInt(input.value) || 1;
            if (btn.dataset.action === 'plus' && val < 99) val++;
            if (btn.dataset.action === 'minus' && val > 1) val--;
            input.value = val;
            updateCartQty(input);
        });
    });
    // Sửa trực tiếp input
    document.querySelectorAll('.cart-qty-input').forEach(function(input) {
        input.addEventListener('change', function() {
            var val = parseInt(input.value) || 1;
            if (val < 1) val = 1;
            if (val > 99) val = 99;
            input.value = val;
            updateCartQty(input);
        });
    });
    // Xóa sản phẩm
    document.querySelectorAll('.remove-cart-item').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = btn.dataset.id;
            var variant = btn.dataset.variant;
            fetch('/PTH_WEB/Client/update-cart.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=remove&product_id=' + id + '&variant_id=' + variant
            })
            .then(res => res.json())
            .then(data => {
                 location.reload();
            });
        });
    });
    function updateCartQty(input) {
        var id = input.dataset.id;
        var variant = input.dataset.variant;
        var qty = parseInt(input.value) || 1;
        fetch('/PTH_WEB/Client/update-cart.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=update&product_id=' + id + '&variant_id=' + variant + '&quantity=' + qty
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                window.location.reload();
            }
        });
    }
});
</script>

<style>
.cart-container {
    max-width: 900px;
    margin: 40px auto 0 auto;
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.07);
    padding: 32px 24px 32px 24px;
    margin-bottom: 30px;
    margin-top: 90px;
}
.cart-container h2 {
    font-size: 2rem;
    margin-bottom: 28px;
    color: #222;
    font-weight: 700;
    text-align: center;
}
.cart-empty {
    text-align: center;
    color: #888;
    font-size: 18px;
    padding: 40px 0;
}
.cart-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 24px;
}
.cart-table th, .cart-table td {
    padding: 14px 10px;
    text-align: center;
    border-bottom: 1px solid #f0f0f0;
}
.cart-table th {
    background: #f7f7f7;
    font-size: 16px;
    color: #333;
    font-weight: 600;
}
.cart-img {
    width: 70px;
    height: 70px;
    object-fit: contain;
    border-radius: 8px;
    background: #fafbfc;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.cart-prod-name {
    font-size: 16px;
    color: #222;
    font-weight: 600;
    text-align: left;
}
.cart-price {
    color: #ff4444;
    font-size: 16px;
    font-weight: 700;
}
.remove-cart-item {
    background: #ff4444;
    color: #fff;
    border: none;
    border-radius: 50%;
    width: 32px;
    height: 32px;
    font-size: 20px;
    cursor: pointer;
    transition: background 0.2s;
}
.remove-cart-item:hover {
    background: #d32f2f;
}
.cart-total {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 18px;
    gap: 10px;
}
.cart-total-price {
    color: #d4af37;
    font-size: 20px;
    font-weight: 700;
}
.cart-actions {
    text-align: right;
}
.checkout-btn {
    background: #d4af37;
    color: #fff;
    padding: 12px 36px;
    border-radius: 999px;
    font-size: 17px;
    font-weight: 700;
    text-decoration: none;
    transition: background 0.2s, box-shadow 0.2s;
    box-shadow: 0 2px 8px rgba(0,123,255,0.08);
    display: inline-block;
}
.checkout-btn:hover {
    background: #b38f2a;
    box-shadow: 0 4px 16px rgba(0,123,255,0.13);
}
@media (max-width: 700px) {
    .cart-container {
        padding: 12px 2px;
    }
    .cart-table th, .cart-table td {
        padding: 8px 2px;
        font-size: 13px;
    }
    .cart-img {
        width: 44px;
        height: 44px;
    }
    .checkout-btn {
        font-size: 14px;
        padding: 8px 18px;
    }
}
.cart-qty-group {
    display: flex;
    align-items: center;
    gap: 6px;
    justify-content: center;
}
.cart-qty-btn {
    width: 28px;
    height: 28px;
    border: none;
    background: #f1f3f6;
    color: #222;
    font-size: 18px;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.2s;
}
.cart-qty-btn:hover {
    background: #e7f1ff;
}
.cart-qty-input {
    width: 40px;
    height: 28px;
    text-align: center;
    font-size: 15px;
    border: 1.5px solid #e0e0e0;
    border-radius: 6px;
    outline: none;
}
.cart-table-wrapper {
    max-height: 400px;
    overflow-y: auto;
    margin-bottom: 24px;
}
</style>
