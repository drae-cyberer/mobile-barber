// Simple Mobile Testimonial Auto-Carousel - 60 seconds per slide
document.addEventListener('DOMContentLoaded', function() {
    // Only run on mobile devices
    function isMobile() {
        return window.innerWidth <= 767.98;
    }
    
    if (!isMobile()) return;
    
    const testimonialContainer = document.querySelector('.testimonials .testimonial-container');
    const testimonialRow = testimonialContainer?.querySelector('.row');
    const testimonialItems = testimonialContainer?.querySelectorAll('.testimonial-item');
    const dots = testimonialContainer?.querySelectorAll('.testimonial-dot');
    const navigation = testimonialContainer?.querySelector('.testimonial-nav');
    
    if (!testimonialContainer || !testimonialRow || !testimonialItems.length) return;
    
    let currentIndex = 0;
    let autoPlayInterval;
    
    // Hide all navigation controls
    if (navigation) navigation.style.display = 'none';
    if (dots.length > 0) {
        dots.forEach(dot => dot.style.display = 'none');
    }
    
    // Initialize carousel
    function initCarousel() {
        // Set up the container
        testimonialContainer.style.overflow = 'hidden';
        testimonialContainer.style.position = 'relative';
        
        // Set up the row for sliding
        testimonialRow.style.display = 'flex';
        testimonialRow.style.flexWrap = 'nowrap';
        testimonialRow.style.width = '300%'; // 3 testimonials * 100%
        testimonialRow.style.transition = 'transform 1s ease-in-out';
        testimonialRow.style.margin = '0';
        
        // Set up each testimonial item
        testimonialItems.forEach((item, index) => {
            item.style.flex = '0 0 33.333%';
            item.style.width = '33.333%';
            item.style.display = 'flex';
            item.style.justifyContent = 'center';
            item.style.padding = '0 15px';
        });
        
        // Start auto-rotation
        startAutoRotation();
    }
    
    // Start auto-rotation every 60 seconds
    function startAutoRotation() {
        autoPlayInterval = setInterval(() => {
            nextSlide();
        }, 60000); // 60 seconds = 60,000 milliseconds
    }
    
    // Go to next slide
    function nextSlide() {
        currentIndex = (currentIndex + 1) % testimonialItems.length;
        updateSlide();
    }
    
    // Update slide position
    function updateSlide() {
        const translateX = -currentIndex * 33.333; // Move by 33.333% for each slide
        testimonialRow.style.transform = `translateX(${translateX}%)`;
    }
    
    // Handle window resize
    window.addEventListener('resize', () => {
        if (!isMobile() && autoPlayInterval) {
            clearInterval(autoPlayInterval);
            autoPlayInterval = null;
            // Reset styles for desktop
            testimonialRow.style.transform = 'none';
            testimonialRow.style.width = 'auto';
            testimonialItems.forEach(item => {
                item.style.flex = 'auto';
                item.style.width = 'auto';
            });
        } else if (isMobile() && !autoPlayInterval) {
            initCarousel();
        }
    });
    
    // Pause on visibility change (when tab is not active)
    document.addEventListener('visibilitychange', () => {
        if (document.hidden && autoPlayInterval) {
            clearInterval(autoPlayInterval);
        } else if (!document.hidden && isMobile()) {
            if (autoPlayInterval) clearInterval(autoPlayInterval);
            startAutoRotation();
        }
    });
    
    // Initialize the carousel
    initCarousel();
});