<?php
use App\Models\Post;

// Get featured post (latest post)
$featuredPost = Post::where('status', 1)
    ->orderBy('created_at', 'desc')
    ->first();

// Get 4 regular posts (excluding the featured post)
$regularPosts = Post::where('status', 1)
    ->where('id', '!=', $featuredPost ? $featuredPost->id : 0)
    ->orderBy('created_at', 'desc')
    ->take(4)
    ->get();
?>
    <!-- News Section -->
    <section class="news-section">
        <div class="news-header"></div>
            <span class="news-title">TIN TỨC</span>
        </div>
        <div class="news-content">
            <?php if ($featuredPost): ?>
            <a href="?page=post&id=<?php echo $featuredPost->id; ?>" class="news-featured">
                <div class="news-featured-img">
                    <img src="<?php echo BASE_URL . '/public/' . $featuredPost->image; ?>" alt="<?php echo $featuredPost->title; ?>">
                </div>
                <div class="news-featured-body">
                    <div class="news-featured-title"><?php echo $featuredPost->title; ?></div>
                    <div class="news-featured-time"><?php echo date('H:i:s d/m/Y', strtotime($featuredPost->created_at)); ?></div>
                </div>
            </a>
            <?php endif; ?>
            
            <div class="news-list">
                <?php foreach ($regularPosts as $post): ?>
                <a href="?page=post&id=<?php echo $post->id; ?>" class="news-item">
                    <div class="news-item-img">
                        <img src="<?php echo BASE_URL . '/public/' . $post->image; ?>" alt="<?php echo $post->title; ?>">
                    </div>
                    <div class="news-item-body">
                        <div class="news-item-title"><?php echo $post->title; ?></div>
                        <div class="news-item-time"><?php echo date('H:i:s d/m/Y', strtotime($post->created_at)); ?></div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>