// Main JavaScript for PhotoShare
class PhotoShare {
    constructor() {
        this.currentFilter = 'all';
        this.currentPage = 1;
        this.imagesPerPage = 12;
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadGallery();
        this.setupMobileMenu();
        this.setupSmoothScrolling();
        this.setupIntersectionObserver();
    }

    bindEvents() {
        // Modal events
        document.getElementById('loginBtn').addEventListener('click', () => this.showModal('loginModal'));
        document.getElementById('registerBtn').addEventListener('click', () => this.showModal('registerModal'));
        document.getElementById('closeLoginModal').addEventListener('click', () => this.hideModal('loginModal'));
        document.getElementById('closeRegisterModal').addEventListener('click', () => this.hideModal('registerModal'));

        // Form submissions
        document.getElementById('loginForm').addEventListener('submit', (e) => this.handleLogin(e));
        document.getElementById('registerForm').addEventListener('submit', (e) => this.handleRegister(e));

        // Upload events
        document.getElementById('startUploadBtn').addEventListener('click', () => this.scrollToSection('upload'));
        document.getElementById('exploreBtn').addEventListener('click', () => this.scrollToSection('gallery'));
        document.getElementById('loadMoreBtn').addEventListener('click', () => this.loadMoreImages());

        // File upload
        this.setupFileUpload();

        // Gallery filters
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', (e) => this.filterGallery(e.target.dataset.filter));
        });

        // Close modals on outside click
        window.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal')) {
                e.target.classList.remove('active');
            }
        });

        // Navigation active state
        this.setupNavigation();
    }

    setupMobileMenu() {
        const navToggle = document.getElementById('navToggle');
        const navMenu = document.querySelector('.nav-menu');
        const navActions = document.querySelector('.nav-actions');

        navToggle.addEventListener('click', () => {
            navMenu.classList.toggle('active');
            navActions.classList.toggle('active');
            navToggle.classList.toggle('active');
        });
    }

    setupSmoothScrolling() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    }

    setupIntersectionObserver() {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate');
                }
            });
        }, observerOptions);

        // Observe elements for animation
        document.querySelectorAll('.feature-card, .gallery-item').forEach(el => {
            observer.observe(el);
        });
    }

    setupFileUpload() {
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('fileInput');
        const uploadProgress = document.getElementById('uploadProgress');
        const progressFill = document.getElementById('progressFill');
        const progressPercent = document.getElementById('progressPercent');

        // Drag and drop
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            const files = e.dataTransfer.files;
            this.handleFiles(files);
        });

        // File input change
        fileInput.addEventListener('change', (e) => {
            this.handleFiles(e.target.files);
        });

        // Click to upload
        uploadArea.addEventListener('click', () => {
            fileInput.click();
        });
    }

    handleFiles(files) {
        if (files.length === 0) return;

        // Show progress
        document.querySelector('.upload-content').style.display = 'none';
        document.getElementById('uploadProgress').style.display = 'block';

        // Simulate upload progress
        let progress = 0;
        const interval = setInterval(() => {
            progress += Math.random() * 20;
            if (progress >= 100) {
                progress = 100;
                clearInterval(interval);
                setTimeout(() => {
                    this.uploadComplete();
                }, 500);
            }
            progressFill.style.width = progress + '%';
            progressPercent.textContent = Math.round(progress) + '%';
        }, 200);

        // Process files
        Array.from(files).forEach(file => {
            if (file.type.startsWith('image/')) {
                this.processImage(file);
            }
        });
    }

    processImage(file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            // Create preview
            const img = new Image();
            img.src = e.target.result;
            img.onload = () => {
                // Add to gallery
                this.addImageToGallery({
                    id: Date.now(),
                    src: e.target.result,
                    title: file.name,
                    category: this.detectCategory(file.name),
                    uploadDate: new Date().toLocaleDateString(),
                    size: this.formatFileSize(file.size)
                });
            };
        };
        reader.readAsDataURL(file);
    }

    detectCategory(filename) {
        const name = filename.toLowerCase();
        if (name.includes('nature') || name.includes('landscape') || name.includes('forest')) return 'nature';
        if (name.includes('city') || name.includes('urban') || name.includes('street')) return 'city';
        if (name.includes('portrait') || name.includes('person') || name.includes('face')) return 'portrait';
        return 'other';
    }

    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    uploadComplete() {
        document.getElementById('uploadProgress').style.display = 'none';
        document.querySelector('.upload-content').style.display = 'block';
        
        // Show success message
        this.showNotification('Фотографии успешно загружены!', 'success');
        
        // Reset file input
        document.getElementById('fileInput').value = '';
    }

    addImageToGallery(imageData) {
        const galleryGrid = document.getElementById('galleryGrid');
        const imageElement = this.createImageElement(imageData);
        galleryGrid.appendChild(imageElement);
    }

    createImageElement(imageData) {
        const div = document.createElement('div');
        div.className = 'gallery-item';
        div.dataset.category = imageData.category;
        
        div.innerHTML = `
            <img src="${imageData.src}" alt="${imageData.title}" loading="lazy">
            <div class="gallery-item-info">
                <div class="gallery-item-title">${imageData.title}</div>
                <div class="gallery-item-meta">
                    <span>${imageData.uploadDate}</span>
                    <span>${imageData.size}</span>
                </div>
            </div>
        `;

        // Add click event for full view
        div.addEventListener('click', () => this.showImageFullscreen(imageData));
        
        return div;
    }

    showImageFullscreen(imageData) {
        const modal = document.createElement('div');
        modal.className = 'modal active';
        modal.innerHTML = `
            <div class="modal-content image-modal">
                <div class="modal-header">
                    <h3>${imageData.title}</h3>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <img src="${imageData.src}" alt="${imageData.title}" style="width: 100%; max-height: 70vh; object-fit: contain;">
                    <div class="image-actions" style="margin-top: 1rem; display: flex; gap: 1rem; justify-content: center;">
                        <button class="btn btn-primary" onclick="this.downloadImage('${imageData.src}', '${imageData.title}')">
                            <i class="fas fa-download"></i> Скачать
                        </button>
                        <button class="btn btn-outline" onclick="this.shareImage('${imageData.src}', '${imageData.title}')">
                            <i class="fas fa-share-alt"></i> Поделиться
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        // Close modal
        modal.querySelector('.modal-close').addEventListener('click', () => {
            modal.remove();
        });

        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.remove();
            }
        });
    }

    downloadImage(src, filename) {
        const link = document.createElement('a');
        link.href = src;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    shareImage(src, title) {
        if (navigator.share) {
            navigator.share({
                title: title,
                url: src
            });
        } else {
            // Fallback: copy to clipboard
            navigator.clipboard.writeText(src).then(() => {
                this.showNotification('Ссылка скопирована в буфер обмена!', 'success');
            });
        }
    }

    filterGallery(category) {
        this.currentFilter = category;
        this.currentPage = 1;
        
        // Update active filter button
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.filter === category);
        });

        // Filter images
        document.querySelectorAll('.gallery-item').forEach(item => {
            if (category === 'all' || item.dataset.category === category) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    }

    loadGallery() {
        // Sample images for demonstration
        const sampleImages = [
            {
                id: 1,
                src: 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=400',
                title: 'Горный пейзаж',
                category: 'nature',
                uploadDate: '2024-01-15',
                size: '2.3 MB'
            },
            {
                id: 2,
                src: 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=400',
                title: 'Лесная тропа',
                category: 'nature',
                uploadDate: '2024-01-14',
                size: '1.8 MB'
            },
            {
                id: 3,
                src: 'https://images.unsplash.com/photo-1519501025264-65ba15a82390?w=400',
                title: 'Городская архитектура',
                category: 'city',
                uploadDate: '2024-01-13',
                size: '3.1 MB'
            },
            {
                id: 4,
                src: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400',
                title: 'Портрет мужчины',
                category: 'portrait',
                uploadDate: '2024-01-12',
                size: '2.7 MB'
            },
            {
                id: 5,
                src: 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=400',
                title: 'Озеро в горах',
                category: 'nature',
                uploadDate: '2024-01-11',
                size: '2.9 MB'
            },
            {
                id: 6,
                src: 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=400',
                title: 'Улица города',
                category: 'city',
                uploadDate: '2024-01-10',
                size: '2.1 MB'
            }
        ];

        sampleImages.forEach(image => {
            this.addImageToGallery(image);
        });
    }

    loadMoreImages() {
        // Simulate loading more images
        const loadMoreBtn = document.getElementById('loadMoreBtn');
        loadMoreBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Загрузка...';
        loadMoreBtn.disabled = true;

        setTimeout(() => {
            // Add more sample images
            const moreImages = [
                {
                    id: Date.now() + 1,
                    src: 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=400',
                    title: 'Новый пейзаж',
                    category: 'nature',
                    uploadDate: new Date().toLocaleDateString(),
                    size: '2.5 MB'
                },
                {
                    id: Date.now() + 2,
                    src: 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=400',
                    title: 'Еще один вид',
                    category: 'city',
                    uploadDate: new Date().toLocaleDateString(),
                    size: '1.9 MB'
                }
            ];

            moreImages.forEach(image => {
                this.addImageToGallery(image);
            });

            loadMoreBtn.innerHTML = '<i class="fas fa-plus"></i> Загрузить еще';
            loadMoreBtn.disabled = false;
        }, 1500);
    }

    showModal(modalId) {
        document.getElementById(modalId).classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    hideModal(modalId) {
        document.getElementById(modalId).classList.remove('active');
        document.body.style.overflow = 'auto';
    }

    handleLogin(e) {
        e.preventDefault();
        const email = document.getElementById('loginEmail').value;
        const password = document.getElementById('loginPassword').value;

        // Simulate login
        if (email && password) {
            this.showNotification('Вход выполнен успешно!', 'success');
            this.hideModal('loginModal');
            document.getElementById('loginForm').reset();
        } else {
            this.showNotification('Пожалуйста, заполните все поля', 'error');
        }
    }

    handleRegister(e) {
        e.preventDefault();
        const name = document.getElementById('registerName').value;
        const email = document.getElementById('registerEmail').value;
        const password = document.getElementById('registerPassword').value;
        const passwordConfirm = document.getElementById('registerPasswordConfirm').value;

        if (password !== passwordConfirm) {
            this.showNotification('Пароли не совпадают', 'error');
            return;
        }

        if (name && email && password) {
            this.showNotification('Регистрация выполнена успешно!', 'success');
            this.hideModal('registerModal');
            document.getElementById('registerForm').reset();
        } else {
            this.showNotification('Пожалуйста, заполните все поля', 'error');
        }
    }

    scrollToSection(sectionId) {
        const section = document.getElementById(sectionId);
        if (section) {
            section.scrollIntoView({ behavior: 'smooth' });
        }
    }

    setupNavigation() {
        const sections = document.querySelectorAll('section[id]');
        const navLinks = document.querySelectorAll('.nav-link');

        window.addEventListener('scroll', () => {
            let current = '';
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;
                if (scrollY >= (sectionTop - 200)) {
                    current = section.getAttribute('id');
                }
            });

            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === `#${current}`) {
                    link.classList.add('active');
                }
            });
        });
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                <span>${message}</span>
            </div>
        `;

        // Add styles
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6'};
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 3000;
            transform: translateX(100%);
            transition: transform 0.3s ease;
        `;

        document.body.appendChild(notification);

        // Animate in
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);

        // Remove after 3 seconds
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new PhotoShare();
});

// Add some CSS for notifications
const notificationStyles = document.createElement('style');
notificationStyles.textContent = `
    .notification-content {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .notification-content i {
        font-size: 1.2rem;
    }
    
    @media (max-width: 768px) {
        .notification {
            right: 10px;
            left: 10px;
            transform: translateY(-100%);
        }
        
        .notification.show {
            transform: translateY(0);
        }
    }
`;
document.head.appendChild(notificationStyles);