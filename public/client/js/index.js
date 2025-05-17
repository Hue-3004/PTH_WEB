let currentSlide = 0;
const slides = document.querySelector('.slider-container');
const dots = document.querySelectorAll('.slider-dot');
const totalSlides = 3;

function updateSlider() {
    slides.style.transform = `translateX(-${currentSlide * 33.33}%)`;
    dots.forEach((dot, index) => {
        dot.classList.toggle('active', index === currentSlide);
    });
}

function nextSlide() {
    currentSlide = (currentSlide + 1) % totalSlides;
    updateSlider();
}

function prevSlide() {
    currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
    updateSlider();
}

// Add click events to dots
dots.forEach((dot, index) => {
    dot.addEventListener('click', () => {
        currentSlide = index;
        updateSlider();
    });
});

// Add click events to arrows
document.querySelector('.slider-next').addEventListener('click', nextSlide);
document.querySelector('.slider-prev').addEventListener('click', prevSlide);

// Auto slide
setInterval(nextSlide, 5000);

// Sample product data
const products = [
    {
        name: 'Áo Sơ Mi Trắng',
        price: '350.000 VNĐ',
        image: 'https://images.unsplash.com/photo-1583743814966-8936f5b7be1a',
        category: 'Áo'
    },
    {
        name: 'Quần Jeans Xanh',
        price: '550.000 VNĐ',
        image: 'https://images.unsplash.com/photo-1618354691373-d851c5c3a990',
        category: 'Quần'
    },
    {
        name: 'Áo Khoác Dạ',
        price: '750.000 VNĐ',
        image: 'https://images.unsplash.com/photo-1602293589930-45aad59ba3ab',
        category: 'Áo'
    },
    {
        name: 'Áo Thun Đen',
        price: '250.000 VNĐ',
        image: 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c',
        category: 'Áo'
    },
    {
        name: 'Váy Hoa Nhí',
        price: '450.000 VNĐ',
        image: 'https://images.unsplash.com/photo-1591195853828-11db59a44f6b',
        category: 'Váy'
    },
    {
        name: 'Áo Len Cổ Lọ',
        price: '400.000 VNĐ',
        image: 'https://images.unsplash.com/photo-1608063615781-e2yce903d41c',
        category: 'Áo'
    },
    {
        name: 'Quần Tây Đen',
        price: '500.000 VNĐ',
        image: 'https://images.unsplash.com/photo-1576566588028-4147f3848f70',
        category: 'Quần'
    },
    {
        name: 'Áo Sơ Mi Hồng',
        price: '380.000 VNĐ',
        image: 'https://images.unsplash.com/photo-1583743814966-8936f5b7be1a',
        category: 'Áo'
    },
    {
        name: 'Quần Jeans Đen',
        price: '520.000 VNĐ',
        image: 'https://images.unsplash.com/photo-1618354691373-d851c5c3a990',
        category: 'Quần'
    },
    {
        name: 'Áo Khoác Bomber',
        price: '680.000 VNĐ',
        image: 'https://images.unsplash.com/photo-1602293589930-45aad59ba3ab',
        category: 'Áo'
    },
    {
        name: 'Áo Thun Trắng',
        price: '220.000 VNĐ',
        image: 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c',
        category: 'Áo'
    },
    {
        name: 'Váy Liền Thân',
        price: '420.000 VNĐ',
        image: 'https://images.unsplash.com/photo-1591195853828-11db59a44f6b',
        category: 'Váy'
    },
    {
        name: 'Áo Len Cổ Tim',
        price: '380.000 VNĐ',
        image: 'https://images.unsplash.com/photo-1608063615781-e2yce903d41c',
        category: 'Áo'
    },
    {
        name: 'Quần Tây Xám',
        price: '480.000 VNĐ',
        image: 'https://images.unsplash.com/photo-1576566588028-4147f3848f70',
        category: 'Quần'
    }
];

// Generate product cards
const productGrid = document.querySelector('.product-grid');
function displayProducts(category = 'all') {
    productGrid.innerHTML = '';
    const filteredProducts = category === 'all' 
        ? products 
        : products.filter(product => product.category === category);

    filteredProducts.forEach(product => {
        const productCard = `
            <div class="product-card">
                <div class="product-image">
                    <span class="discount-label">-20%</span>
                    <img src="${product.image}" alt="${product.name}">
                </div>
                <div class="product-info">
                    <h3>${product.name}</h3>
                    <div class="product-price-block">
                        <span class="old-price">450.000 VNĐ</span>
                        <span class="new-price">350.000 VNĐ</span>
                    </div>
                    <button class="add-to-cart">Thêm vào giỏ</button>
                </div>
            </div>
        `;
        productGrid.innerHTML += productCard;
    });
}

// Category tabs functionality
const categoryTabs = document.querySelectorAll('.category-tab');
categoryTabs.forEach(tab => {
    tab.addEventListener('click', () => {
        categoryTabs.forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        const category = tab.textContent === 'Tất Cả' ? 'all' : tab.textContent;
        displayProducts(category);
    });
});

function toggleUserMenu() {
    const userIcon = document.querySelector('.user-icon');
    userIcon.classList.toggle('active');
    const userMenu = document.getElementById('user-menu');
    if (userMenu.style.display === 'block') {
        userMenu.style.display = 'none';
    } else {
        userMenu.style.display = 'block';
    }
}

// Đóng dropdown khi click ra ngoài
window.onclick = function(event) {
    if (!event.target.matches('.user-icon, .user-icon *')) {
        const userMenu = document.getElementById('user-menu');
        if (userMenu) {
            userMenu.style.display = 'none';
        }
    }
}

function toggleMenu() {
    const menu = document.getElementById('menu');
    menu.classList.toggle('responsive');
}

function toggleUserMenu() {
    const userMenu = document.getElementById('user-menu');
    userMenu.style.display = userMenu.style.display === 'block' ? 'none' : 'block';
}
