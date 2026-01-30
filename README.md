# Bumpa Loyalty Program

A comprehensive loyalty program application featuring customer-facing dashboards and admin management tools, built with Laravel backend and React frontend.

## Overview

This application provides a complete loyalty system with:
- **Customer Portal**: User registration, login, dashboard with achievements, badges, and cashback tracking
- **Admin Portal**: Management of users, achievements, badges, and system statistics
- **Backend API**: Event-driven Laravel microservice handling business logic
- **Infrastructure**: Docker-based deployment with MySQL, Redis, and RabbitMQ


## Setup and Running the App

### Prerequisites
- Docker (version 20.10 or later)
- Docker Compose (version 2.0 or later)

### Environment Setup
1. Clone the repository:
   ```bash
   git clone <repository-url>
   cd bumpa-loyalty-app
   ```

2. Copy the environment file for the backend:
   ```bash
   cp backend/.env.example backend/.env
   ```

3. Configure the environment variables in `backend/.env` as needed (database credentials, API keys, etc.).

### Running the Application
1. Start all services using Docker Compose:
   ```bash
   docker-compose up -d
   ```

   This will start the following services:
   - **Laravel Backend**: Available at `http://localhost:8000`
   - **React Frontend (Customer Portal)**: Available at `http://localhost:3000`
   - **MySQL Database**: Available at `localhost:3307`
   - **Redis**: Available at `localhost:6380`
   - **RabbitMQ Management**: Available at `http://localhost:15672` (username: guest, password: guest)

2. Create a queue in the RabbitMQ called `default`, because Laravel doesn't create that automatically which is meant not to be so:
   - Go to [RabbitMQ Management](http://localhost:15672)
   - Click `Queue` and create a queue named `default`


3. Access the applications:
   - Customer Portal: `http://localhost:3000`
   - Admin Portal: `http://localhost:3000/admin` (or as configured)
   - Backend API: `http://localhost:8000/api`

### Stopping the Application
To stop all services:
```bash
docker-compose down
```

To stop and remove volumes (this will delete database data):
```bash
docker-compose down -v
```

## Architecture

### Backend (Laravel)
Event-driven microservice handling loyalty program logic with achievements, badges, and cashback processing.

### Frontend (React/TypeScript)
Turborepo monorepo with two separate applications:
- **Customer App**: User-facing interface for loyalty program interaction
- **Admin App**: Administrative interface for system management
- **Shared UI Package**: Reusable components and utilities

### Infrastructure
Docker Compose setup with:
- Laravel application server
- Nginx reverse proxy
- MySQL database
- Redis cache/session store
- RabbitMQ message queue
- Queue workers for async processing
- Frontend served via Nginx

### Technical Decisions
- **Laravel**: Chosen  as that is what is required for the task
- **React + TypeScript**: Type-safe frontend development with component reusability
- **Turborepo**: Efficient monorepo management with build caching and task orchestration
- **Tailwind CSS**: Utility-first CSS framework for consistent, maintainable styling
- **Docker**: Containerization for consistent development and deployment environments
- **RabbitMQ**: Reliable message queuing for async processing
- **MySQL**: Relational database for structured data with ACID compliance
- **Redis**: High-performance caching and session storage

## Architecture Principles

### 1. Domain-Driven Design (DDD)
- **Bounded Contexts**: Clear separation between Loyalty, Payment, and User domains
- **Entities**: User, Achievement, Badge, Purchase, Transaction
- **Value Objects**: Criteria, Progress, Benefits
- **Aggregates**: User with achievements and badges

### 2. SOLID Principles
- **Single Responsibility**: Each service handles one concern
- **Open/Closed**: Payment providers extensible without modification
- **Liskov Substitution**: All payment providers are interchangeable
- **Interface Segregation**: Focused contracts like `PaymentProviderInterface`
- **Dependency Inversion**: Services depend on abstractions, not concretions

### 3. Event-Driven Architecture
```
Event Source → Event Bus → Event Listener → Job Queue → Worker → Actions
```

## System Components

### Core Services

#### 1. AchievementService
**Responsibility**: Manages achievement lifecycle and progress tracking

**Key Methods**:
- `processPurchaseForAchievements()`: Analyzes purchase against achievement criteria
- `checkAndUpdateProgress()`: Updates user progress toward achievements
- `unlockAchievement()`: Handles achievement unlock logic
- `getUserAchievementProgress()`: Retrieves comprehensive progress data

**Business Rules**:
- Achievements cannot be unlocked twice
- Progress is cumulative
- Points are awarded on unlock
- Events are fired for real-time updates

#### 2. BadgeService
**Responsibility**: Manages badge tier progression

**Key Methods**:
- `checkAndAwardBadges()`: Evaluates badge eligibility
- `awardBadge()`: Awards badge and updates current status
- `getAllBadgeProgress()`: Calculates progress toward all badges
- `getBadgeHistory()`: Retrieves user's badge history

**Business Rules**:
- Badges have point and achievement requirements
- Only one current badge per user
- Higher-level badges supersede lower ones
- Badges grant benefits (cashback multipliers, etc.)

#### 3. CashbackPaymentService
**Responsibility**: Processes cashback payments through payment providers

**Key Methods**:
- `calculateCashback()`: Determines cashback amount based on rules
- `processCashback()`: Initiates payment transaction
- `retryCashback()`: Handles failed payment retries
- `getCashbackSummary()`: Aggregates transaction data

**Business Rules**:
- Tiered cashback rates (1%-5% based on amount)
- Badge bonuses apply
- Automatic retry on failure (5 attempts with backoff)
- Transaction state tracking

### Data Flow

#### Purchase-to-Reward Flow
```
1. Purchase Created (status: pending)
   ↓
2. Payment Confirmed (status: completed)
   ↓
3. PurchaseCompleted Event Fired
   ↓
4. Event Listener Queues Job
   ↓
5. ProcessLoyaltyRewards Job Executes
   ├─▶ Check Achievements
   │   ├─▶ Update Progress
   │   └─▶ Unlock if Met → Fire AchievementUnlocked Event
   ├─▶ Check Badges
   │   ├─▶ Calculate Progress
   │   └─▶ Award if Met → Fire BadgeUnlocked Event
   └─▶ Process Cashback
       ├─▶ Calculate Amount
       ├─▶ Call Payment Provider
       └─▶ Create Transaction Record
   ↓
6. Purchase Marked as Processed
```

## Database Design

### Schema Philosophy
- **Normalization**: 3NF for data integrity
- **Indexing Strategy**: Strategic indexes on foreign keys and query patterns
- **Pivot Tables**: Explicit many-to-many relationships
- **Soft Deletes**: Where applicable for audit trails
- **Timestamps**: All tables track created/updated times

### Key Tables

#### users
```sql
id, name, email, password, role, 
total_points, total_cashback, current_badge_id,
created_at, updated_at
```
**Indexes**: email (unique), role+total_points (composite)

#### achievements
```sql
id, name, description, type, criteria (JSON),
points, icon, tier, is_active,
created_at, updated_at
```
**Indexes**: type+is_active, tier

#### badges
```sql
id, name, description, level (unique),
points_required, achievements_required,
icon, color, benefits (JSON), is_active,
created_at, updated_at
```
**Indexes**: level+is_active

#### user_achievements (pivot)
```sql
id, user_id, achievement_id,
unlocked_at (nullable), progress, metadata (JSON),
created_at, updated_at
```
**Indexes**: user_id+achievement_id (unique), unlocked_at

#### purchases
```sql
id, user_id, order_id, amount, currency, status,
items (JSON), metadata (JSON), processed_for_loyalty,
created_at, updated_at
```
**Indexes**: user_id+status, status+processed_for_loyalty

#### cashback_transactions
```sql
id, user_id, purchase_id, amount, currency, status,
payment_provider, payment_reference,
payment_response (JSON), error_message,
processed_at, created_at, updated_at
```
**Indexes**: user_id+status, status, payment_reference

## API Design

### RESTful Conventions
- **Resource-Oriented**: URLs represent resources
- **HTTP Verbs**: Proper use of GET, POST, PUT, DELETE
- **Status Codes**: Semantic response codes
- **Versioning**: URI versioning (v1)
- **Pagination**: Cursor-based for large datasets
- **Filtering**: Query parameters for filtering/sorting

### Response Structure
```json
{
  "data": { /* Resource data */ },
  "meta": { /* Pagination metadata */ },
  "links": { /* HATEOAS links */ },
  "message": "Success message"
}
```

### Error Handling
```json
{
  "message": "Error description",
  "errors": {
    "field": ["Validation error messages"]
  }
}
```

## Security Architecture

### Authentication
- **Token-Based**: Laravel Sanctum SPA authentication and JWT
- **Token Storage**: Encrypted in database
- **Token Expiration**: Configurable TTL
- **Revocation**: Instant token invalidation

### Authorization
- **Role-Based Access Control (RBAC)**: Customer vs Admin roles
- **Policy-Based**: Fine-grained permissions per resource
- **Middleware**: Request-level authorization checks
- **Route Protection**: Grouped route middleware

### Data Protection
- **Password Hashing**: Bcrypt with salt
- **SQL Injection**: Eloquent ORM parameterized queries
- **XSS Protection**: Input sanitization and output escaping
- **CSRF Protection**: Token validation on mutations
- **Rate Limiting**: Throttling on sensitive endpoints

## Queue System

### RabbitMQ Architecture
```
Publishers → Exchange → Queues → Consumers
```

### Queue Strategy
- **Dedicated Queues**: loyalty, payments, notifications
- **Dead Letter Queue**: Failed message handling
- **Message Persistence**: Durable messages
- **Prefetch Count**: Optimized worker throughput
- **Acknowledgments**: Manual ack for reliability

### Job Configuration
```php
class ProcessLoyaltyRewards implements ShouldQueue
{
    public $tries = 3;           // Retry attempts
    public $timeout = 120;       // Max execution time
    public $backoff = [60, 300]; // Exponential backoff
}
```

## Caching Strategy

### Redis Implementation
- **Session Storage**: User sessions
- **Rate Limiting**: Request throttling counters
- **Query Caching**: Frequently accessed data
- **Cache Tags**: Group invalidation
- **Cache TTL**: Appropriate expiration per data type

### Cache Patterns
```php
// Cache-Aside Pattern
$achievements = Cache::remember('achievements:active', 3600, function () {
    return Achievement::active()->get();
});

// Write-Through Pattern
$user->update($data);
Cache::put("user:{$user->id}", $user, 3600);
```

## Performance Optimization

### Database Optimization
- **Eager Loading**: Prevent N+1 queries
- **Query Optimization**: Selective column fetching
- **Index Strategy**: Covering indexes on frequent queries
- **Connection Pooling**: Persistent connections

### Application Optimization
- **Config Caching**: Pre-compile configuration
- **Route Caching**: Compiled route definitions
- **Autoloader Optimization**: Composer classmap
- **OPcache**: PHP opcode caching

### Monitoring Metrics
- Response time (p50, p95, p99)
- Throughput (requests/second)
- Error rate
- Queue depth and processing time
- Database query performance

## Scalability Considerations

### Horizontal Scaling
- **Stateless Application**: No session storage in app servers
- **Load Balancing**: Nginx upstream servers
- **Database Read Replicas**: Separate read/write operations
- **Queue Workers**: Multiple worker instances

### Vertical Scaling
- **Database**: Larger instance for complex queries
- **Redis**: Memory optimization for cache hit rate
- **Application**: CPU/memory tuning

## Deployment Pipeline

### CI/CD Flow
```
Git Push → Tests → Build → Docker Image → Push Registry → Deploy
```

### Deployment Strategy
- **Blue-Green Deployment**: Zero downtime
- **Database Migrations**: Versioned, reversible
- **Feature Flags**: Gradual rollout
- **Rollback Plan**: Quick revert capability

## Monitoring & Observability

### Logging Strategy
- **Structured Logging**: JSON format
- **Log Levels**: DEBUG, INFO, WARNING, ERROR, CRITICAL
- **Contextual Data**: User ID, request ID, timestamps
- **Log Aggregation**: Centralized log storage



## Frontend Applications

### Customer Portal (`/apps/customer`)
- User authentication (login/register)
- Dashboard with loyalty progress
- Achievement and badge tracking
- Purchase history and cashback status
- Profile management

### Admin Portal (`/apps/admin`)
- Admin authentication
- User management
- Achievement and badge administration
- System statistics and analytics
- Cashback transaction monitoring

### Shared UI Package (`/packages/ui`)
- Reusable React components
- Tailwind CSS configuration
- Common utilities and hooks
- Consistent design system

## API Endpoints

### Authentication
- `POST /api/login` - User/Admin login
- `POST /api/register` - User registration
- `POST /api/logout` - Logout

### Customer Endpoints
- `GET /api/user/achievements` - User's achievements
- `GET /api/user/badges` - User's badges
- `GET /api/user/purchases` - Purchase history
- `GET /api/user/cashback` - Cashback transactions

### Admin Endpoints
- `GET /api/admin/users` - List users
- `GET /api/admin/statistics` - System stats
- `POST /api/admin/achievements` - Create achievement
- `PUT /api/admin/achievements/{id}` - Update achievement
- `POST /api/admin/badges` - Create badge

## Development Workflow

### Building and Running
```bash
# Build all frontend apps
cd re-frontend && pnpm build

# Run specific app in development
pnpm dev --filter=customer
pnpm dev --filter=admin

# Run backend tests
cd backend && php artisan test
```

### Code Quality
- ESLint for JavaScript/TypeScript linting
- Prettier for code formatting
- PHPUnit for backend testing
- TypeScript for type checking

## Deployment

The application is containerized and can be deployed using Docker Compose. For production:

1. Configure environment variables
2. Set up SSL certificates
3. Configure reverse proxy
4. Set up monitoring and logging
5. Configure backup strategies

## Conclusion

This architecture provides:
- **Scalability**: Horizontal and vertical scaling capability
- **Reliability**: Event-driven async processing with retries
- **Maintainability**: Clean code, SOLID principles, comprehensive tests
- **Security**: Multi-layer authentication and authorization
- **Performance**: Caching, indexing, and query optimization
- **Observability**: Logging, monitoring, and metrics

The system is production-ready and designed for long-term growth and evolution.