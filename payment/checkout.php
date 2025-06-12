<?php
// Check if user is logged in
if (!is_logged_in()) {
    flash_message("Please login to complete payment", "warning");
    header("Location: index.php?page=login&redirect=payment");
    exit;
}

// Get booking ID from URL
$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;

// Validate booking ID
if ($booking_id <= 0) {
    flash_message("Invalid booking ID", "danger");
    header("Location: index.php?page=home");
    exit;
}

// Get user information
$user_id = $_SESSION['user_id'];
$user = get_user($user_id);

// Get booking information
$sql = "SELECT b.*, s.name as service_name, s.price, s.duration, 
        u.first_name as barber_first_name, u.last_name as barber_last_name, u.profile_image as barber_image 
        FROM bookings b 
        JOIN services s ON b.service_id = s.id 
        JOIN users u ON b.barber_id = u.id 
        WHERE b.id = ? AND b.client_id = ?";
$stmt = db_query($sql, [$booking_id, $user_id]);
$result = $stmt->get_result();

// Check if booking exists and belongs to user
if ($result->num_rows === 0) {
    flash_message("Booking not found or unauthorized", "danger");
    header("Location: index.php?page=home");
    exit;
}

$booking = $result->fetch_assoc();

// Get payment information
$sql = "SELECT * FROM payments WHERE booking_id = ?";
$stmt = db_query($sql, [$booking_id]);
$result = $stmt->get_result();
$payment = $result->fetch_assoc();

// Check if payment is already completed
if ($payment['status'] === 'completed') {
    flash_message("Payment already completed", "info");
    header("Location: index.php?page=profile");
    exit;
}

// Process payment if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $payment_method = sanitize_input($_POST['payment_method']);
    $transaction_id = 'TXN' . time() . rand(1000, 9999); // Generate transaction ID
    
    // Validate input
    $errors = [];
    
    if (empty($payment_method)) {
        $errors[] = "Please select a payment method";
    }
    
    // If no errors, process payment
    if (empty($errors)) {
        // In a real application, you would integrate with a payment gateway here
        // For demonstration purposes, we'll simulate a successful payment
        
        // Update payment status
        $sql = "UPDATE payments SET payment_method = ?, transaction_id = ?, status = 'completed' WHERE booking_id = ?";
        $stmt = db_query($sql, [$payment_method, $transaction_id, $booking_id]);
        
        // Update booking status
        $sql = "UPDATE bookings SET status = 'confirmed' WHERE id = ?";
        $stmt = db_query($sql, [$booking_id]);
        
        // Set success message
        flash_message("Payment successful! Your booking is confirmed.", "success");
        
        // Redirect to profile page
        header("Location: index.php?page=profile");
        exit;
    }
}
?>

<div class="container py-5 payment-container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-md">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Complete Your Payment</h4>
                </div>
                <div class="card-body p-4">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Booking Details</h5>
                            <p><strong>Service:</strong> <?php echo htmlspecialchars($booking['service_name']); ?></p>
                            <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($booking['booking_date'])); ?></p>
                            <p><strong>Time:</strong> <?php echo date('g:i A', strtotime($booking['booking_time'])); ?></p>
                            <p><strong>Duration:</strong> <?php echo htmlspecialchars($booking['duration']); ?> minutes</p>
                            <p><strong>Location:</strong> <?php echo htmlspecialchars($booking['location_address']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <h5>Barber Information</h5>
                            <div class="d-flex align-items-center mb-3">
                                <img src="<?php echo !empty($booking['barber_image']) ? 'uploads/profiles/' . htmlspecialchars($booking['barber_image']) : 'assets/images/barber-placeholder.jpg'; ?>" alt="<?php echo htmlspecialchars($booking['barber_first_name'] . ' ' . $booking['barber_last_name']); ?>" class="rounded-circle me-3" width="60" height="60">
                                <div>
                                    <h6 class="mb-0"><?php echo htmlspecialchars($booking['barber_first_name'] . ' ' . $booking['barber_last_name']); ?></h6>
                                    <p class="text-muted mb-0">Professional Barber</p>
                                </div>
                            </div>
                            <p><strong>Amount:</strong> <span class="text-primary fw-bold"><?php echo format_price($booking['price']); ?></span></p>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <form method="POST" action="" id="payment-form" class="needs-validation" novalidate>
                        <h5 class="mb-3">Payment Method</h5>
                        
                        <div class="mb-4">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="payment_method" id="card" value="card" checked required>
                                <label class="form-check-label" for="card">
                                    <i class="far fa-credit-card me-2"></i> Credit/Debit Card
                                </label>
                            </div>
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="payment_method" id="bank_transfer" value="bank_transfer" required>
                                <label class="form-check-label" for="bank_transfer">
                                    <i class="fas fa-university me-2"></i> Bank Transfer
                                </label>
                            </div>
                            
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="mobile_money" value="mobile_money" required>
                                <label class="form-check-label" for="mobile_money">
                                    <i class="fas fa-mobile-alt me-2"></i> Mobile Money
                                </label>
                            </div>
                        </div>
                        
                        <!-- Card Payment Form (shown by default) -->
                        <div id="card-payment-form" class="payment-method-form">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="card_number" class="form-label">Card Number</label>
                                    <input type="text" class="form-control" id="card_number" placeholder="1234 5678 9012 3456">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="expiry_date" class="form-label">Expiry Date</label>
                                    <input type="text" class="form-control" id="expiry_date" placeholder="MM/YY">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="cvv" class="form-label">CVV</label>
                                    <input type="text" class="form-control" id="cvv" placeholder="123">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="card_name" class="form-label">Name on Card</label>
                                <input type="text" class="form-control" id="card_name" placeholder="John Doe">
                            </div>
                        </div>
                        
                        <!-- Bank Transfer Form (hidden by default) -->
                        <div id="bank-transfer-form" class="payment-method-form" style="display: none;">
                            <div class="alert alert-info">
                                <p class="mb-0">Please make a transfer to the following bank account:</p>
                                <p class="mb-0"><strong>Bank:</strong> Example Bank</p>
                                <p class="mb-0"><strong>Account Name:</strong> Mobile Barber Ltd</p>
                                <p class="mb-0"><strong>Account Number:</strong> 1234567890</p>
                                <p class="mb-0"><strong>Reference:</strong> BOOKING-<?php echo $booking_id; ?></p>
                            </div>
                        </div>
                        
                        <!-- Mobile Money Form (hidden by default) -->
                        <div id="mobile-money-form" class="payment-method-form" style="display: none;">
                            <div class="mb-3">
                                <label for="phone_number" class="form-label">Phone Number</label>
                                <input type="text" class="form-control" id="phone_number" placeholder="+1234567890">
                            </div>
                            
                            <div class="alert alert-info">
                                <p class="mb-0">You will receive a prompt on your phone to complete the payment.</p>
                            </div>
                        </div>
                        
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="terms" required>
                            <label class="form-check-label" for="terms">
                                I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
                            </label>
                            <div class="invalid-feedback">You must agree before submitting.</div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg" id="pay-button">Pay <?php echo format_price($booking['price']); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Show/hide payment method forms based on selection
        const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
        const paymentForms = document.querySelectorAll('.payment-method-form');
        
        paymentMethods.forEach(method => {
            method.addEventListener('change', function() {
                // Hide all forms
                paymentForms.forEach(form => {
                    form.style.display = 'none';
                });
                
                // Show selected form
                if (this.value === 'card') {
                    document.getElementById('card-payment-form').style.display = 'block';
                } else if (this.value === 'bank_transfer') {
                    document.getElementById('bank-transfer-form').style.display = 'block';
                } else if (this.value === 'mobile_money') {
                    document.getElementById('mobile-money-form').style.display = 'block';
                }
            });
        });
        
        // Initialize PayStack (in a real application)
        // const payButton = document.getElementById('pay-button');
        // payButton.addEventListener('click', function(e) {
        //     e.preventDefault();
        //     
        //     // Validate form
        //     const form = document.getElementById('payment-form');
        //     if (!form.checkValidity()) {
        //         form.classList.add('was-validated');
        //         return;
        //     }
        //     
        //     // Initialize PayStack payment
        //     const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
        //     
        //     if (paymentMethod === 'card') {
        //         const handler = PaystackPop.setup({
        //             key: 'YOUR_PAYSTACK_PUBLIC_KEY',
        //             email: '<?php echo $user["email"]; ?>',
        //             amount: <?php echo $booking["price"] * 100; ?>, // Amount in kobo
        //             currency: 'NGN',
        //             ref: 'BOOKING-<?php echo $booking_id; ?>-' + Math.floor((Math.random() * 1000000000) + 1),
        //             callback: function(response) {
        //                 // Submit form with transaction ID
        //                 const transactionInput = document.createElement('input');
        //                 transactionInput.type = 'hidden';
        //                 transactionInput.name = 'transaction_id';
        //                 transactionInput.value = response.reference;
        //                 form.appendChild(transactionInput);
        //                 form.submit();
        //             },
        //             onClose: function() {
        //                 alert('Transaction was not completed, window closed.');
        //             }
        //         });
        //         handler.openIframe();
        //     } else {
        //         // For other payment methods, submit the form directly
        //         form.submit();
        //     }
        // });
    });
</script>