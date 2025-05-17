<?php
use App\Models\Banner;


$banners = Banner::where('status', 1)->orderBy('created_at', 'desc')->get();


?>

<section class="hero-slider">
        <div class="slider-container">
            <?php foreach ($banners as $banner): ?>
                <div class="slide" style="background-image: url('<?php echo BASE_URL . '/public/' . $banner->image; ?>')">
                    <div class="slide-content">
                        <h1><?php echo $banner->title; ?></h1>
                        <a href="<?php echo $banner->link; ?>" class="cta-button" style="text-decoration: none">Xem ngay</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="slider-nav">
            <span class="slider-dot active"></span>
            <span class="slider-dot"></span>
            <span class="slider-dot"></span>
        </div>
        <div class="slider-arrow slider-prev">
            <i class="fas fa-chevron-left"></i>
        </div>
        <div class="slider-arrow slider-next">
            <i class="fas fa-chevron-right"></i>
        </div>
    </section>