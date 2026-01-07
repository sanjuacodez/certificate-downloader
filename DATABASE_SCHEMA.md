# Database Schema Documentation

## Overview

Certificate Downloader uses two custom database tables to manage student certificates and course configurations, plus WordPress options for plugin settings. Tables are created automatically upon plugin activation using WordPress's `dbDelta()` function.

## Table Prefix

All tables use the WordPress table prefix defined in `wp-config.php`:
- Default: `wp_`
- Actual table names: `{prefix}_cert_students` and `{prefix}_cert_courses`
- Settings stored in: `{prefix}_options`
- Settings stored in: `{prefix}_options`

---

## Table: `{prefix}_cert_students`

Stores individual student records with certificate eligibility status.

### Schema

```sql
CREATE TABLE wp_cert_students (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    admission_number varchar(50) NOT NULL,
    full_name varchar(255) NOT NULL,
    email varchar(100) NOT NULL,
    phone varchar(20) NOT NULL,
    course_id bigint(20) NOT NULL,
    issue_status enum('pending', 'issued') DEFAULT 'pending' NOT NULL,
    photo_url varchar(255) DEFAULT '' NOT NULL,
    download_count int(11) DEFAULT 0 NOT NULL,
    created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
    updated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY admission_number (admission_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Column Descriptions

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | bigint(20) | NO | AUTO_INCREMENT | Primary key, auto-incrementing student ID |
| `admission_number` | varchar(50) | NO | - | Unique student admission number (used for lookup) |
| `full_name` | varchar(255) | NO | - | Student's full name (displayed on certificate) |
| `email` | varchar(100) | NO | - | Student's email address (for notifications) |
| `phone` | varchar(20) | NO | - | Student's phone number |
| `course_id` | bigint(20) | NO | - | Foreign key reference to `cert_courses.id` |
| `issue_status` | enum | NO | 'pending' | Certificate status: 'pending' or 'issued' |
| `photo_url` | varchar(255) | NO | '' | URL to student photo (supports local and remote URLs) |
| `download_count` | int(11) | NO | 0 | Number of times certificate has been downloaded |
| `created_at` | datetime | NO | '0000-00-00 00:00:00' | Record creation timestamp |
| `updated_at` | datetime | NO | '0000-00-00 00:00:00' | Record last update timestamp |

### Indexes

- **PRIMARY KEY**: `id`
- **UNIQUE KEY**: `admission_number` - Ensures no duplicate admission numbers

### Constraints

- `admission_number` must be unique across all records
- `issue_status` can only be 'pending' or 'issued'
- `course_id` should reference a valid course in `cert_courses` table

### Usage Notes

- **Lookup Key**: `admission_number` is used as the primary lookup field in frontend forms
- **Photo URLs**: Can store local WordPress media URLs or remote URLs (downloaded and cached locally)
- **Download Counter**: Currently not automatically incremented (pending implementation)
- **Timestamps**: Should be updated via model methods (not enforced by database)

---

## Table: `{prefix}_cert_courses`

Stores course information and certificate template configurations.

### Schema

```sql
CREATE TABLE wp_cert_courses (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    course_name varchar(255) NOT NULL,
    template_config longtext NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Column Descriptions

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | bigint(20) | NO | AUTO_INCREMENT | Primary key, auto-incrementing course ID |
| `course_name` | varchar(255) | NO | - | Name of the course (e.g., "Web Development", "Data Science") |
| `template_config` | longtext | NO | - | JSON-encoded certificate template configuration |

### Indexes

- **PRIMARY KEY**: `id`

### Template Configuration (JSON)

The `template_config` field stores a JSON object with the following structure:

```json
{
    "schema_version": "1.0.0",
    "header": {
        "logo_url": "http://example.com/logo.png",
        "logo_width": "120",
        "logo_height": "80",
        "title": "Certificate of Completion",
        "title_font_size": "36",
        "title_color": "#1a1a1a",
        "subtitle": "This is to certify that",
        "subtitle_font_size": "18",
        "subtitle_color": "#666666"
    },
    "body": {
        "certificate_text": "has successfully completed the course",
        "certificate_text_font_size": "16",
        "certificate_text_color": "#333333",
        "name_font_size": "28",
        "name_color": "#000000",
        "course_font_size": "22",
        "course_color": "#1a73e8"
    },
    "student_photo": {
        "enabled": true,
        "width": "100",
        "height": "100",
        "position_x": "50",
        "position_y": "50"
    },
    "student_details": {
        "show_admission_number": true,
        "show_email": false,
        "show_phone": false,
        "show_date": true,
        "date_format": "F j, Y"
    },
    "signatures": [
        {
            "label": "Director",
            "name": "John Doe",
            "image_url": "http://example.com/signature1.png",
            "position_x": "100",
            "position_y": "650"
        }
    ],
    "seal_badge": {
        "enabled": true,
        "image_url": "http://example.com/seal.png",
        "width": "100",
        "height": "100",
        "position_x": "450",
        "position_y": "650"
    },
    "footer": {
        "text": "www.example.com | info@example.com",
        "font_size": "10",
        "color": "#666666",
        "footer_logo_url": "",
        "footer_logo_width": "60",
        "footer_logo_height": "40"
    },
    "background": {
        "background_color": "#ffffff",
        "background_image_url": "",
        "opacity": "0.1"
    },
    "qr_code": {
        "enabled": true,
        "data": "{admission_number}",
        "size": "80",
        "position_x": "50",
        "position_y": "650"
    },
    "fonts": {
        "heading_font": "Times-Roman",
        "body_font": "Helvetica"
    }
}
```

### Template Configuration Fields

#### Header Section
- Logo image URL, dimensions
- Certificate title and styling
- Subtitle text and styling

#### Body Section
- Main certificate text
- Student name styling
- Course name styling

#### Student Photo
- Enable/disable photo display
- Photo dimensions and position

#### Student Details
- Toggle visibility of admission number, email, phone
- Date format for certificate issue date

#### Signatures
- Array of signature objects
- Each signature has label, name, image URL, and position

#### Seal/Badge
- Enable/disable seal
- Seal image URL, dimensions, and position

#### Footer
- Footer text and styling
- Optional footer logo

#### Background
- Background color or image
- Opacity control for background image

#### QR Code
- Enable/disable QR code
- QR code data (supports template tags)
- Size and position

#### Fonts
- Heading font (Times-Roman, Helvetica, Courier)
- Body font (Times-Roman, Helvetica, Courier)

### Usage Notes

- **JSON Validation**: Template config should always be valid JSON
- **Image URLs**: All image URLs should be accessible (local or remote)
- **Fonts**: Only DomPDF core fonts are currently supported
- **Positions**: X/Y positions are in pixels relative to PDF dimensions (A4: 595x842px landscape, 842x595px portrait)

---

## WordPress Options (`{prefix}_options`)

Plugin settings are stored using WordPress's native options API.

### Settings Options

| Option Name | Type | Default | Description |
|------------|------|---------|-------------|
| `sjs_cert_logo_url` | string | '' | Default logo URL for certificates (uploaded via media library) |
| `sjs_cert_organization_name` | string | '' | Organization name displayed on certificates |
| `sjs_cert_enable_email` | boolean | 0 | Enable automatic email notifications when certificate is issued |
| `sjs_cert_attach_pdf` | boolean | 1 | Attach PDF certificate to notification emails |
| `sjs_cert_email_subject` | string | 'Your Certificate is Ready!' | Email subject line template |
| `sjs_cert_email_body` | text | (default template) | Email body template with support for placeholders |

### Template Placeholders

The following placeholders are supported in email templates and certificate templates:

| Placeholder | Replaced With | Available In |
|------------|---------------|--------------|
| `{name}` | Student's full name | Email, Certificate |
| `{course}` | Course name | Email, Certificate |
| `{admission_number}` | Student's admission number | Email, Certificate |
| `{download_url}` | Certificate download URL | Email only |
| `{organization_name}` | Organization name from settings | Email, Certificate |

### Default Email Body Template

```
Dear {name},

Congratulations! You have successfully completed the course {course}.

You can download your certificate from the following link:
{download_url}

We have also attached your certificate to this email.

Best regards,
Academy Team
```

### Accessing Settings

**PHP:**
```php
// Get setting
$logo_url = get_option('sjs_cert_logo_url');
$org_name = get_option('sjs_cert_organization_name', 'Your Academy');
$attach_pdf = get_option('sjs_cert_attach_pdf', 1);

// Update setting
update_option('sjs_cert_logo_url', 'https://example.com/logo.png');
update_option('sjs_cert_enable_email', 1);
```

**Database Query:**
```sql
SELECT option_value FROM wp_options 
WHERE option_name = 'sjs_cert_logo_url';
```

---

## Relationships

```
cert_courses (1) ←→ (many) cert_students
```

- One course can have many students
- Each student belongs to exactly one course
- Relationship is logical (not enforced by foreign key constraint)

---

## Data Access

### Model Classes

- **Student Model**: `SJS_Cert\Model\Student`
  - Located: `includes/Model/Student.php`
  - Methods: `get_all()`, `get_by_id()`, `get_by_admission_number()`, `create()`, `update()`, `delete()`, `get_stats()`

- **Course Model**: `SJS_Cert\Model\Course`
  - Located: `includes/Model/Course.php`
  - Methods: `get_all()`, `get_by_id()`, `create()`, `update()`, `delete()`, `get_with_normalized_config()`

### Direct Database Access

```php
global $wpdb;
$table_students = $wpdb->prefix . 'cert_students';
$table_courses = $wpdb->prefix . 'cert_courses';

// Example: Get all issued certificates
$results = $wpdb->get_results(
    "SELECT * FROM $table_students WHERE issue_status = 'issued'"
);
```

---

## Migration & Versioning

### Current Version
- **Database Schema Version**: 1.0
- **Template Schema Version**: 1.0.0

### Activation
Tables are created automatically on plugin activation via:
- File: `includes/Core/Activator.php`
- Method: `Activator::activate()`
- Function: Uses WordPress `dbDelta()` for safe table creation/updates

### Future Migrations
When schema changes are needed:
1. Update SQL in `Activator.php`
2. Increment schema version
3. Add migration method if data transformation needed
4. Use `dbDelta()` for safe updates (adds/modifies columns without data loss)

---

## Performance Considerations

### Indexing
- **admission_number**: UNIQUE index for fast lookups
- Consider adding indexes for:
  - `course_id` if querying students by course frequently
  - `issue_status` if filtering by status frequently
  - `created_at` for chronological queries

### JSON Storage
- `template_config` stored as LONGTEXT (JSON)
- Parsed on read, serialized on write
- Not directly queryable (consider caching for frequently accessed configs)

### Optimization Tips
1. Cache course configurations in WordPress transients
2. Add database indexes for frequent queries
3. Consider pagination for large student lists (already implemented via WP_List_Table)
4. Use prepared statements for all queries (already implemented via Models)

---

## Backup & Export

### Recommended Backup Strategy
1. Use WordPress backup plugins (UpdraftPlus, BackWPup)
2. Manual MySQL dump:
   ```bash
   mysqldump -u username -p database_name wp_cert_students wp_cert_courses > certificate_backup.sql
   ```

### CSV Export
Students can be exported via admin interface:
- Current: Manual CSV download
- Format: admission_number, full_name, email, phone, course_id, photo_url

---

## Security Notes

1. **SQL Injection**: All queries use prepared statements via `$wpdb->prepare()`
2. **Data Sanitization**: Input sanitized via WordPress functions before database insert
3. **Output Escaping**: Data escaped on output to prevent XSS
4. **Access Control**: Database operations restricted to users with `manage_options` capability
5. **Photo URLs**: Validated and sanitized before storage

---

## Troubleshooting

### Tables Not Created
```sql
-- Check if tables exist
SHOW TABLES LIKE 'wp_cert_%';

-- Manually create tables using SQL from Activator.php
```

### Check Table Structure
```sql
DESCRIBE wp_cert_students;
DESCRIBE wp_cert_courses;
```

### Verify Data Integrity
```sql
-- Check for students with invalid course_id
SELECT s.* FROM wp_cert_students s
LEFT JOIN wp_cert_courses c ON s.course_id = c.id
WHERE c.id IS NULL;

-- Check for invalid issue_status values
SELECT * FROM wp_cert_students
WHERE issue_status NOT IN ('pending', 'issued');
```

---

## Change Log

### Version 1.0 (January 2026)
- Initial database schema
- Added `photo_url` field to students table
- Added `download_count` field to students table
- Added `created_at` and `updated_at` timestamps to students table
- JSON-based template configuration for courses

---

## References

- WordPress Database API: https://developer.wordpress.org/apis/handbook/database/
- dbDelta Function: https://developer.wordpress.org/reference/functions/dbdelta/
- WordPress Table Prefix: https://developer.wordpress.org/advanced-administration/security/hardening/#database-security
