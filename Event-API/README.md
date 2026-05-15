# Student Event Integration API

This folder is the separate API system required by the system integration project.

## Local URLs

- Health check: `http://localhost/Project/Event-API/index.php/health`
- Swagger UI: `http://localhost/Project/Event-API/docs.php`
- Base API URL: `http://localhost/Project/Event-API/index.php`

## Security

All CRUD endpoints require:

```http
X-API-Key: student-event-api-key-2026
```

The API also applies a simple per-IP rate limit of 100 requests per minute.

## Main Endpoints

- `GET /events`
- `GET /events/{id}`
- `POST /events`
- `PUT /events/{id}`
- `DELETE /events/{id}`
- `GET /attendees`
- `POST /attendees`
- `PUT /attendees/{id}`
- `DELETE /attendees/{id}`
- `GET /reports` - event summary, event performance, and website activity history logs

## Example Postman Test

POST `http://localhost/Project/Event-API/index.php/events`

Headers:

```http
Content-Type: application/json
X-API-Key: student-event-api-key-2026
```

Body:

```json
{
  "event_name": "Student Orientation",
  "event_desc": "Freshmen orientation program",
  "event_date": "2026-06-01",
  "max_capacity": 50,
  "created_by": 1
}
```
