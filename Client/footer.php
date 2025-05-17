<footer class="footer">
        <div class="footer-content">
            <div class="footer-column">
                <h3>Về Chúng Tôi</h3>
                <?php $system = \App\Models\System::first(); ?>
                <p> <?= $system->site_name?> - Thương hiệu thời trang cao cấp, mang đến những trải nghiệm mua sắm tuyệt vời cho khách hàng.</p>
            </div>
            <div class="footer-column">
                <h3>Liên Kết</h3>
                <ul>
                    <li><a href="#">Trang Chủ</a></li>
                    <li><a href="#">Sản Phẩm</a></li>
                    <li><a href="#">Bộ Sưu Tập</a></li>
                    <li><a href="#">Về Chúng Tôi</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h3>Liên Hệ</h3>
                <ul>
                    <li>Email: <?= $system->email?></li>
                    <li>Phone: <?= $system->hotline?></li>
                    <li>Địa chỉ: <?= $system->address?></li>
                </ul>
            </div>
            <div class="footer-column">
                <h3>Theo Dõi Chúng Tôi</h3>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 <?= $system->site_name?>. All rights reserved.</p>
        </div>
</footer>