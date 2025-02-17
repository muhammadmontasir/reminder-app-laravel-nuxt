# Event Management API

A Laravel 11 REST API for managing events with reminder functionality.

## Features

- User Authentication (Laravel Sanctum)
- Event Management (CRUD operations)
- CSV Import for Events
- Event Synchronization
- Email Reminders
- Rate Limiting
- Queue Processing

## Requirements

- PHP 8.2+
- Composer
- Redis
- MySQL/PostgreSQL
- Docker (optional)

## Installation

1. Clone the repository
```bash
git clone https://github.com/muhammadmontasir/reminder-app-laravel-nuxt.git
cd backend
```

2. Install dependencies
```bash
composer install
```

3. Configure environment
```bash
cp .env.example .env
```
4. Update the .env file with your configuration:

```bash
APP_NAME="Event Reminder"
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=event_reminder
DB_USERNAME=sail
DB_PASSWORD=password

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="no-reply@eventreminder.com"
MAIL_FROM_NAME="Event Reminder"
```

5. Start Laravel Sail
```bash
./vendor/bin/sail up -d
```

6. Run database migrations and seeders:

```bash
./vendor/bin/sail artisan migrate --seed
```

7. Generate Application Key

```bash
./vendor/bin/sail artisan key:generate
```

8. Access the Application
The API will be available at: http://localhost/api/v1.

Access Mailpit for email testing at: http://localhost:8025. 


## API Documentation

The API documentation is available at: http://localhost/api/v1/docs.

### Authentication
- `POST /api/register` - Register new user
- `POST /api/login` - User login
- `POST /api/logout` - User logout (requires authentication)
- `GET /api/user` - Get user info (requires authentication)

### Events
All routes require authentication:
- `GET /api/v1/events` - List events
- `GET /api/v1/events/{eventId}` - Get event details
- `POST /api/v1/events` - Create event
- `PUT /api/v1/events/{eventId}` - Update event
- `DELETE /api/v1/events/{eventId}` - Delete event
- `POST /api/v1/events/sync` - Sync events (rate limit: 60/minute)
- `POST /api/v1/events/import` - Import events from CSV (rate limit: 30/minute)

## Queue Workers

```bash
./vendor/bin/sail artisan queue:work redis
```

## Start the scheduler:

```bash
./vendor/bin/sail artisan schedule:work
```


## Testing API Endpoints

### Register User

```bash
curl -X POST http://localhost/api/v1/register \
  -H "Content-Type: application/json" \
  -d '{"name": "John Doe", "email": "john@example.com", "password": "password"}'
```

### Login User

```bash
curl -X POST http://localhost/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email": "john@example.com", "password": "password"}'
``` 

### Get User Info

```bash
curl -X GET http://localhost/api/v1/user \
  -H "Authorization: Bearer {token}"
``` 

### Create Event

```bash
curl -X POST http://localhost/api/v1/events \
  -H "Authorization: Bearer {token}" \  
  -H "Content-Type: application/json" \
  -d '{"title": "Event Title", "description": "Event Description", "date": "2024-01-01"}'
```

### Update Event

```bash
curl -X PUT http://localhost/api/v1/events/{eventId} \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"title": "Updated Title", "description": "Updated Description", "date": "2024-01-02"}'
```

### Delete Event

```bash
curl -X DELETE http://localhost/api/v1/events/{eventId} \
  -H "Authorization: Bearer {token}"
``` 

### Sync Events

```bash
curl -X POST http://localhost/api/v1/events/sync \
  -H "Authorization: Bearer {token}"
``` 

### Import Events

```bash
curl -X POST http://localhost/api/v1/events/import \
  -H "Authorization: Bearer {token}"
``` 

### Logout User

```bash
curl -X POST http://localhost/api/v1/logout \
  -H "Authorization: Bearer {token}"
```

## CSV Import Format

Example CSV format for event import:

```
title,description,start_time,end_time,reminder_time,participants
Team Meeting,Monthly sync,2025-03-20 14:00:00,2025-03-20 15:00:00,2025-03-18 13:45:00,john@example.com
Project Review,Q1 Review,2025-03-21 10:00:00,2025-03-21 11:30:00,2025-03-19 09:45:00,bob@example.com
```
