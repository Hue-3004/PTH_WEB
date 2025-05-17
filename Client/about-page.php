<!-- About Page Section -->
<section class="about-section">
    <div class="about-hero">
        <div class="container">
            <h1 class="about-title">Về LUXE Fashion</h1>
            <p class="about-subtitle">Định hình phong cách thời trang cao cấp</p>
        </div>
    </div>

    <div class="about-content">
        <div class="container">
            <!-- Our Story Section -->
            <div class="about-block">
                <div class="about-text">
                    <h2>Câu Chuyện Của Chúng Tôi</h2>
                    <p>LUXE Fashion được thành lập với tình yêu và đam mê dành cho thời trang cao cấp. Từ những ngày đầu tiên, chúng tôi đã cam kết mang đến những sản phẩm chất lượng nhất, kết hợp giữa truyền thống và hiện đại.</p>
                    <p>Với hơn 10 năm kinh nghiệm trong ngành thời trang, chúng tôi tự hào là điểm đến tin cậy cho những người yêu thích phong cách thời trang đẳng cấp.</p>
                </div>
                <div class="about-image">
                    <img src="https://js0fpsb45jobj.vcdn.cloud/storage/upload/media/nam-moi-2024/thang-42025/1600x635-2.jpg" alt="LUXE Fashion Story">
                </div>
            </div>

            <!-- Our Mission Section -->
            <div class="about-block reverse">
                <div class="about-text">
                    <h2>Sứ Mệnh Của Chúng Tôi</h2>
                    <p>Chúng tôi cam kết mang đến những trải nghiệm mua sắm tuyệt vời và những sản phẩm thời trang chất lượng cao. Mỗi sản phẩm của LUXE Fashion đều được chọn lọc kỹ lưỡng, đảm bảo tính thẩm mỹ và độ bền vững.</p>
                    <p>Chúng tôi không chỉ bán quần áo - chúng tôi mang đến phong cách sống và sự tự tin cho mỗi khách hàng.</p>
                </div>
                <div class="about-image">
                    <img src="https://js0fpsb45jobj.vcdn.cloud/storage/upload/media/nam-moi-2024/thang-42025/1600x635-1.jpg" alt="LUXE Fashion Mission">
                </div>
            </div>

            <!-- Our Values Section -->
            <div class="about-values">
                <h2>Giá Trị Cốt Lõi</h2>
                <div class="values-grid">
                    <div class="value-item">
                        <i class="fas fa-gem"></i>
                        <h3>Chất Lượng</h3>
                        <p>Cam kết mang đến những sản phẩm chất lượng cao nhất</p>
                    </div>
                    <div class="value-item">
                        <i class="fas fa-heart"></i>
                        <h3>Đam Mê</h3>
                        <p>Nhiệt huyết với thời trang và phong cách</p>
                    </div>
                    <div class="value-item">
                        <i class="fas fa-handshake"></i>
                        <h3>Uy Tín</h3>
                        <p>Xây dựng niềm tin với khách hàng</p>
                    </div>
                    <div class="value-item">
                        <i class="fas fa-leaf"></i>
                        <h3>Bền Vững</h3>
                        <p>Hướng đến phát triển bền vững</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.about-section {
    padding: 0;
    background-color: #fff;
}

.about-hero {
    background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('public/client/images/about-hero.jpg');
    background-size: cover;
    background-position: center;
    height: 400px;
    display: flex;
    align-items: center;
    text-align: center;
    color: #fff;
}

.about-title {
    font-family: 'Playfair Display', serif;
    font-size: 3.5rem;
    margin-bottom: 1rem;
}

.about-subtitle {
    font-size: 1.5rem;
    font-weight: 300;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.about-content {
    padding: 80px 0;
}

.about-block {
    display: flex;
    align-items: center;
    gap: 50px;
    margin-bottom: 80px;
}

.about-block.reverse {
    flex-direction: row-reverse;
}

.about-text {
    flex: 1;
}

.about-text h2 {
    font-family: 'Playfair Display', serif;
    font-size: 2.5rem;
    margin-bottom: 20px;
    color: #333;
}

.about-text p {
    font-size: 1.1rem;
    line-height: 1.8;
    color: #666;
    margin-bottom: 20px;
}

.about-image {
    flex: 1;
}

.about-image img {
    width: 100%;
    height: auto;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.about-values {
    text-align: center;
    padding: 60px 0;
}

.about-values h2 {
    font-family: 'Playfair Display', serif;
    font-size: 2.5rem;
    margin-bottom: 50px;
    color: #333;
}

.values-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
    margin-top: 40px;
}

.value-item {
    padding: 30px;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    transition: transform 0.3s ease;
}

.value-item:hover {
    transform: translateY(-10px);
}

.value-item i {
    font-size: 2.5rem;
    color: #d4af37;
    margin-bottom: 20px;
}

.value-item h3 {
    font-family: 'Playfair Display', serif;
    font-size: 1.5rem;
    margin-bottom: 15px;
    color: #333;
}

.value-item p {
    color: #666;
    line-height: 1.6;
}

@media (max-width: 768px) {
    .about-block {
        flex-direction: column;
    }
    
    .about-block.reverse {
        flex-direction: column;
    }
    
    .about-hero {
        height: 300px;
    }
    
    .about-title {
        font-size: 2.5rem;
    }
    
    .about-subtitle {
        font-size: 1.2rem;
    }
}
</style>
