# Implementation Plan - Reports & Email

## Goal Description
Add reporting capabilities, email notifications with calendar integration, client history autocomplete, and UI improvements.

## Proposed Changes

### Database Layer
#### [MODIFY] [database/schema.sql](file:///d:/Clientes/JefeNman/Cafe-Pelotero/database/schema.sql)
- Add `client_email` column to `turns` table.
- Add `payment_method` column to `turns` (or rely on `transactions` table, but `turns` is easier for reporting). Let's sync them.

### Backend (PHP)
#### [NEW] [api/smtp.php](file:///d:/Clientes/JefeNman/Cafe-Pelotero/api/smtp.php)
- A minimal SMTP class to send emails using the provided credentials.
- Function to generate `.ics` content.

#### [NEW] [api/clients.php](file:///d:/Clientes/JefeNman/Cafe-Pelotero/api/clients.php)
- Endpoint to search distinct emails/names from `turns` table for autocomplete.

#### [MODIFY] [api/turns.php](file:///d:/Clientes/JefeNman/Cafe-Pelotero/api/turns.php)
- `create`:
    - Accept `email` and `payment_method`.
    - Trigger `send_turn_email` function.
- `history`:
    - Allow filtering by date range (for reports).

#### [NEW] [api/export.php](file:///d:/Clientes/JefeNman/Cafe-Pelotero/api/export.php)
- Generate CSV output for a given date range.

### Frontend
#### [MODIFY] [admin.php](file:///d:/Clientes/JefeNman/Cafe-Pelotero/admin.php)
- **Nav**: Improve button styling (CSS).
- **New Turn**:
    - Add `Email` input with datalist/autocomplete.
    - Add `Payment Method` select.
- **Reports View**:
    - Add a new tab/view for "Reportes".
    - Date range picker (Daily, Weekly, Monthly, Custom).
    - Buttons: "Exportar CSV", "Imprimir / PDF".
- **Settings**:
    - Debug/Verify why some pricing options might be hidden (likely just need to ensure the loop covers them).

#### [MODIFY] [assets/css/style.css](file:///d:/Clientes/JefeNman/Cafe-Pelotero/assets/css/style.css)
- Styling for new inputs and report tables.
- Print stylesheet for PDF generation via browser print.
