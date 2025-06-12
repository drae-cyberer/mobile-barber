# Mobile Barber Platform

A web-based platform that connects clients with barbers for on-demand home barbing services, similar to the Bolt transportation app model.

## Features

- **User Authentication**: Registration and login for clients, barbers, and admins
- **Service Booking**: Clients can book barbing services at their preferred location and time
- **Barber Profiles**: Detailed profiles with ratings, reviews, and portfolio
- **Real-time Chat**: Communication between clients, barbers, and admin support
- **Online Payments**: Secure payment processing for services
- **Location Services**: GPS tracking for barbers and client locations
- **Admin Dashboard**: Comprehensive management of users, services, and transactions
- **Responsive Design**: Mobile-friendly interface for all devices

## Technology Stack

- **Frontend**: HTML, CSS, JavaScript
- **Backend**: PHP
- **Database**: MySQL
- **Real-time Communication**: WebSockets
- **Payment Integration**: PayStack/Flutterwave
- **Maps & Location**: Google Maps API

## Installation

1. Clone the repository
2. Import the database schema from `database/schema.sql`
3. Configure database connection in `config/database.php`
4. Set up your web server (Apache/Nginx) to point to the project directory
5. Access the application through your web browser

## Project Structure

```
mobile-barber/
├── admin/              # Admin dashboard and management
├── api/                # API endpoints for mobile integration
├── assets/             # Static assets (CSS, JS, images)
├── chat/               # Real-time chat functionality
├── config/             # Configuration files
├── database/           # Database schema and migrations
├── includes/           # Reusable PHP components
├── payment/            # Payment processing modules
├── services/           # Service-related functionality
├── uploads/            # User-uploaded content
├── users/              # User authentication and profiles
├── vendor/             # Third-party libraries
├── index.php           # Main entry point
└── README.md           # Project documentation
```

## License

MIT