# Event Manager Pro

Event Manager Pro is a production-ready WordPress plugin that provides
event management, registration, filtering, REST API integration,
notifications, caching, and internationalization support.

---

## Features

### Custom Post Type
- Event custom post type
- Event Type taxonomy
- REST API enabled structure

### Admin Enhancements
- Event Date & Location meta fields
- Custom admin columns
- Sortable and filterable event list

### Frontend
- Archive and single event templates
- Event filtering (type, date range, search)
- Shortcode support `[events]`

### Registration System
- Public event registration form
- AJAX submission
- Duplicate registration protection
- Nonce verification

### REST API
Custom endpoints:
POST /wp-json/event-manager-pro/v1/events/{id}/register
GET /wp-json/event-manager-pro/v1/events/{id}/registrations/count


### Notifications
- Email notification on publish/update
- Debug logging support (development mode)

### Performance
- Transient-based caching
- Automatic cache invalidation

### Internationalization
- Translation ready
- Example `tr_TR` language file included

### Testing
- PHPUnit + WordPress testing framework
- CPT, meta, and REST endpoint tests

---

## Installation

1. Copy plugin into:
wp-content/plugins/event-manager-pro

2. Activate plugin from WordPress Admin.

3. Go to:
Settings → Permalinks → Save

to refresh rewrite rules.

---

## Usage

### Create Event
Admin → Events → Add New

Add:
- Event Date
- Location
- Event Type

---

### Display Events

Use shortcode:
[events]

---

### Registration

Users can register directly from the single event page.

---

## REST API Example

Register user:

```bash
curl -X POST \
http://example.com/wp-json/event-manager-pro/v1/events/10/register \
-d "name=John Doe" \
-d "email=john@example.com"
```

## Development

Enable debug logging:
define('WP_DEBUG', true);
## Running Tests

From plugin directory:

vendor/bin/phpunit

## Security

Nonce verification

Input sanitization

Capability checks

REST validation
## Author

Ali Vahap Aydın