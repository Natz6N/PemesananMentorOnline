# 🎓 MentorConnect API - Backend Platform Pencarian dan Booking Mentor

<div align="center">
  <img src="https://via.placeholder.com/200x200/4F46E5/FFFFFF?text=MC" alt="MentorConnect API Logo" width="200"/>
  
  [![Laravel](https://img.shields.io/badge/Laravel-10.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
  [![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
  [![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
  [![API](https://img.shields.io/badge/REST-API-blue?style=for-the-badge)](https://restfulapi.net)
  [![License](https://img.shields.io/badge/License-MIT-green.svg?style=for-the-badge)](LICENSE)
</div>

## 📋 About Project

**MentorConnect API** adalah backend service untuk platform pencarian dan booking mentor. API ini menyediakan endpoints lengkap untuk mengelola mentor, student, booking session, pembayaran, dan sistem review. Dibangun dengan Laravel 10 dan menggunakan RESTful API architecture yang dapat diintegrasikan dengan berbagai frontend framework.

### ✨ API Features

- 🔐 **Authentication & Authorization** - JWT token-based dengan role management
- 👥 **User Management** - Register, login, profile management untuk Student & Mentor
- 🏷️ **Category System** - Manajemen kategori keahlian mentor
- 👨‍🏫 **Mentor Profiles** - CRUD operations untuk profil mentor lengkap
- 📅 **Availability Management** - Sistem jadwal ketersediaan mentor
- 📋 **Booking System** - Complete booking lifecycle management
- 💳 **Payment Integration** - Payment gateway integration ready
- ⭐ **Review & Rating** - Sistem penilaian dan feedback
- 🔍 **Advanced Search** - Filter mentor berdasarkan kategori, rating, harga
- 📊 **Analytics Endpoints** - Dashboard data untuk admin

### 🎯 API Use Cases

- **Mobile Apps**: Backend untuk aplikasi iOS/Android mentoring
- **Web Applications**: Backend untuk SPA (React, Vue, Angular)
- **Third-party Integration**: API untuk integrasi dengan platform lain
- **Microservices**: Service untuk architecture microservices
- **White-label Solutions**: Backend untuk custom mentoring platforms

## 🚀 Installation

### Prerequisites

Pastikan sistem Anda memiliki requirements berikut:

- PHP >= 8.1
- Composer
- Node.js & NPM
- MySQL >= 8.0
- Redis (optional, untuk caching)

### Step-by-Step Installation

1. **Clone Repository**
   ```bash
   git clone https://github.com/yourusername/mentorconnect.git
   cd mentorconnect
   ```

2. **Install Dependencies**
   ```bash
   # Install PHP dependencies
   composer install
   
   # Install Node.js dependencies
   npm install
   ```

3. **Environment Setup**
   ```bash
   # Copy environment file
   cp .env.example .env
   
   # Generate application key
   php artisan key:generate
   ```

4. **Database Configuration**
   
   Edit file `.env` dan sesuaikan konfigurasi database:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=mentorconnect
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

5. **Run Migrations & Seeders**
   ```bash
   # Create database tables
   php artisan migrate
   
   # Seed sample data (optional)
   php artisan db:seed
   ```

6. **Storage Setup**
   ```bash
   # Create storage link
   php artisan storage:link
   
   # Set permissions
   chmod -R 775 storage bootstrap/cache
   ```

7. **Compile Assets**
   ```bash
   # Development
   npm run dev
   
   # Production
   npm run build
   ```

8. **Start Development Server**
   ```bash
   php artisan serve
   ```

   Aplikasi akan berjalan di `http://localhost:8000`

## 💻 API Documentation & Frontend Integration

### Base URL
```
Development: http://localhost:8000/api
Production: https://yourdomain.com/api
```

### Authentication
API menggunakan Laravel Sanctum untuk authentication. Setiap request yang membutuhkan authentication harus menyertakan Bearer token di header.

```bash
Authorization: Bearer {your-token-here}
Content-Type: application/json
```

### Core API Endpoints

#### 🔐 Authentication
```http
POST   /api/register                    # User registration
POST   /api/login                       # User login
POST   /api/logout                      # User logout
GET    /api/user                        # Get authenticated user
PUT    /api/profile                     # Update user profile
```

#### 👥 User Management
```http
GET    /api/users                       # Get all users (admin)
GET    /api/users/{id}                  # Get user detail
PUT    /api/users/{id}                  # Update user
DELETE /api/users/{id}                  # Delete user (admin)
```

#### 🏷️ Categories
```http
GET    /api/categories                  # Get all categories
POST   /api/categories                  # Create category (admin)
GET    /api/categories/{id}             # Get category detail
PUT    /api/categories/{id}             # Update category (admin)
DELETE /api/categories/{id}             # Delete category (admin)
```

#### 👨‍🏫 Mentors
```http
GET    /api/mentors                     # Get all mentors with filters
POST   /api/mentors                     # Create mentor profile
GET    /api/mentors/{id}                # Get mentor detail
PUT    /api/mentors/{id}                # Update mentor profile
DELETE /api/mentors/{id}                # Delete mentor profile
GET    /api/mentors/{id}/availability   # Get mentor availability
POST   /api/mentors/{id}/availability   # Set mentor availability
GET    /api/mentors/{id}/reviews        # Get mentor reviews
```

#### 📋 Bookings
```http
GET    /api/bookings                    # Get user bookings
POST   /api/bookings                    # Create new booking
GET    /api/bookings/{id}               # Get booking detail
PUT    /api/bookings/{id}               # Update booking
DELETE /api/bookings/{id}               # Cancel booking
PUT    /api/bookings/{id}/confirm       # Confirm booking (mentor)
PUT    /api/bookings/{id}/complete      # Complete booking (mentor)
```

#### 💳 Payments
```http
GET    /api/payments                    # Get payment history
POST   /api/payments                    # Process payment
GET    /api/payments/{id}               # Get payment detail
POST   /api/payments/webhook            # Payment gateway webhook
```

#### ⭐ Reviews
```http
GET    /api/reviews                     # Get reviews
POST   /api/reviews                     # Create review
GET    /api/reviews/{id}                # Get review detail
PUT    /api/reviews/{id}                # Update review
DELETE /api/reviews/{id}                # Delete review
```

### 📝 Request/Response Examples

#### Register New User
```http
POST /api/register
Content-Type: application/json

{
  "name": "natz",
  "email": "natz@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "student",
  "phone": "+62812345678"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "natz",
      "email": "natz@example.com",
      "role": "student"
    },
    "token": "1|abc123def456..."
  },
  "message": "Registration successful"
}
```

#### Search Mentors
```http
GET /api/mentors?category=programming&min_rating=4&max_price=100&sort=rating
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "mentors": [
      {
        "id": 1,
        "name": "StringBadi",
        "avatar": "storage/avatars/StringBadi.jpg",
        "bio": "Senior Full Stack Developer with 5+ years experience",
        "expertise": ["Laravel", "React", "Node.js"],
        "hourly_rate": 75.00,
        "rating_average": 4.8,
        "total_reviews": 24,
        "categories": [
          {"id": 1, "name": "Programming"}
        ]
      }
    ],
    "pagination": {
      "current_page": 1,
      "total": 15,
      "per_page": 10
    }
  }
}
```

#### Create Booking
```http
POST /api/bookings
Authorization: Bearer {token}
Content-Type: application/json

{
  "mentor_id": 1,
  "scheduled_at": "2024-01-15 10:00:00",
  "duration_minutes": 60,
  "session_topic": "Laravel Advanced Concepts",
  "student_notes": "I want to learn about Laravel Queues and Jobs"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "booking": {
      "id": 1,
      "booking_code": "BK-20240115-ABC123",
      "mentor": {
        "id": 1,
        "name": "StringBadi"
      },
      "scheduled_at": "2024-01-15T10:00:00.000000Z",
      "duration_minutes": 60,
      "total_amount": 75.00,
      "status": "pending"
    }
  },
  "message": "Booking created successfully"
}
```

## 🛠️ Tech Stack

### Backend Framework
- **Framework**: Laravel 12.x
- **Language**: PHP 8.3+
- **Architecture**: RESTful API
- **Authentication**: Laravel Sanctum (Token-based)
- **Validation**: Laravel Form Requests
- **Eloquent ORM**: Database relationships and queries

### Database & Storage
- **Database**: MySQL 8.0+
- **Migrations**: Laravel Database Migrations
- **Seeders**: Sample data generation
- **File Storage**: Laravel Storage (Local/S3)
- **Cache**: Redis/File-based caching
