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
curl --location 'http://localhost/api/register' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--data-raw '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}'
```

### Login User

```bash
curl --location 'http://localhost/api/login' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer token' \
--data-raw '{
    "email": "test@example.com",
    "password": "password123"
}'
``` 

### Get User Info

```bash
curl --location 'http://localhost/api/user' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer 2|EhgUSkBaO7bfAXboME7lRqG3FUF8r318XUzk0ALW20c71d58'
``` 

### Create Event

```bash
curl -X POST http://localhost/api/v1/events \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer token' \
--data-raw '{
    "title": "Team Meeting (test)",
    "description": "Weekly sync meeting (test)",
    "start_time": "2025-03-28 1:00:00",
    "end_time": "2025-03-29 14:00:00",
    "metadata": {
        "location": "Conference Room V"
    },
    "reminder_time": "2025-03-28 00:30:00",
    "participants": ["john@example.com"]
}'
```

### Update Event

```bash
curl --location --request PUT 'http://localhost/api/v1/events/EVENT-1739736105-c0PT948N' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer token' \
--data '{
    "title": "Updated Team Meeting",
    "description": "Rescheduled weekly sync",
    "start_time": "2025-04-22 11:00:00",
    "end_time": "2025-04-22 12:00:00"
}'
```

### Delete Event

```bash
curl --location --request DELETE 'http://localhost/api/v1/events/EVENT-1739731899-O9QZYMZl' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer token'
``` 

### Sync Events

```bash
curl --location 'http://localhost/api/v1/events/sync' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer token' \
--data-raw '{
  "events": [
    {
      "event_id": "EVENT-1739730425-W0d45HYv", // give event id from previous response
      "title": "Meeting 121322",
      "description": "third meeting",
      "start_time": "2024-03-20T10:00:00Z",
      "end_time": "2024-03-20T11:00:00Z",
      "status": "upcoming",
      "is_online": true,
      "reminder_time": "2025-03-18 00:30:00",
      "participants": ["johnnny@example.com"]
    },
    {
      "title": "Meeting 2122",
      "description": "Second meeting",
      "start_time": "2024-03-20T14:00:00Z",
      "end_time": "2024-03-20T15:00:00Z",
      "status": "upcoming",
      "is_online": true,
      "reminder_time": "2025-03-28 00:30:00",
      "participants": ["john@example.com"]
    }
  ]
}'
``` 

### Import Events

```bash
curl --location 'http://localhost/api/v1/events/import' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer token' \
--form 'file=@"/Users/test/test12/events.csv"'
``` 

### Logout User

```bash
curl --location --request POST 'http://localhost/api/logout' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer token'
```

## CSV Import Format

Example CSV format for event import:

```
title,description,start_time,end_time,reminder_time,participants
Team Meeting,Monthly sync,2025-03-20 14:00:00,2025-03-20 15:00:00,2025-03-18 13:45:00,john@example.com
Project Review,Q1 Review,2025-03-21 10:00:00,2025-03-21 11:30:00,2025-03-19 09:45:00,bob@example.com
```
