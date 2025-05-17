<?php
use App\Models\Product;
use App\Models\Category;

$categories = Category::where('status', 1)->get();
$selectedCategory = isset($_GET['category']) ? $_GET['category'] : null;

$query = Product::with(['category', 'brand', 'productVariants']);
if ($selectedCategory) {
    $query->where('category_id', $selectedCategory);
}
$products = $query->limit(30)->get();
$totalProducts = $query->count();
?>

<section class="categories">
        <div class="section-title">
            <h2>Sản Phẩm Theo Danh Mục</h2>
            <p>Khám phá những sản phẩm được yêu thích nhất của chúng tôi</p>
        </div>
        <div class="product-grid">
            <?php foreach ($products as $product) { 
                $maxPriceOld = $product->productVariants->max('price_old');
                $minPriceNew = $product->productVariants->min('price_new');
                $discount = $maxPriceOld > 0 ? round((($maxPriceOld - $minPriceNew) / $maxPriceOld) * 100) : 0;
            ?>
                <div class="product-card">
                    <a href="?page=product-detail&id=<?php echo $product->id; ?>" style="text-decoration:none;color:inherit;">
                        <div class="product-image">
                            <?php if ($discount > 0) { ?>
                                <span class="discount-label">-<?php echo $discount; ?>%</span>
                            <?php } ?>
                            <img src="<?php echo BASE_URL . '/public/' . $product->image; ?>" alt="<?php echo $product->name; ?>">
                        </div>
                        <div class="product-info">
                            <h3><?php echo $product->name; ?></h3>
                            <div class="product-price-block">
                                <?php if ($maxPriceOld > 0) { ?>
                                    <span class="old-price"><?php echo number_format($maxPriceOld, 0, ',', '.'); ?> VNĐ</span>
                                <?php } ?>
                                <span class="new-price"><?php echo number_format($minPriceNew, 0, ',', '.'); ?> VNĐ</span>
                            </div>
                            <button 
                                class="add-to-cart" 
                                data-id="<?php echo $product->id; ?>" 
                                data-id-variant="<?php echo isset($product->productVariants[0]) ? $product->productVariants[0]->id : ''; ?>">
                                Xem chi tiết
                            </button>
                        </div>
                    </a>
                </div>
            <?php } ?>
        </div>
        <?php if ($totalProducts > 30) { ?>
            <div class="view-more-container">
                <a href="?page=product-list" class="view-more-btn">
                    Xem thêm
                </a>
            </div>
        <?php } ?>
</section>

<style>
.view-more-container {
    text-align: center;
    margin-top: 30px;
}

.view-more-btn {
    display: inline-block;
    padding: 12px 30px;
    background-color: #333;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    transition: background-color 0.3s;
}

.view-more-btn:hover {
    background-color: #555;
}
</style>