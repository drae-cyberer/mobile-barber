# Database Setup Instructions

## Automatic Setup

To automatically create all the database tables for the Mobile Barber Platform, follow these steps:

1. Make sure your XAMPP server is running (Apache and MySQL services)
2. Open your web browser and navigate to: http://localhost/mobile-barber/setup_database.php
3. The script will automatically create all the necessary tables defined in the schema.sql file
4. You should see a success message when all tables are created

## Manual Setup

If you prefer to set up the database manually:

1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Create a new database named `mobile_barber` if it doesn't exist
3. Select the `mobile_barber` database
4. Click on the "Import" tab
5. Click "Browse" and select the `schema.sql` file from the database folder
6. Click "Go" to import the database structure

## Database Structure

The Mobile Barber Platform uses the following tables:

- `users` - Stores user account information
- `user_roles` - Manages user roles (client, barber, admin)
- `barber_profiles` - Contains barber-specific information
- `services` - Lists available barber services
- `bookings` - Tracks client bookings
- `payments` - Records payment information
- `chat_messages` - Stores messages between users

Refer to the `schema.sql` file for the complete database structure.