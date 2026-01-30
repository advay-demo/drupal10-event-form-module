# Event Registration – Drupal 10 Custom Module

## Overview
This custom Drupal 10 module provides a full event registration system with:

- Admin event configuration
- User registration form with AJAX filters
- Duplicate prevention
- Registration window validation
- Email notifications
- Admin listing dashboard
- CSV export
- Configurable admin email

No contrib modules used.

---

## Installation

1. Place module inside:
   web/modules/custom/event_registration

2. Enable module:
   /admin/modules

3. Clear cache:
   /core/rebuild.php

---

## URLs

Admin Event Configuration:
`/admin/config/event-registration`

User Registration Form:
`/event/register`

Admin Registration Dashboard:
`/admin/event-registrations`

CSV Export:
`/admin/event-registrations/export`

---

## Database Tables

### event_registration_event
Stores admin-created events:

- event_name
- category
- event_date
- reg_start_date
- reg_end_date
- created timestamp

### event_registration_entry
Stores registrations:

- event_id (FK)
- full_name
- email
- college
- department
- created timestamp

Duplicate prevention index:
(email + event_id)

---

## Validation Logic

- Required fields enforced
- No special characters in text fields
- Email format validated
- Duplicate registration blocked
- Registration allowed only within configured window

---

## AJAX Logic

User form:
Category → Event Date → Event Name filtering

Admin dashboard:
Event Date → Event filter → AJAX table update

---

## Email Logic

Uses Drupal Mail API (hook_mail)

Sends:
- confirmation to user
- optional admin notification

Admin email configured via Config API.

---

## Permissions

Custom permission:
view event registrations

Controls admin dashboard access.

---

## Technical Standards

- Drupal 10.x
- Form API
- Config API
- Schema API
- PSR-4
- No contrib modules
- Drupal coding standards followed
