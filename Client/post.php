<?php
use App\Models\Post;
use Illuminate\Database\QueryException;
?>
<style>
/* Common Styles */
.post-detail, .posts-listing {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    font-family: 'Arial', sans-serif;
}

/* Post Detail Styles */
.post-detail {
    background: #fff;
    box-shadow: 0 0 20px rgba(0,0,0,0.1);
    border-radius: 8px;
    padding: 30px;
    margin-top: 90px;
    margin-bottom: 20px;
}

.post-detail .post-header {
    margin-bottom: 30px;
    text-align: center;
}

.post-detail .post-title {
    font-size: 2.5em;
    color: #333;
    margin-bottom: 15px;
    line-height: 1.3;
}

.post-detail .post-meta {
    color: #666;
    font-size: 0.9em;
    margin-bottom: 20px;
}

.post-detail .post-meta span {
    margin: 0 15px;
}

.post-detail .post-image {
    margin: 30px 0;
    text-align: center;
}

.post-detail .post-image img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.post-detail .post-content {
    line-height: 1.8;
    color: #444;
    font-size: 1.1em;
}

/* Posts Listing Styles */
.posts-listing {
    background: #f8f9fa;
    margin-top: 60px;
}

.posts-header {
    text-align: center;
    margin-bottom: 40px;
}

.section-title {
    font-size: 2em;
    color: #333;
    margin-bottom: 20px;
    position: relative;
    padding-bottom: 15px;
}

.section-title:after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 100px;
    height: 3px;
    background: #007bff;
}

.posts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 30px;
    padding: 20px 0;
}

.post-card {
    background: #fff;
    border-radius: 10px;
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.post-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.post-link {
    text-decoration: none;
    color: inherit;
    display: block;
}

.post-card .post-image {
    width: 100%;
    height: 200px;
    overflow: hidden;
}

.post-card .post-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.post-card:hover .post-image img {
    transform: scale(1.05);
}

.post-info {
    padding: 20px;
}

.post-card .post-title {
    font-size: 1.2em;
    color: #333;
    margin-bottom: 10px;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.post-card .post-meta {
    display: flex;
    justify-content: space-between;
    color: #666;
    font-size: 0.85em;
    margin-bottom: 10px;
}

.post-card .post-excerpt {
    color: #666;
    font-size: 0.9em;
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Pagination Styles */
.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-top: 40px;
    gap: 10px;
}

.pagination a, .pagination span {
    padding: 8px 16px;
    border-radius: 5px;
    text-decoration: none;
    color: #333;
    background: #fff;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.pagination a:hover {
    background: #007bff;
    color: #fff;
}

.pagination .current {
    background: #007bff;
    color: #fff;
}

.pagination .disabled {
    color: #999;
    cursor: not-allowed;
}

/* Error Message Style */
.error-message {
    text-align: center;
    padding: 50px;
    color: #dc3545;
    font-size: 1.2em;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 0 20px rgba(0,0,0,0.1);
}
</style>

<?php
try {
    // Check if id parameter exists
    if (isset($_GET['id'])) {
        // Get post detail
        $post = Post::where('status', 1)
            ->where('id', $_GET['id'])
            ->first();

        if ($post) {
            // Increment view count
            $post->increment('views');
            ?>
            <!-- Post Detail Section -->
            <section class="post-detail">
                <div class="post-header">
                    <h1 class="post-title"><?php echo htmlspecialchars($post->title); ?></h1>
                    <div class="post-meta">
                        <span class="post-date"><?php echo date('d/m/Y H:i', strtotime($post->created_at)); ?></span>
                        <span class="post-views">Lượt xem: <?php echo $post->views; ?></span>
                    </div>
                </div>
                <div class="post-image">
                    <img src="<?php echo BASE_URL . '/public/' . htmlspecialchars($post->image); ?>" alt="<?php echo htmlspecialchars($post->title); ?>">
                </div>
                <div class="post-content">
                    <?php echo $post->content; ?>
                </div>
            </section>
            <?php
        } else {
            echo '<div class="error-message">Bài viết không tồn tại hoặc đã bị xóa.</div>';
        }
    } else {
        // Pagination parameters
        $page = isset($_GET['page_num']) ? (int)$_GET['page_num'] : 1;
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        // Get total posts count
        $totalPosts = Post::where('status', 1)->count();
        $totalPages = ceil($totalPosts / $perPage);

        // Get posts for current page
        $posts = Post::where('status', 1)
            ->orderBy('created_at', 'desc')
            ->skip($offset)
            ->take($perPage)
            ->get();
        ?>
        <!-- Posts Listing Section -->
        <section class="posts-listing">
            <div class="posts-header">
                <h1 class="section-title">Danh sách bài viết</h1>
            </div>
            <div class="posts-grid">
                <?php foreach ($posts as $post): ?>
                <div class="post-card">
                    <a href="?page=post&id=<?php echo $post->id; ?>" class="post-link">
                        <div class="post-image">
                            <img src="<?php echo BASE_URL . '/public/' . htmlspecialchars($post->image); ?>" alt="<?php echo htmlspecialchars($post->title); ?>">
                        </div>
                        <div class="post-info">
                            <h2 class="post-title"><?php echo htmlspecialchars($post->title); ?></h2>
                            <div class="post-meta">
                                <span class="post-date"><?php echo date('d/m/Y', strtotime($post->created_at)); ?></span>
                                <span class="post-views"><?php echo $post->views; ?> lượt xem</span>
                            </div>
                            <div class="post-excerpt">
                                <?php echo htmlspecialchars($post->short_title); ?>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=post&page_num=<?php echo $page - 1; ?>">Trang trước</a>
                <?php else: ?>
                    <span class="disabled">Trang trước</span>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="current"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?page=post&page_num=<?php echo $i; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?page=post&page_num=<?php echo $page + 1; ?>">Trang sau</a>
                <?php else: ?>
                    <span class="disabled">Trang sau</span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </section>
        <?php
    }
} catch (QueryException $e) {
    // Log the error
    error_log("Database Error: " . $e->getMessage());
    echo '<div class="error-message">Có lỗi xảy ra khi tải dữ liệu. Vui lòng thử lại sau.</div>';
} catch (Exception $e) {
    // Log the error
    error_log("General Error: " . $e->getMessage());
    echo '<div class="error-message">Có lỗi xảy ra. Vui lòng thử lại sau.</div>';
}
?>
