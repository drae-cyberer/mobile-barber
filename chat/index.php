<?php
// Check if user is logged in
if (!is_logged_in()) {
    flash_message("Please login to access chat", "warning");
    header("Location: index.php?page=login&redirect=chat");
    exit;
}

// Get user information
$user_id = $_SESSION['user_id'];
$user = get_user($user_id);

// Get user role
$is_client = has_role('client');
$is_barber = has_role('barber');
$is_admin = has_role('admin');

// Get chat contacts based on user role
$contacts = [];

if ($is_client) {
    // Get barbers the client has booked with
    $sql = "SELECT DISTINCT u.id, u.first_name, u.last_name, u.profile_image, u.status, 
            (SELECT MAX(created_at) FROM chat_messages 
             WHERE (sender_id = ? AND receiver_id = u.id) OR (sender_id = u.id AND receiver_id = ?)) as last_message_time,
            (SELECT COUNT(*) FROM chat_messages WHERE sender_id = u.id AND receiver_id = ? AND is_read = 0) as unread_count
            FROM users u 
            JOIN barber_profiles bp ON u.id = bp.user_id 
            JOIN bookings b ON bp.user_id = b.barber_id 
            WHERE b.client_id = ? 
            ORDER BY last_message_time DESC NULLS LAST, u.first_name ASC";
    $stmt = db_query($sql, [$user_id, $user_id, $user_id, $user_id]);
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $contacts[] = $row;
    }
    
    // Add admin contact
    $sql = "SELECT u.id, u.first_name, u.last_name, u.profile_image, u.status, 
            (SELECT MAX(created_at) FROM chat_messages 
             WHERE (sender_id = ? AND receiver_id = u.id) OR (sender_id = u.id AND receiver_id = ?)) as last_message_time,
            (SELECT COUNT(*) FROM chat_messages WHERE sender_id = u.id AND receiver_id = ? AND is_read = 0) as unread_count
            FROM users u 
            JOIN user_roles ur ON u.id = ur.user_id 
            WHERE ur.role = 'admin' 
            LIMIT 1";
    $stmt = db_query($sql, [$user_id, $user_id, $user_id]);
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $row['is_admin'] = true;
        $contacts[] = $row;
    }
} elseif ($is_barber) {
    // Get clients who have booked with this barber
    $sql = "SELECT DISTINCT u.id, u.first_name, u.last_name, u.profile_image, u.status, 
            (SELECT MAX(created_at) FROM chat_messages 
             WHERE (sender_id = ? AND receiver_id = u.id) OR (sender_id = u.id AND receiver_id = ?)) as last_message_time,
            (SELECT COUNT(*) FROM chat_messages WHERE sender_id = u.id AND receiver_id = ? AND is_read = 0) as unread_count
            FROM users u 
            JOIN bookings b ON u.id = b.client_id 
            WHERE b.barber_id = ? 
            ORDER BY last_message_time DESC NULLS LAST, u.first_name ASC";
    $stmt = db_query($sql, [$user_id, $user_id, $user_id, $user_id]);
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $contacts[] = $row;
    }
    
    // Add admin contact
    $sql = "SELECT u.id, u.first_name, u.last_name, u.profile_image, u.status, 
            (SELECT MAX(created_at) FROM chat_messages 
             WHERE (sender_id = ? AND receiver_id = u.id) OR (sender_id = u.id AND receiver_id = ?)) as last_message_time,
            (SELECT COUNT(*) FROM chat_messages WHERE sender_id = u.id AND receiver_id = ? AND is_read = 0) as unread_count
            FROM users u 
            JOIN user_roles ur ON u.id = ur.user_id 
            WHERE ur.role = 'admin' 
            LIMIT 1";
    $stmt = db_query($sql, [$user_id, $user_id, $user_id]);
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $row['is_admin'] = true;
        $contacts[] = $row;
    }
} elseif ($is_admin) {
    // Get all users for admin
    $sql = "SELECT u.id, u.first_name, u.last_name, u.profile_image, u.status, 
            (SELECT MAX(created_at) FROM chat_messages 
             WHERE (sender_id = ? AND receiver_id = u.id) OR (sender_id = u.id AND receiver_id = ?)) as last_message_time,
            (SELECT COUNT(*) FROM chat_messages WHERE sender_id = u.id AND receiver_id = ? AND is_read = 0) as unread_count,
            (SELECT GROUP_CONCAT(role) FROM user_roles WHERE user_id = u.id) as roles
            FROM users u 
            WHERE u.id != ? 
            ORDER BY last_message_time DESC NULLS LAST, u.first_name ASC";
    $stmt = db_query($sql, [$user_id, $user_id, $user_id, $user_id]);
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $roles = explode(',', $row['roles']);
        $row['is_client'] = in_array('client', $roles);
        $row['is_barber'] = in_array('barber', $roles);
        $contacts[] = $row;
    }
}

// Get selected contact ID from URL or use first contact
$selected_contact_id = isset($_GET['contact']) ? intval($_GET['contact']) : ($contacts[0]['id'] ?? 0);

// Get messages with selected contact
$messages = [];

if ($selected_contact_id > 0) {
    $sql = "SELECT * FROM chat_messages 
            WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) 
            ORDER BY created_at ASC";
    $stmt = db_query($sql, [$user_id, $selected_contact_id, $selected_contact_id, $user_id]);
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    
    // Mark messages as read
    $sql = "UPDATE chat_messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ? AND is_read = 0";
    db_query($sql, [$selected_contact_id, $user_id]);
    
    // Get selected contact info
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = db_query($sql, [$selected_contact_id]);
    $result = $stmt->get_result();
    $selected_contact = $result->fetch_assoc();
}

// Handle message sending
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message']) && $selected_contact_id > 0) {
    $message_text = sanitize_input($_POST['message']);
    
    if (!empty($message_text)) {
        $sql = "INSERT INTO chat_messages (sender_id, receiver_id, message) VALUES (?, ?, ?)";
        db_query($sql, [$user_id, $selected_contact_id, $message_text]);
        
        // Redirect to prevent form resubmission
        header("Location: index.php?page=chat&contact={$selected_contact_id}");
        exit;
    }
}
?>

<div class="container py-5">
    <div class="card shadow-md">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Messages</h4>
        </div>
        <div class="card-body p-0">
            <div class="chat-container">
                <!-- Contacts Sidebar -->
                <div class="chat-sidebar">
                    <div class="list-group list-group-flush">
                        <?php if (empty($contacts)): ?>
                            <div class="p-3 text-center text-muted">
                                <p>No contacts found.</p>
                                <?php if ($is_client): ?>
                                    <p>Book a service to chat with a barber!</p>
                                    <a href="index.php?page=booking" class="btn btn-sm btn-primary">Book Now</a>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <?php foreach ($contacts as $contact): ?>
                                <a href="index.php?page=chat&contact=<?php echo $contact['id']; ?>" class="list-group-item list-group-item-action <?php echo ($selected_contact_id == $contact['id']) ? 'active' : ''; ?>">
                                    <div class="d-flex align-items-center">
                                        <div class="position-relative">
                                            <img src="<?php echo !empty($contact['profile_image']) ? 'uploads/profiles/' . htmlspecialchars($contact['profile_image']) : 'assets/images/user-placeholder.jpg'; ?>" alt="<?php echo htmlspecialchars($contact['first_name'] . ' ' . $contact['last_name']); ?>" class="rounded-circle me-2" width="50" height="50">
                                            <span class="position-absolute bottom-0 end-0 p-1 bg-<?php echo ($contact['status'] === 'active') ? 'success' : 'secondary'; ?> border border-light rounded-circle" style="width: 12px; height: 12px;"></span>
                                        </div>
                                        <div class="flex-grow-1 ms-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0"><?php echo htmlspecialchars($contact['first_name'] . ' ' . $contact['last_name']); ?></h6>
                                                <?php if ($contact['unread_count'] > 0): ?>
                                                    <span class="badge bg-danger rounded-pill"><?php echo $contact['unread_count']; ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <small class="text-muted">
                                                <?php if (isset($contact['is_admin']) && $contact['is_admin']): ?>
                                                    <i class="fas fa-user-shield me-1"></i> Admin
                                                <?php elseif (isset($contact['is_barber']) && $contact['is_barber']): ?>
                                                    <i class="fas fa-cut me-1"></i> Barber
                                                <?php elseif (isset($contact['is_client']) && $contact['is_client']): ?>
                                                    <i class="fas fa-user me-1"></i> Client
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Chat Messages -->
                <div class="d-flex flex-column flex-grow-1">
                    <?php if ($selected_contact_id > 0 && isset($selected_contact)): ?>
                        <!-- Chat Header -->
                        <div class="p-3 border-bottom d-flex align-items-center">
                            <img src="<?php echo !empty($selected_contact['profile_image']) ? 'uploads/profiles/' . htmlspecialchars($selected_contact['profile_image']) : 'assets/images/user-placeholder.jpg'; ?>" alt="<?php echo htmlspecialchars($selected_contact['first_name'] . ' ' . $selected_contact['last_name']); ?>" class="rounded-circle me-2" width="40" height="40">
                            <div>
                                <h6 class="mb-0"><?php echo htmlspecialchars($selected_contact['first_name'] . ' ' . $selected_contact['last_name']); ?></h6>
                                <small class="text-muted">
                                    <?php echo ($selected_contact['status'] === 'active') ? '<span class="text-success">Online</span>' : '<span class="text-secondary">Offline</span>'; ?>
                                </small>
                            </div>
                        </div>
                        
                        <!-- Messages -->
                        <div class="chat-messages p-3">
                            <?php if (empty($messages)): ?>
                                <div class="text-center text-muted my-5">
                                    <i class="far fa-comments fa-3x mb-3"></i>
                                    <p>No messages yet. Start the conversation!</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($messages as $message): ?>
                                    <div class="message <?php echo ($message['sender_id'] == $user_id) ? 'message-sent' : 'message-received'; ?>">
                                        <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                                        <small class="d-block mt-1 text-muted">
                                            <?php echo date('g:i A', strtotime($message['created_at'])); ?>
                                            <?php if ($message['sender_id'] == $user_id): ?>
                                                <?php echo $message['is_read'] ? '<i class="fas fa-check-double ms-1"></i>' : '<i class="fas fa-check ms-1"></i>'; ?>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Message Input -->
                        <div class="chat-input mt-auto p-3 border-top">
                            <form method="POST" action="">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="message" placeholder="Type your message..." required>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="d-flex flex-column align-items-center justify-content-center h-100 p-5 text-center text-muted">
                            <i class="far fa-comments fa-5x mb-4"></i>
                            <h5>Select a contact to start chatting</h5>
                            <?php if (empty($contacts)): ?>
                                <?php if ($is_client): ?>
                                    <p class="mt-3">Book a service to chat with a barber!</p>
                                    <a href="index.php?page=booking" class="btn btn-primary mt-2">Book Now</a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Scroll to bottom of chat messages
        const chatMessages = document.querySelector('.chat-messages');
        if (chatMessages) {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
        
        // Auto-refresh chat (in a real application, use WebSockets instead)
        // setInterval(function() {
        //     const currentUrl = window.location.href;
        //     if (currentUrl.includes('page=chat')) {
        //         location.reload();
        //     }
        // }, 30000); // Refresh every 30 seconds
    });
</script>