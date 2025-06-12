<?php
// Check if user is logged in
if (!is_logged_in()) {
    flash_message("Please login to book a service", "warning");
    header("Location: index.php?page=login&redirect=booking");
    exit;
}

// Get user information
$user_id = $_SESSION['user_id'];
$user = get_user($user_id);

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $service_id = sanitize_input($_POST['service_id']);
    $barber_id = sanitize_input($_POST['barber_id']);
    $booking_date = sanitize_input($_POST['booking_date']);
    $booking_time = sanitize_input($_POST['booking_time']);
    $address = sanitize_input($_POST['address']);
    $lat = sanitize_input($_POST['lat']);
    $lng = sanitize_input($_POST['lng']);
    $notes = sanitize_input($_POST['notes']);
    
    // Validate input
    $errors = [];
    
    if (empty($service_id)) {
        $errors[] = "Please select a service";
    }
    
    if (empty($barber_id)) {
        $errors[] = "Please select a barber";
    }
    
    if (empty($booking_date)) {
        $errors[] = "Please select a date";
    } else {
        // Check if date is in the future
        $today = date('Y-m-d');
        if ($booking_date < $today) {
            $errors[] = "Booking date must be in the future";
        }
    }
    
    if (empty($booking_time)) {
        $errors[] = "Please select a time";
    }
    
    if (empty($address)) {
        $errors[] = "Please enter your address";
    }
    
    // If no errors, create booking
    if (empty($errors)) {
        // Begin transaction
        global $conn;
        $conn->begin_transaction();
        
        try {
            // Insert booking
            $sql = "INSERT INTO bookings (client_id, barber_id, service_id, booking_date, booking_time, location_address, location_lat, location_lng, notes) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = db_query($sql, [$user_id, $barber_id, $service_id, $booking_date, $booking_time, $address, $lat, $lng, $notes]);
            
            // Get booking ID
            $booking_id = $conn->insert_id;
            
            // Get service price
            $sql = "SELECT price FROM services WHERE id = ?";
            $stmt = db_query($sql, [$service_id]);
            $result = $stmt->get_result();
            $service = $result->fetch_assoc();
            $price = $service['price'];
            
            // Create pending payment
            $sql = "INSERT INTO payments (booking_id, amount, payment_method, status) VALUES (?, ?, 'card', 'pending')";
            $stmt = db_query($sql, [$booking_id, $price]);
            
            // Commit transaction
            $conn->commit();
            
            // Redirect to payment page
            flash_message("Booking created successfully! Please complete your payment.", "success");
            header("Location: index.php?page=payment&booking_id={$booking_id}");
            exit;
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $errors[] = "Booking failed: " . $e->getMessage();
        }
    }
}

// Get services
$services = get_services();

// Get available barbers
$barbers = get_available_barbers();

// Pre-select service if provided in URL
$selected_service = isset($_GET['service']) ? intval($_GET['service']) : '';

// Pre-select barber if provided in URL
$selected_barber = isset($_GET['barber']) ? intval($_GET['barber']) : '';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="booking-form">
                <h2 class="text-center mb-4">Book a Barber</h2>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" class="needs-validation" novalidate>
                    <div class="mb-4">
                        <h5>1. Select a Service</h5>
                        <div class="row g-3">
                            <?php foreach ($services as $service): ?>
                                <div class="col-md-6">
                                    <div class="form-check custom-radio">
                                        <input class="form-check-input" type="radio" name="service_id" id="service_<?php echo $service['id']; ?>" value="<?php echo $service['id']; ?>" <?php echo ($selected_service == $service['id']) ? 'checked' : ''; ?> required>
                                        <label class="form-check-label d-flex justify-content-between align-items-center" for="service_<?php echo $service['id']; ?>">
                                            <span>
                                                <strong><?php echo htmlspecialchars($service['name']); ?></strong>
                                                <br>
                                                <small class="text-muted"><?php echo htmlspecialchars($service['duration']); ?> mins</small>
                                            </span>
                                            <span class="service-price"><?php echo format_price($service['price']); ?></span>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <h5>2. Choose a Barber</h5>
                        <div class="row g-3">
                            <?php foreach ($barbers as $barber): ?>
                                <div class="col-md-6">
                                    <div class="form-check custom-radio">
                                        <input class="form-check-input" type="radio" name="barber_id" id="barber_<?php echo $barber['user_id']; ?>" value="<?php echo $barber['user_id']; ?>" <?php echo ($selected_barber == $barber['user_id']) ? 'checked' : ''; ?> required>
                                        <label class="form-check-label d-flex align-items-center" for="barber_<?php echo $barber['user_id']; ?>">
                                            <img src="<?php echo !empty($barber['profile_image']) ? 'uploads/profiles/' . htmlspecialchars($barber['profile_image']) : 'assets/images/barber-placeholder.jpg'; ?>" alt="<?php echo htmlspecialchars($barber['first_name'] . ' ' . $barber['last_name']); ?>" class="rounded-circle me-2" width="50" height="50">
                                            <div>
                                                <strong><?php echo htmlspecialchars($barber['first_name'] . ' ' . $barber['last_name']); ?></strong>
                                                <br>
                                                <div class="barber-rating">
                                                    <?php
                                                    $rating = round($barber['rating'] * 2) / 2; // Round to nearest 0.5
                                                    for ($i = 1; $i <= 5; $i++) {
                                                        if ($i <= $rating) {
                                                            echo '<i class="fas fa-star"></i>';
                                                        } elseif ($i - 0.5 == $rating) {
                                                            echo '<i class="fas fa-star-half-alt"></i>';
                                                        } else {
                                                            echo '<i class="far fa-star"></i>';
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <h5>3. Select Date and Time</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="booking_date" class="form-label">Date</label>
                                <input type="date" class="form-control" id="booking_date" name="booking_date" min="<?php echo date('Y-m-d'); ?>" required>
                                <div class="invalid-feedback">Please select a date.</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="booking_time" class="form-label">Time</label>
                                <input type="time" class="form-control" id="booking_time" name="booking_time" required>
                                <div class="invalid-feedback">Please select a time.</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <h5>4. Your Location</h5>
                        <div class="map-container" id="map"></div>
                        
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" class="form-control" id="address" name="address" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>" required>
                            <div class="invalid-feedback">Please enter your address.</div>
                        </div>
                        
                        <!-- Hidden fields for coordinates -->
                        <input type="hidden" id="lat" name="lat" value="">
                        <input type="hidden" id="lng" name="lng" value="">
                    </div>
                    
                    <div class="mb-4">
                        <h5>5. Additional Notes</h5>
                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Any special instructions or preferences?"></textarea>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">Proceed to Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize map when Google Maps API is loaded
function initMap() {
    // Default location (city center)
    var defaultLocation = {lat: 6.5244, lng: 3.3792}; // Lagos, Nigeria
    
    // Create map
    var map = new google.maps.Map(document.getElementById('map'), {
        zoom: 13,
        center: defaultLocation
    });
    
    // Create marker
    var marker = new google.maps.Marker({
        position: defaultLocation,
        map: map,
        draggable: true
    });
    
    // Update coordinates when marker is dragged
    google.maps.event.addListener(marker, 'dragend', function() {
        var position = marker.getPosition();
        document.getElementById('lat').value = position.lat();
        document.getElementById('lng').value = position.lng();
        
        // Reverse geocode to get address
        var geocoder = new google.maps.Geocoder();
        geocoder.geocode({'location': position}, function(results, status) {
            if (status === 'OK') {
                if (results[0]) {
                    document.getElementById('address').value = results[0].formatted_address;
                }
            }
        });
    });
    
    // Create search box
    var input = document.getElementById('address');
    var searchBox = new google.maps.places.SearchBox(input);
    
    // Bias the SearchBox results towards current map's viewport
    map.addListener('bounds_changed', function() {
        searchBox.setBounds(map.getBounds());
    });
    
    // Listen for the event fired when the user selects a prediction and retrieve
    // more details for that place
    searchBox.addListener('places_changed', function() {
        var places = searchBox.getPlaces();
        
        if (places.length === 0) {
            return;
        }
        
        // For each place, get the location
        var bounds = new google.maps.LatLngBounds();
        places.forEach(function(place) {
            if (!place.geometry) {
                console.log("Returned place contains no geometry");
                return;
            }
            
            // Update marker position
            marker.setPosition(place.geometry.location);
            
            // Update hidden fields
            document.getElementById('lat').value = place.geometry.location.lat();
            document.getElementById('lng').value = place.geometry.location.lng();
            
            // Update map
            if (place.geometry.viewport) {
                bounds.union(place.geometry.viewport);
            } else {
                bounds.extend(place.geometry.location);
            }
        });
        map.fitBounds(bounds);
    });
    
    // Try HTML5 geolocation
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            var pos = {
                lat: position.coords.latitude,
                lng: position.coords.longitude
            };
            
            // Update marker and map
            marker.setPosition(pos);
            map.setCenter(pos);
            
            // Update hidden fields
            document.getElementById('lat').value = pos.lat;
            document.getElementById('lng').value = pos.lng;
            
            // Reverse geocode to get address
            var geocoder = new google.maps.Geocoder();
            geocoder.geocode({'location': pos}, function(results, status) {
                if (status === 'OK') {
                    if (results[0]) {
                        document.getElementById('address').value = results[0].formatted_address;
                    }
                }
            });
        }, function() {
            // Handle geolocation error
            console.log('Error: The Geolocation service failed.');
        });
    } else {
        // Browser doesn't support Geolocation
        console.log('Error: Your browser doesn\'t support geolocation.');
    }
}
</script>