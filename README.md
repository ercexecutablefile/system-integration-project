# System Integration Project

Student Event Management integration project with three separate systems:

- Web system: PHP event management website
- Desktop system: C# Windows Forms event client
- API system: Separate PHP REST API

All CRUD operations pass through the API layer and use one shared MySQL database named `event_management`.

## Architecture

```text
PHP Web System
    |
    | HTTP + JSON API requests
    v
Separate PHP API System
    |
    v
Shared MySQL Database: event_management
    ^
    |
C# Desktop System
```

## Local XAMPP URLs

- Web app: `http://localhost/Project/Event-Management-System-using-PHP-MySQL-main/`
- API health check: `http://localhost/Project/Event-API/index.php/health`
- Swagger docs: `http://localhost/Project/Event-API/docs.php`
- phpMyAdmin database: `http://localhost/phpmyadmin/index.php?route=/database/structure&db=event_management`

## API Security

CRUD endpoints require this request header:

```http
X-API-Key: student-event-api-key-2026
```

The API also has simple per-IP rate limiting.

## Demo Login

- Email: `kaye.dela.cruz@student.edu`
- Password: `kaye1S23`

Desktop sample login:

- Username: `kaye`
- Email: `kayeexample@gmail.com`
- Password: `kaye1S23`

## Run Locally

1. Start Apache and MySQL in XAMPP.
2. Import `database/event_management.sql` into phpMyAdmin.
3. Copy these folders into `C:\xampp\htdocs\Project`:
   - `web/Event-Management-System-using-PHP-MySQL-main`
   - `api/Event-API`
   - `desktop/DesktopEventClient`
4. Open the web app URL in the browser.
5. Open Swagger docs or import the Postman collection from `postman/`.
6. Run the desktop client:

```powershell
dotnet run --project C:\xampp\htdocs\Project\DesktopEventClient
```

## Folder Guide

- `web/` - PHP website system
- `api/` - separate API system
- `desktop/` - C# desktop system
- `database/` - shared MySQL database export
- `postman/` - API testing collection
- `docs/` - project architecture notes

## Reports

The web system includes a functional Event Reports page that reads from `GET /reports`.
It shows live totals, event attendance performance, and website activity history logs for event and attendee changes.
