</main>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-3 mb-md-0">
                    <h5>Mobile Barber</h5>
                    <p>On-demand barbing services at your doorstep. Professional barbers, quality service, and convenience.</p>
                    <div class="social-icons">
                        <a href="#" class="text-white me-2"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white me-2"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white me-2"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="col-md-4 mb-3 mb-md-0">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-white">Home</a></li>
                        <li><a href="index.php?page=services" class="text-white">Services</a></li>
                        <li><a href="index.php?page=barbers" class="text-white">Barbers</a></li>
                        <li><a href="index.php?page=booking" class="text-white">Book Now</a></li>
                        <li><a href="#" class="text-white">About Us</a></li>
                        <li><a href="#" class="text-white">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Contact Us</h5>
                    <address>
                        <p><i class="fas fa-map-marker-alt me-2"></i> 123 Barber Street, City</p>
                        <p><i class="fas fa-phone me-2"></i> +1 234 567 8901</p>
                        <p><i class="fas fa-envelope me-2"></i> info@mobilebarber.com</p>
                    </address>
                </div>
            </div>
            <hr class="my-4">
            <div class="row">
                <div class="col-md-6 mb-3 mb-md-0">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> Mobile Barber. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0"><a href="#" class="text-white">Privacy Policy</a> | <a href="#" class="text-white">Terms of Service</a></p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Custom JavaScript -->
    <script src="assets/js/main.js"></script>
    
    <?php if (isset($page) && $page == 'booking'): ?>
    <!-- Google Maps API for booking page -->
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=places&callback=initMap" async defer></script>
    <script src="assets/js/booking.js"></script>
    <?php endif; ?>
    
    <?php if (isset($page) && $page == 'chat'): ?>
    <!-- Socket.io for real-time chat -->
    <script src="https://cdn.socket.io/4.4.1/socket.io.min.js"></script>
    <script src="assets/js/chat.js"></script>
    <?php endif; ?>
    
    <?php if (isset($page) && $page == 'payment'): ?>
    <!-- Payment gateway integration -->
    <script src="https://js.paystack.co/v1/inline.js"></script>
    <script src="assets/js/payment.js"></script>
    <?php endif; ?>

    <script src="assets/js/testimonial-carousel.js"></script>
</body>
</html>