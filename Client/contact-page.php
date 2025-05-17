<!-- Contact Page Section -->
<section class="contact-section">
    <div class="contact-hero">
        <div class="container">
            <h1 class="contact-title">Liên Hệ</h1>
            <p class="contact-subtitle">Chúng tôi luôn sẵn sàng lắng nghe ý kiến của bạn</p>
        </div>
    </div>

    <div class="contact-content">
        <div class="container">
            <div class="contact-grid">
                <!-- Contact Information -->
                <div class="contact-info">
                    <h2>Thông Tin Liên Hệ</h2>
                    <div class="info-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div>
                            <h3>Địa Chỉ</h3>
                            <p>123 Đường Nguyễn Huệ, Quận 1, TP.HCM</p>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-phone"></i>
                        <div>
                            <h3>Điện Thoại</h3>
                            <p>+84 28 1234 5678</p>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-envelope"></i>
                        <div>
                            <h3>Email</h3>
                            <p>info@luxefashion.com</p>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-clock"></i>
                        <div>
                            <h3>Giờ Làm Việc</h3>
                            <p>Thứ 2 - Thứ 6: 9:00 - 21:00</p>
                            <p>Thứ 7 - Chủ Nhật: 10:00 - 20:00</p>
                        </div>
                    </div>
                </div>

                <!-- Contact Form -->
                <div class="contact-form">
                    <h2>Gửi Tin Nhắn Cho Chúng Tôi</h2>
                    <form id="contactForm" action="#" method="POST">
                        <div class="form-group">
                            <input type="text" id="name" name="name" required>
                            <label for="name">Họ và Tên</label>
                        </div>
                        <div class="form-group">
                            <input type="email" id="email" name="email" required>
                            <label for="email">Email</label>
                        </div>
                        <div class="form-group">
                            <input type="tel" id="phone" name="phone" required>
                            <label for="phone">Số Điện Thoại</label>
                        </div>
                        <div class="form-group">
                            <textarea id="message" name="message" required></textarea>
                            <label for="message">Nội Dung</label>
                        </div>
                        <button type="submit" class="submit-btn">Gửi Tin Nhắn</button>
                    </form>
                </div>
            </div>

            <!-- Map Section -->
            <div class="map-section">
                <h2>Vị Trí Của Chúng Tôi</h2>
                <div class="map-container">
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.4241674814687!2d106.6981!3d10.7756!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMTDCsDQ2JzMyLjEiTiAxMDbCsDQxJzUzLjIiRQ!5e0!3m2!1svi!2s!4v1620000000000!5m2!1svi!2s" 
                        width="100%" 
                        height="450" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy">
                    </iframe>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.contact-section {
    background-color: #fff;
}

.contact-hero {
    background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('https://js0fpsb45jobj.vcdn.cloud/storage/upload/media/nam-moi-2024/thang-42025/1600x635-2.jpg');
    background-size: cover;
    background-position: center;
    height: 300px;
    display: flex;
    align-items: center;
    text-align: center;
    color: #fff;
}

.contact-title {
    font-family: 'Playfair Display', serif;
    font-size: 3.5rem;
    margin-bottom: 1rem;
}

.contact-subtitle {
    font-size: 1.2rem;
    font-weight: 300;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.contact-content {
    padding: 80px 0;
}

.contact-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 50px;
    margin-bottom: 80px;
}

/* Contact Info Styles */
.contact-info {
    padding: 30px;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
}

.contact-info h2 {
    font-family: 'Playfair Display', serif;
    font-size: 2rem;
    margin-bottom: 30px;
    color: #333;
}

.info-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 25px;
}

.info-item i {
    font-size: 1.5rem;
    color: #d4af37;
    margin-right: 20px;
    margin-top: 5px;
}

.info-item h3 {
    font-size: 1.2rem;
    margin-bottom: 5px;
    color: #333;
}

.info-item p {
    color: #666;
    line-height: 1.6;
}

/* Contact Form Styles */
.contact-form {
    padding: 30px;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
}

.contact-form h2 {
    font-family: 'Playfair Display', serif;
    font-size: 2rem;
    margin-bottom: 30px;
    color: #333;
}

.form-group {
    position: relative;
    margin-bottom: 25px;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 10px 0;
    font-size: 1rem;
    border: none;
    border-bottom: 2px solid #ddd;
    outline: none;
    background: transparent;
    transition: all 0.3s ease;
}

.form-group textarea {
    min-height: 100px;
    resize: vertical;
}

.form-group label {
    position: absolute;
    top: 10px;
    left: 0;
    font-size: 1rem;
    color: #999;
    pointer-events: none;
    transition: all 0.3s ease;
}

.form-group input:focus,
.form-group textarea:focus {
    border-bottom-color: #d4af37;
}

.form-group input:focus ~ label,
.form-group textarea:focus ~ label,
.form-group input:valid ~ label,
.form-group textarea:valid ~ label {
    top: -20px;
    font-size: 0.8rem;
    color: #d4af37;
}

.submit-btn {
    background: #d4af37;
    color: #fff;
    padding: 12px 30px;
    border: none;
    border-radius: 5px;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.submit-btn:hover {
    background: #b38f2a;
    transform: translateY(-2px);
}

/* Map Section Styles */
.map-section {
    margin-top: 50px;
}

.map-section h2 {
    font-family: 'Playfair Display', serif;
    font-size: 2rem;
    margin-bottom: 30px;
    text-align: center;
    color: #333;
}

.map-container {
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
}

@media (max-width: 768px) {
    .contact-grid {
        grid-template-columns: 1fr;
    }
    
    .contact-hero {
        height: 200px;
    }
    
    .contact-title {
        font-size: 2.5rem;
    }
    
    .contact-subtitle {
        font-size: 1rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('contactForm');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Add your form submission logic here
        alert('Cảm ơn bạn đã liên hệ với chúng tôi! Chúng tôi sẽ phản hồi sớm nhất có thể.');
        form.reset();
    });
});
</script>
