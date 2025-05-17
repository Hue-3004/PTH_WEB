<?php
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;

// Lấy các tham số lọc
$selectedCategory = isset($_GET['category']) ? $_GET['category'] : null;
$selectedBrand = isset($_GET['brand']) ? $_GET['brand'] : null;
$priceRange = isset($_GET['price']) ? $_GET['price'] : null;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 12;
$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';

// Lấy danh sách danh mục và thương hiệu
$categories = Category::where('status', 1)->get();
$brands = Brand::where('status', 1)->get();

// Xây dựng query
$query = Product::with(['category', 'brand', 'productVariants'])->whereLike('name', '%' . $searchQuery . '%');

// Lọc theo danh mục
if ($selectedCategory) {
    $query->where('category_id', $selectedCategory);
}

// Lọc theo thương hiệu
if ($selectedBrand) {
    $query->where('brand_id', $selectedBrand);
}

// Lọc theo khoảng giá
if ($priceRange) {
    switch ($priceRange) {
        case 'under-500k':
            $query->whereHas('productVariants', function($q) {
                $q->where('price_new', '<=', 500000);
            });
            break;
        case '500k-1m':
            $query->whereHas('productVariants', function($q) {
                $q->where('price_new', '>', 500000)
                  ->where('price_new', '<=', 1000000);
            });
            break;
        case '1m-2m':
            $query->whereHas('productVariants', function($q) {
                $q->where('price_new', '>', 1000000)
                  ->where('price_new', '<=', 2000000);
            });
            break;
        case 'over-2m':
            $query->whereHas('productVariants', function($q) {
                $q->where('price_new', '>', 2000000);
            });
            break;
    }
}

// Tính toán phân trang thủ công
$total = $query->count();
$totalPages = ceil($total / $perPage);
$offset = ($page - 1) * $perPage;
$products = $query->skip($offset)->take($perPage)->get();
$count=count($query->get());
// Tạo URL cho phân trang
function getPaginationUrl($page) {
    $params = $_GET;
    $params['page'] = $page;
    return '?' . http_build_query($params);
}
?>

<div class="product-list-container">
    <div class="filters-sidebar">
        <div class="filter-section">
            <h3>Danh Mục</h3>
            <ul class="filter-list">
                <li>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['category' => null])); ?>" 
                       class="<?php echo !$selectedCategory ? 'active' : ''; ?>">
                        Tất cả
                    </a>
                </li>
                <?php foreach ($categories as $category) { ?>
                    <li>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['category' => $category->id])); ?>" 
                           class="<?php echo $selectedCategory == $category->id ? 'active' : ''; ?>">
                            <?php echo $category->name; ?>
                        </a>
                    </li>
                <?php } ?>
            </ul>
        </div>

        <div class="filter-section">
            <h3>Thương Hiệu</h3>
            <ul class="filter-list">
                <li>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['brand' => null])); ?>" 
                       class="<?php echo !$selectedBrand ? 'active' : ''; ?>">
                        Tất cả
                    </a>
                </li>
                <?php foreach ($brands as $brand) { ?>
                    <li>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['brand' => $brand->id])); ?>" 
                           class="<?php echo $selectedBrand == $brand->id ? 'active' : ''; ?>">
                            <?php echo $brand->name; ?>
                        </a>
                    </li>
                <?php } ?>
            </ul>
        </div>

        <div class="filter-section">
            <h3>Khoảng Giá</h3>
            <ul class="filter-list">
                <li>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['price' => null])); ?>" 
                       class="<?php echo !$priceRange ? 'active' : ''; ?>">
                        Tất cả
                    </a>
                </li>
                <li>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['price' => 'under-500k'])); ?>" 
                       class="<?php echo $priceRange == 'under-500k' ? 'active' : ''; ?>">
                        Dưới 500.000đ
                    </a>
                </li>
                <li>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['price' => '500k-1m'])); ?>" 
                       class="<?php echo $priceRange == '500k-1m' ? 'active' : ''; ?>">
                        500.000đ - 1.000.000đ
                    </a>
                </li>
                <li>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['price' => '1m-2m'])); ?>" 
                       class="<?php echo $priceRange == '1m-2m' ? 'active' : ''; ?>">
                        1.000.000đ - 2.000.000đ
                    </a>
                </li>
                <li>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['price' => 'over-2m'])); ?>" 
                       class="<?php echo $priceRange == 'over-2m' ? 'active' : ''; ?>">
                        Trên 2.000.000đ
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="products-content">
        <div class="search-result-row">
            <?php if ($count == 0) { ?>
                <div class="no-products">
                    <h2>Không tìm thấy sản phẩm nào!</h2>
                </div>
            <?php } ?>
            <?php if ($searchQuery) { ?>
                <h2 class="search-result">Kết quả tìm kiếm cho: "<?php echo htmlspecialchars($searchQuery); ?>"</h2>    
            <?php } ?>
            <?php if ($count > 0) { ?>
                <h2 class="search-result">Tìm thấy <?php echo $count; ?> sản phẩm</h2>
            <?php } ?>
        </div>
        <div class="products-grid">
            <?php foreach ($products as $product) { 
                $maxPriceOld = $product->productVariants->max('price_old');
                $minPriceNew = $product->productVariants->min('price_new');
                $discount = $maxPriceOld > 0 ? round((($maxPriceOld - $minPriceNew) / $maxPriceOld) * 100) : 0;
            ?>
                <div class="product-card">
                    <a href="?page=product-detail&id=<?php echo $product->id; ?>" style="text-decoration:none;color:inherit;display:block;height:100%;">
                        <div class="product-image">
                            <?php if ($discount > 0) { ?>
                                <span class="discount-label">-<?php echo $discount; ?>%</span>
                            <?php } ?>
                            <img src="/public/<?php echo $product->image; ?>" alt="<?php echo $product->name; ?>">
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

        <?php if ($totalPages > 1) { ?>
            <div class="pagination">
                <?php if ($page > 1) { ?>
                    <a href="<?php echo getPaginationUrl($page - 1); ?>" class="page-link">&laquo; Trước</a>
                <?php } ?>

                <?php
                $start = max(1, $page - 2);
                $end = min($totalPages, $page + 2);

                for ($i = $start; $i <= $end; $i++) {
                    $activeClass = $i == $page ? 'active' : '';
                    echo "<a href='" . getPaginationUrl($i) . "' class='page-link $activeClass'>$i</a>";
                }
                ?>

                <?php if ($page < $totalPages) { ?>
                    <a href="<?php echo getPaginationUrl($page + 1); ?>" class="page-link">Sau &raquo;</a>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
</div>

<style>
.search-result-row {
    display: flex;
    align-items: center;
    gap: 24px;
    margin-bottom: 18px;
}
.search-result-row h2,
.search-result-row .no-products {
    margin: 0;
    font-size: 20px;
    font-weight: 500;
}
.product-list-container {
    display: flex;
    gap: 30px;
    padding: 20px;
    margin-top: 100px;
    max-width: 1400px;
    margin-left: auto;
    margin-right: auto;
}

.filters-sidebar {
    width: 280px;
    flex-shrink: 0;
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    position: sticky;
    top: 100px;
    height: fit-content;
}

.filter-section {
    margin-bottom: 30px;
    border-bottom: 1px solid #eee;
    padding-bottom: 20px;
}

.filter-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.filter-section h3 {
    font-size: 18px;
    margin-bottom: 15px;
    color: #333;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.filter-section h3::before {
    content: '';
    display: block;
    width: 4px;
    height: 18px;
    background: #007bff;
    border-radius: 2px;
}

.filter-list {
    list-style: none;
    padding: 0;
    margin: 0;
    overflow-y: auto;
    max-height: 200px;
}

.filter-list li {
    margin-bottom: 12px;
}

.filter-list li:last-child {
    margin-bottom: 0;
}

.filter-list a {
    color: #666;
    text-decoration: none;
    display: block;
    padding: 8px 12px;
    border-radius: 6px;
    transition: all 0.3s ease;
    font-size: 15px;
}

.filter-list a:hover {
    color: #007bff;
    background: #f8f9fa;
}

.filter-list a.active {
    color: #007bff;
    background: #e7f1ff;
    font-weight: 500;
}

.products-content {
    flex-grow: 1;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 24px;
    margin-bottom: 30px;
    justify-items: center;
}

.product-card {
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 24px rgba(0,0,0,0.07);
    transition: transform 0.25s, box-shadow 0.25s;
    width: 200px;
    display: flex;
    flex-direction: column;
    align-items: center;
    margin: 0;
    min-height: 350px;
}
.product-card:hover {
    transform: translateY(-6px) scale(1.03);
    box-shadow: 0 8px 32px rgba(0,0,0,0.12);
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
    transition: transform 0.3s;
    display: block;
    margin: 0 auto;
}
.product-card:hover .product-image img {
    transform: scale(1.07);
}
.discount-label {
    position: absolute;
    top: 12px;
    right: 12px;
    background: #ff4444;
    color: #fff;
    padding: 5px 10px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    z-index: 2;
}
.product-info {
    flex: 1;
    width: 100%;
    padding: 16px 14px 14px 14px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-end;
}
.product-info h3 {
    font-size: 15px;
    margin: 0 0 10px 0;
    color: #222;
    font-weight: 600;
    text-align: center;
    line-height: 1.3;
    min-height: 38px;
    max-height: 38px;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}
.product-price-block {
    margin-bottom: 12px;
    text-align: center;
}
.old-price {
    color: #b0b0b0;
    text-decoration: line-through;
    font-size: 13px;
    margin-right: 6px;
}
.new-price {
    color: #ff4444;
    font-size: 17px;
    font-weight: 700;
}
.add-to-cart {
    width: 100%;
    padding: 10px 0;
    background: linear-gradient(90deg, #007bff 60%, #0056b3 100%);
    color: #fff;
    border: none;
    border-radius: 999px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s, box-shadow 0.2s;
    box-shadow: 0 2px 8px rgba(0,123,255,0.08);
    margin-top: 8px;
}
.add-to-cart:hover {
    background: linear-gradient(90deg, #0056b3 60%, #007bff 100%);
    box-shadow: 0 4px 16px rgba(0,123,255,0.13);
}
.pagination {
    text-align: center;
    margin-top: 40px;
    display: flex;
    justify-content: center;
    gap: 8px;
}
.pagination .page-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 40px;
    height: 40px;
    padding: 0 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    text-decoration: none;
    color: #666;
    font-size: 15px;
    transition: all 0.3s ease;
}
.pagination .page-link:hover {
    background-color: #f8f9fa;
    border-color: #007bff;
    color: #007bff;
}
.pagination .page-link.active {
    background-color: #007bff;
    color: white;
    border-color: #007bff;
}
@media (max-width: 900px) {
    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 12px;
    }
    .product-card {
        width: 150px;
        min-height: 260px;
    }
    .product-info h3 {
        font-size: 12px;
        min-height: 28px;
        max-height: 28px;
    }
    .add-to-cart {
        font-size: 12px;
        padding: 7px 0;
    }
    .new-price {
        font-size: 13px;
    }
}
</style>
