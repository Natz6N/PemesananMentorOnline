# 🎓 MentorConnect API - Backend Platform Pencarian dan Booking Mentor

<div align="center">
  <img src="https://via.placeholder.com/200x200/4F46E5/FFFFFF?text=MC" alt="MentorConnect API Logo" width="200"/>
  
  [![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
  [![PHP](https://img.shields.io/badge/PHP-8.3+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
  [![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
  [![API](https://img.shields.io/badge/REST-API-blue?style=for-the-badge)](https://restfulapi.net)
  [![License](https://img.shields.io/badge/License-MIT-green.svg?style=for-the-badge)](LICENSE)
</div>

## 📋 About Project

**MentorConnect API** adalah backend service untuk platform pencarian dan booking mentor. API ini menyediakan endpoints lengkap untuk mengelola mentor, student, booking session, pembayaran, dan sistem review. Dibangun dengan Laravel 12 dan menggunakan RESTful API architecture yang dapat diintegrasikan dengan berbagai frontend framework.

### ✨ API Features

- 🔐 **Authentication & Authorization** - Sanctum untuk autentikasi dan Gate/Policy untuk otorisasi
- 👥 **User Management** - Register, login, profile management untuk Student & Mentor
- 🏷️ **Category System** - Manajemen kategori keahlian mentor
- 👨‍🏫 **Mentor Profiles** - CRUD operations untuk profil mentor lengkap
- 📅 **Availability Management** - Sistem jadwal ketersediaan mentor
- 📋 **Booking System** - Complete booking lifecycle management
- 💳 **Payment Integration** - Payment gateway integration ready
- ⭐ **Review & Rating** - Sistem penilaian dan feedback
- 🔍 **Advanced Search** - Filter mentor berdasarkan kategori, rating, harga
- 📊 **Analytics Endpoints** - Dashboard data untuk admin
- 🔄 **Realtime Updates** - Laravel Reverb untuk WebSockets communication

### 🎯 API Use Cases

- **Mobile Apps**: Backend untuk aplikasi iOS/Android mentoring
- **Web Applications**: Backend untuk SPA (React, Vue, Angular)
- **Third-party Integration**: API untuk integrasi dengan platform lain
- **Microservices**: Service untuk architecture microservices
- **White-label Solutions**: Backend untuk custom mentoring platforms

## 🚀 Installation

### Prerequisites

Pastikan sistem Anda memiliki requirements berikut:

- PHP >= 8.3
- Composer
- Node.js & NPM
- MySQL >= 8.0
- Redis (untuk WebSockets dengan Laravel Reverb)

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
   
   # WebSockets configuration
   BROADCAST_DRIVER=reverb
   REVERB_APP_ID=mentorconnect
   REVERB_APP_KEY=your_reverb_key
   REVERB_APP_SECRET=your_reverb_secret
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

7. **Start Development Server**
   ```bash
   # Start Laravel server
   php artisan serve
   
   # Start Reverb WebSocket server
   php artisan reverb:start
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

### Role-Based Access Control
API menggunakan sistem role-based access control:
- **Admin**: Akses penuh ke semua data dan endpoints
- **Mentor**: Mengelola profil, availability, dan booking sessions
- **Student**: Mencari mentor, membuat booking, dan memberikan review

### Core API Endpoints

#### 🔐 Authentication
```http
POST   /api/register                    # User registration
POST   /api/login                       # User login
POST   /api/auth/logout                 # User logout
GET    /api/auth/profile                # Get authenticated user
POST   /api/auth/update-profile         # Update user profile
POST   /api/auth/change-password        # Change password
POST   /api/auth/refresh-token          # Refresh token
```

#### 👥 User Management (Admin)
```http
GET    /api/admin/users                 # Get all users
GET    /api/admin/users/{user}          # Get user detail
PUT    /api/admin/users/{user}          # Update user
DELETE /api/admin/users/{user}          # Delete user
```

#### 🏷️ Categories
```http
GET    /api/categories                  # Get all categories (public)
GET    /api/categories/{category}       # Get category detail (public)
POST   /api/admin/categories            # Create category (admin)
PUT    /api/admin/categories/{category} # Update category (admin)
DELETE /api/admin/categories/{category} # Delete category (admin)
```

#### 👨‍🏫 Mentors
```http
GET    /api/mentors                     # Get all mentors with filters (public)
GET    /api/mentors/{id}                # Get mentor detail (public)
GET    /api/mentors/{id}/availability   # Get mentor availability (public)
GET    /api/mentors/{id}/reviews        # Get mentor reviews (public)

GET    /api/mentor/profile              # Get own mentor profile (mentor)
POST   /api/mentor/profile              # Create mentor profile (mentor)
PUT    /api/mentor/profile/{id}         # Update mentor profile (mentor)

PUT    /api/admin/mentors/{id}          # Update mentor profile (admin)
DELETE /api/admin/mentors/{id}          # Delete mentor profile (admin)
```

#### 📅 Mentor Availability
```http
GET    /api/mentor/availabilities                      # Get own availabilities (mentor)
POST   /api/mentor/availabilities                      # Create availability (mentor)
PUT    /api/mentor/availabilities/{availability}       # Update availability (mentor)
DELETE /api/mentor/availabilities/{availability}       # Delete availability (mentor)
POST   /api/mentor/availabilities/bulk                 # Bulk update availability (mentor)
```

#### 📋 Bookings
```http
GET    /api/student/bookings                  # Get own bookings (student)
POST   /api/student/bookings                  # Create new booking (student)
GET    /api/student/bookings/{booking}        # Get booking detail (student)
PUT    /api/student/bookings/{booking}        # Update booking (student)
DELETE /api/student/bookings/{booking}        # Cancel booking (student)

GET    /api/mentor/bookings                   # Get assigned bookings (mentor)
GET    /api/mentor/bookings/{booking}         # Get booking detail (mentor)
PUT    /api/mentor/bookings/{booking}         # Update booking details (mentor)
PUT    /api/mentor/bookings/{booking}/confirm # Confirm booking (mentor)
PUT    /api/mentor/bookings/{booking}/complete # Complete booking (mentor)

GET    /api/admin/bookings                    # Get all bookings (admin)
GET    /api/admin/bookings/{booking}          # Get booking detail (admin)
PUT    /api/admin/bookings/{booking}          # Update booking (admin)
DELETE /api/admin/bookings/{booking}          # Cancel booking (admin)
```

#### 💳 Payments
```http
GET    /api/student/payments                 # Get payment history (student)
POST   /api/student/payments                 # Process payment (student)
GET    /api/student/payments/{payment}       # Get payment detail (student)

GET    /api/admin/payments                   # Get all payments (admin)
GET    /api/admin/payments/{payment}         # Get payment detail (admin)

POST   /api/webhook/payment                  # Payment gateway webhook (public)
```

#### ⭐ Reviews
```http
POST   /api/student/bookings/{booking}/reviews   # Create review for booking (student)
GET    /api/student/reviews                      # Get own reviews (student)
PUT    /api/student/reviews/{review}             # Update review (student)
DELETE /api/student/reviews/{review}             # Delete review (student)

GET    /api/admin/reviews                        # Get all reviews (admin)
GET    /api/admin/reviews/{review}               # Get review detail (admin)
PUT    /api/admin/reviews/{review}               # Update review (admin)
DELETE /api/admin/reviews/{review}               # Delete review (admin)
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
GET /api/mentors?category_id=1&min_rating=4&max_rate=100&sort_by=rating_average&sort_order=desc
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "message": "Daftar profil mentor berhasil diambil",
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
POST /api/student/bookings
Authorization: Bearer {token}
Content-Type: application/json

{
  "mentor_profile_id": 1,
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
  "message": "Booking berhasil dibuat",
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
  }
}
```

## 🛠️ Tech Stack

### Backend Framework
- **Framework**: Laravel 12.x
- **Language**: PHP 8.3+
- **Architecture**: RESTful API
- **Authentication**: Laravel Sanctum (Token-based)
- **Authorization**: Gates & Policies
- **WebSockets**: Laravel Reverb
- **Validation**: Laravel Form Requests
- **Eloquent ORM**: Database relationships and queries

### Security Features
- **Rate Limiting**: Throttle requests to prevent abuse
- **Status Checks**: Middleware untuk memastikan user active
- **Authorization Rules**: Gates dan Policies untuk RBAC
- **Input Validation**: Form Request validation
- **Mass Assignment Protection**: $fillable attributes
- **Standardized Error Responses**: JSON-formatted errors

### Database & Storage
- **Database**: MySQL 8.0+
- **Migrations**: Laravel Database Migrations
- **Seeders**: Sample data generation
- **File Storage**: Laravel Storage (Local/S3)
- **Cache**: Redis/File-based caching
