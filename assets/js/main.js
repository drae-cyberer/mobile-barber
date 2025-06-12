/**
 * Mobile Barber Platform - Main JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Auto-hide flash messages after 5 seconds
    setTimeout(function() {
        var flashMessages = document.querySelectorAll('.alert');
        flashMessages.forEach(function(alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
    
    // Testimonial slider functionality
    
    // Initialize testimonial slider

    // Mobile search toggle functionality - only for mobile view
    const mobileSearchToggle = document.getElementById('mobileSearchToggle');
    const mobileSearchContainer = document.querySelector('.mobile-search-container');
    
    if (mobileSearchToggle && mobileSearchContainer) {
        mobileSearchToggle.addEventListener('click', function() {
            // Only toggle the active class on mobile devices
            if (window.innerWidth <= 767.98) {
                mobileSearchContainer.classList.toggle('active');
                // Close menu if open
                const navbarNav = document.getElementById('navbarNav');
                if (navbarNav && navbarNav.classList.contains('show')) {
                    document.querySelector('.navbar-toggler').click();
                }
            }
        });
        
        // Close search when clicking outside
        document.addEventListener('click', function(event) {
            if (mobileSearchContainer.classList.contains('active')) {
                if (!mobileSearchContainer.contains(event.target) && event.target !== mobileSearchToggle) {
                    mobileSearchContainer.classList.remove('active');
                }
            }
        });
    }

    // Mobile menu toggle - updated for new structure
    var navbarToggler = document.querySelector('.navbar-toggler:not(.original-toggler)');
    if (navbarToggler) {
        navbarToggler.addEventListener('click', function() {
            document.querySelector('.navbar-collapse').classList.toggle('show');
            
            // Close search when opening menu
            if (mobileSearchContainer && mobileSearchContainer.classList.contains('active')) {
                mobileSearchContainer.classList.remove('active');
            }
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            const navbarCollapse = document.querySelector('.navbar-collapse');
            if (navbarCollapse && navbarCollapse.classList.contains('show')) {
                if (!navbarCollapse.contains(event.target) && event.target !== navbarToggler) {
                    navbarCollapse.classList.remove('show');
                }
            }
        });
    }

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });

    // Form validation
    var forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // Service filter functionality
    const serviceFilter = document.getElementById('service-filter');
    if (serviceFilter) {
        serviceFilter.addEventListener('change', function() {
            const category = this.value;
            const serviceCards = document.querySelectorAll('.service-card');
            
            serviceCards.forEach(card => {
                if (category === 'all' || card.dataset.category === category) {
                    card.closest('.col').style.display = 'block';
                } else {
                    card.closest('.col').style.display = 'none';
                }
            });
        });
    }

    // Barber filter functionality
    const barberFilter = document.getElementById('barber-filter');
    if (barberFilter) {
        barberFilter.addEventListener('change', function() {
            const rating = parseFloat(this.value);
            const barberCards = document.querySelectorAll('.barber-card');
            
            barberCards.forEach(card => {
                const barberRating = parseFloat(card.dataset.rating);
                if (barberRating >= rating) {
                    card.closest('.col').style.display = 'block';
                } else {
                    card.closest('.col').style.display = 'none';
                }
            });
        });
    }

    // Password strength meter
    const passwordInput = document.getElementById('password');
    const passwordStrength = document.getElementById('password-strength');
    
    if (passwordInput && passwordStrength) {
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            if (password.length >= 8) strength += 1;
            if (password.match(/[a-z]+/)) strength += 1;
            if (password.match(/[A-Z]+/)) strength += 1;
            if (password.match(/[0-9]+/)) strength += 1;
            if (password.match(/[^a-zA-Z0-9]+/)) strength += 1;
            
            switch (strength) {
                case 0:
                case 1:
                    passwordStrength.className = 'progress-bar bg-danger';
                    passwordStrength.style.width = '20%';
                    passwordStrength.textContent = 'Very Weak';
                    break;
                case 2:
                    passwordStrength.className = 'progress-bar bg-warning';
                    passwordStrength.style.width = '40%';
                    passwordStrength.textContent = 'Weak';
                    break;
                case 3:
                    passwordStrength.className = 'progress-bar bg-info';
                    passwordStrength.style.width = '60%';
                    passwordStrength.textContent = 'Medium';
                    break;
                case 4:
                    passwordStrength.className = 'progress-bar bg-primary';
                    passwordStrength.style.width = '80%';
                    passwordStrength.textContent = 'Strong';
                    break;
                case 5:
                    passwordStrength.className = 'progress-bar bg-success';
                    passwordStrength.style.width = '100%';
                    passwordStrength.textContent = 'Very Strong';
                    break;
            }
        });
    }

    // Back to top button
    const backToTopBtn = document.getElementById('back-to-top');
    if (backToTopBtn) {
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTopBtn.style.display = 'block';
            } else {
                backToTopBtn.style.display = 'none';
            }
        });

        backToTopBtn.addEventListener('click', function() {
            window.scrollTo({top: 0, behavior: 'smooth'});
        });
    }
});