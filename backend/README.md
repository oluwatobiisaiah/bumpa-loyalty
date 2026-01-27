# E-Commerce Loyalty Program - Backend

A production-grade, event-driven microservice for managing customer loyalty programs with achievements, badges, and cashback rewards.

## 🏗️ Architecture & Design

### System Architecture

```
┌─────────────────┐     ┌──────────────┐     ┌─────────────┐
│   Frontend      │────▶│   Nginx      │────▶│  Laravel    │
│   (React SPA)   │     │  (Web Server)│     │     App     │
└─────────────────┘     └──────────────┘     └──────┬──────┘
                                                      │
                        ┌─────────────────────────────┼─────────────────┐
                        │                             │                 │
                   ┌────▼────┐              ┌────────▼────────┐  ┌────▼────┐
                   │  MySQL  │              │   RabbitMQ      │  │  Redis  │
                   │Database │              │  Message Queue  │  │  Cache  │
                   └─────────┘              └─────────────────┘  └─────────┘
                                                      │
                                              ┌───────▼────────┐
                                              │  Queue Worker  │
                                              │  (Background)  │
                                              └────────────────┘
```

### Design Patterns

- **Domain-Driven Design (DDD)**: Clear separation of concerns with domain models
- **Event-Driven Architecture**: Async processing via message queues
- **Repository Pattern**: Data access abstraction
- **Service Layer**: Business logic isolation
- **CQRS**: Separate read/write operations for scalability
- **Strategy Pattern**: Payment provider abstraction

### Core Components

1. **Achievement System**: Track and unlock user achievements
2. **Badge System**: Progressive tier-based rewards
3. **Cashback Engine**: Automated payment processing
4. **Event Bus**: RabbitMQ for reliable message delivery
5. **API Layer**: RESTful endpoints with Sanctum authentication

## 🚀 Getting Started

### Prerequisites

- Docker & Docker Compose
- Git
- (Optional) PHP 8.2+, Composer for local development

### Quick Start

```bash
# Clone the repository
git clone <repository-url>
cd loyalty-program

# Copy environment file
cp backend/.env.example backend/.env

# Start the services
docker-compose up -d

# Install dependencies
docker-compose exec app composer install

# Generate application key
docker-compose exec app php artisan key:generate

# Run migrations
docker-compose exec app php artisan migrate

# Seed database with sample data
docker-compose exec app php artisan db:seed

# Access the application
# API: http://localhost:8000
# RabbitMQ Management: http://localhost:15672 (guest/guest)
```

### Manual Setup (Without Docker)

```bash
cd backend

# Install dependencies
composer install

# Setup environment
cp .env.example .env
php artisan key:generate

# Configure database in .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=loyalty_program
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Run migrations and seeders
php artisan migrate
php artisan db:seed

# Start queue worker
php artisan queue:work --tries=3 --timeout=90

# Start development server
php artisan serve
```

## 📋 API Documentation

### Authentication Endpoints

#### Register Customer
```http
POST /api/v1/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

#### Login Customer
```http
POST /api/v1/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password123"
}
```

#### Admin Login
```http
POST /api/v1/admin/login
Content-Type: application/json

{
  "email": "admin@loyalty.com",
  "password": "password"
}
```

### Customer Endpoints

All customer endpoints require Bearer token authentication.

#### Get Loyalty Dashboard
```http
GET /api/v1/loyalty/dashboard
Authorization: Bearer {token}
```

**Response:**
```json
{
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "total_points": 750,
      "total_cashback": 125.50
    },
    "achievements": {
      "progress": [...],
      "summary": {
        "total_achievements": 10,
        "unlocked_achievements": 5,
        "completion_percentage": 50
      }
    },
    "badges": {
      "current": {...},
      "progress": [...],
      "summary": {...}
    },
    "cashback": {...}
  }
}
```

#### Get User Achievements
```http
GET /api/v1/users/{userId}/achievements
Authorization: Bearer {token}
```

#### Get User Badges
```http
GET /api/v1/users/{userId}/badges
Authorization: Bearer {token}
```

### Admin Endpoints

#### Get All Users' Achievements
```http
GET /api/v1/admin/users/achievements?page=1&per_page=15&search=john&sort_by=total_points&sort_order=desc
Authorization: Bearer {admin_token}
```

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "total_points": 750,
      "achievements_count": 5,
      "current_badge": {
        "id": 2,
        "name": "Silver Member",
        "level": 2
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 100
  }
}
```

#### Get User Detail
```http
GET /api/v1/admin/users/{userId}/loyalty
Authorization: Bearer {admin_token}
```

#### Get Statistics
```http
GET /api/v1/admin/loyalty/stats
Authorization: Bearer {admin_token}
```

## 🧪 Testing

### Run All Tests
```bash
docker-compose exec app php artisan test
```

### Run Specific Test Suite
```bash
# Unit tests
docker-compose exec app php artisan test --testsuite=Unit

# Feature/Integration tests
docker-compose exec app php artisan test --testsuite=Feature

# Specific test file
docker-compose exec app php artisan test tests/Feature/Api/UserAchievementApiTest.php
```

### Test Coverage
```bash
docker-compose exec app php artisan test --coverage
```

### Testing Strategy

- **Unit Tests**: Service layer business logic
- **Integration Tests**: API endpoints and database interactions
- **Event Tests**: Queue jobs and event listeners

## 🔄 Event-Driven Flow

### Purchase Processing Flow

```
Purchase Completed
       │
       ├─▶ PurchaseCompleted Event Fired
       │
       ├─▶ ProcessPurchaseForLoyalty Listener
       │
       ├─▶ ProcessLoyaltyRewards Job (Queued)
       │
       ├─▶ 1. Check Achievements
       │   ├─▶ Update Progress
       │   └─▶ Unlock if Met → AchievementUnlocked Event
       │
       ├─▶ 2. Check Badges
       │   ├─▶ Calculate Progress
       │   └─▶ Award if Met → BadgeUnlocked Event
       │
       └─▶ 3. Process Cashback
           ├─▶ Calculate Amount
           ├─▶ Call Payment Provider
           └─▶ Update Transaction Status
```

### Testing the Flow

```bash
# Create a test purchase (triggers the entire flow)
curl -X POST http://localhost:8000/api/v1/purchases \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 5000,
    "items": [
      {"name": "Product A", "quantity": 2, "price": 2500}
    ]
  }'

# Watch queue processing logs
docker-compose logs -f queue_worker
```

## 💳 Payment Integration

### Supported Providers

1. **Paystack** (Nigerian market)
2. **Flutterwave** (African markets)
3. **Mock Provider** (Development/Testing)

### Configuration

```env
# .env file
PAYMENT_PROVIDER=mock  # Options: paystack, flutterwave, mock

# Paystack Configuration
PAYSTACK_SECRET_KEY=your_secret_key
PAYSTACK_PUBLIC_KEY=your_public_key

# Flutterwave Configuration
FLUTTERWAVE_SECRET_KEY=your_secret_key
FLUTTERWAVE_PUBLIC_KEY=your_public_key
```

### Adding New Payment Provider

1. Implement `PaymentProviderInterface`
2. Register in service container
3. Update configuration

```php
// app/Services/Payment/Providers/NewProvider.php
class NewProvider implements PaymentProviderInterface
{
    public function getName(): string { return 'new_provider'; }
    
    public function transferCashback(User $user, float $amount, string $currency, array $metadata): array
    {
        // Implementation
    }
}
```

## 🗄️ Database Schema

### Key Tables

- **users**: User accounts and loyalty stats
- **achievements**: Achievement definitions
- **badges**: Badge tier definitions
- **user_achievements**: Achievement progress tracking
- **user_badges**: Badge ownership
- **purchases**: Purchase records
- **cashback_transactions**: Payment history

### ER Diagram

```
users
├── has many → user_achievements → achievements
├── has many → user_badges → badges
├── has many → purchases
└── has many → cashback_transactions
```

## 🔐 Security

- **Authentication**: Laravel Sanctum (Token-based)
- **Authorization**: Role-based access control (Customer/Admin)
- **Rate Limiting**: API throttling
- **CORS**: Configured for frontend domain
- **SQL Injection**: Eloquent ORM protection
- **XSS**: Input validation and sanitization

## 📊 Monitoring & Logging

### Application Logs

```bash
# View application logs
docker-compose exec app tail -f storage/logs/laravel.log

# View queue worker logs
docker-compose logs -f queue_worker

# View nginx access logs
docker-compose exec nginx tail -f /var/log/nginx/access.log
```

### Queue Monitoring

- RabbitMQ Management UI: http://localhost:15672
- Monitor queue depth, message rates, and consumer performance

## 🚢 Deployment

### Production Checklist

- [ ] Set `APP_ENV=production` in `.env`
- [ ] Set `APP_DEBUG=false`
- [ ] Configure real database credentials
- [ ] Set up SSL certificates
- [ ] Configure payment provider keys
- [ ] Set up log aggregation (e.g., ELK Stack)
- [ ] Configure backup strategy
- [ ] Set up monitoring (e.g., New Relic, Datadog)
- [ ] Configure queue supervisord for auto-restart

### Environment Variables

```env
APP_NAME="Loyalty Program"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.loyalty.com

DB_CONNECTION=mysql
DB_HOST=production-db-host
DB_DATABASE=loyalty_production

QUEUE_CONNECTION=rabbitmq
RABBITMQ_HOST=production-rabbitmq-host

CACHE_DRIVER=redis
REDIS_HOST=production-redis-host

PAYMENT_PROVIDER=paystack
PAYSTACK_SECRET_KEY=live_secret_key
```

## 🛠️ Troubleshooting

### Queue Not Processing

```bash
# Restart queue worker
docker-compose restart queue_worker

# Check RabbitMQ connection
docker-compose exec app php artisan queue:failed

# Retry failed jobs
docker-compose exec app php artisan queue:retry all
```

### Database Connection Issues

```bash
# Check MySQL status
docker-compose ps mysql

# View MySQL logs
docker-compose logs mysql

# Reset database
docker-compose exec app php artisan migrate:fresh --seed
```

### Performance Optimization

```bash
# Cache configuration
docker-compose exec app php artisan config:cache

# Cache routes
docker-compose exec app php artisan route:cache

# Optimize autoloader
docker-compose exec app composer dump-autoload --optimize
```

## 📈 Performance Considerations

- **Database Indexing**: Strategic indexes on foreign keys and frequently queried columns
- **Query Optimization**: Eager loading relationships to prevent N+1 queries
- **Caching**: Redis for frequently accessed data
- **Queue Workers**: Multiple workers for parallel processing
- **Database Connection Pooling**: Persistent connections for better performance

## 👥 Team & Support

**Test Credentials:**
- Customer: `customer@test.com` / `password`
- Admin: `admin@loyalty.com` / `password`

For technical support or questions, contact the development team.

## 📝 License

Proprietary - All rights reserved
