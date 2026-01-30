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

## Screenshots

### Module Enabled
<img width="1901" height="962" alt="image" src="https://github.com/user-attachments/assets/21941f75-77ca-41d0-942e-eb2b421d6ab9" />

### Event Configuration Form
<img width="1383" height="958" alt="image" src="https://github.com/user-attachments/assets/271fb19f-1e68-4864-916a-d86f15ae962c" />


### Registration Form
<img width="1448" height="967" alt="image" src="https://github.com/user-attachments/assets/d76baeb1-80b3-4839-82ef-96bb5cad0807" />
<img width="1295" height="686" alt="image" src="https://github.com/user-attachments/assets/4c2e9fd9-9a75-49ed-9db0-c469bc5584be" />


### AJAX Date → Various Filtering
<img width="447" height="397" alt="image" src="https://github.com/user-attachments/assets/f404df45-7c59-44d0-a19e-6f80d571eafb" />

### Admin Dashboard
<img width="1915" height="832" alt="image" src="https://github.com/user-attachments/assets/d90eb471-fa69-42cc-80d0-2100221865ff" />


### CSV Export
<img width="993" height="217" alt="image" src="https://github.com/user-attachments/assets/226471ab-e735-44eb-bd26-7c10ef17fe25" />


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


