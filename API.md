# Certificate Downloader API Documentation

Complete API reference for developers working with the Certificate Downloader plugin.

## Table of Contents

- [Shortcodes](#shortcodes)
- [Template Functions](#template-functions)
- [Hooks & Filters](#hooks--filters)
- [Model Classes](#model-classes)
- [Service Classes](#service-classes)
- [JavaScript Events](#javascript-events)

---

## Shortcodes

### `[certificate_lookup]`

Displays the certificate lookup form on any page or post.

**Usage:**
```php
[certificate_lookup]
```

**Attributes:**
None currently supported.

**Output:**
Renders a form with:
- Admission Number field
- Email field
- Submit button
- Certificate preview area (displayed after validation)
- Download PDF button (displayed after validation)

**Example:**
```php
// In post content or page
[certificate_lookup]

// Programmatically
echo do_shortcode('[certificate_lookup]');
```

---

## Template Functions

### `sjs_cert_get_template()`

Load a template part into a template.

**Signature:**
```php
function sjs_cert_get_template( string $slug, array $args = array() ): void
```

**Parameters:**
- `$slug` (string) - Template slug (e.g., 'admin/settings', 'frontend/certificate-lookup')
- `$args` (array) - Array of variables to pass to the template

**Example:**
```php
// Load admin settings template
sjs_cert_get_template( 'admin/settings', array(
    'courses' => $courses,
    'students' => $students
) );

// Load certificate preview
sjs_cert_get_template( 'frontend/certificate-preview', array(
    'student' => $student,
    'course' => $course,
    'config' => $template_config
) );
```

---

## Hooks & Filters

### Actions

#### `sjs_cert_before_student_create`

Fires before a student record is created.

**Parameters:**
- `$data` (array) - Student data to be inserted

**Example:**
```php
add_action( 'sjs_cert_before_student_create', function( $data ) {
    // Log student creation
    error_log( 'Creating student: ' . $data['full_name'] );
    
    // Modify data if needed (pass by reference in actual implementation)
    // $data['custom_field'] = 'value';
}, 10, 1 );
```

#### `sjs_cert_after_student_create`

Fires after a student record is successfully created.

**Parameters:**
- `$student_id` (int) - ID of the newly created student
- `$data` (array) - Student data that was inserted

**Example:**
```php
add_action( 'sjs_cert_after_student_create', function( $student_id, $data ) {
    // Send custom notification
    // Integrate with third-party service
    do_action( 'my_custom_integration', $student_id );
}, 10, 2 );
```

#### `sjs_cert_certificate_issued`

Fires when a certificate status changes to "issued".

**Parameters:**
- `$student_id` (int) - Student ID

**Example:**
```php
add_action( 'sjs_cert_certificate_issued', function( $student_id ) {
    // Send notification
    // Update external system
    // Log the event
}, 10, 1 );
```

### Filters

#### `sjs_cert_pdf_options`

Filter PDF generation options before creating the PDF.

**Parameters:**
- `$options` (array) - PDF options array
- `$student` (object) - Student object
- `$course` (object) - Course object

**Default Options:**
```php
array(
    'orientation' => 'landscape',
    'page_size' => 'A4',
    'dpi' => 96,
    'defaultFont' => 'Helvetica'
)
```

**Example:**
```php
add_filter( 'sjs_cert_pdf_options', function( $options, $student, $course ) {
    // Force portrait orientation for specific course
    if ( $course->course_name === 'Short Course' ) {
        $options['orientation'] = 'portrait';
    }
    
    // Increase DPI for higher quality
    $options['dpi'] = 150;
    
    return $options;
}, 10, 3 );
```

#### `sjs_cert_email_subject`

Filter email subject before sending.

**Parameters:**
- `$subject` (string) - Email subject after template tag replacement
- `$student` (object) - Student object
- `$course` (object) - Course object

**Example:**
```php
add_filter( 'sjs_cert_email_subject', function( $subject, $student, $course ) {
    // Prefix with course name
    return '[' . $course->course_name . '] ' . $subject;
}, 10, 3 );
```

#### `sjs_cert_email_body`

Filter email body before sending.

**Parameters:**
- `$body` (string) - Email body after template tag replacement
- `$student` (object) - Student object
- `$course` (object) - Course object

**Example:**
```php
add_filter( 'sjs_cert_email_body', function( $body, $student, $course ) {
    // Add custom footer
    $body .= "\n\n--- \nThis is an automated message from " . get_bloginfo( 'name' );
    return $body;
}, 10, 3 );
```

#### `sjs_cert_template_tags`

Add custom template tags for email and certificate templates.

**Parameters:**
- `$replacements` (array) - Array of tag => value pairs

**Default Tags:**
```php
array(
    '{name}' => $student->full_name,
    '{course}' => $course->course_name,
    '{admission_number}' => $student->admission_number,
    '{download_url}' => $download_url,
    '{organization_name}' => get_option('sjs_cert_organization_name')
)
```

**Example:**
```php
add_filter( 'sjs_cert_template_tags', function( $replacements ) {
    // Add custom tags
    $replacements['{site_url}'] = get_site_url();
    $replacements['{current_year}'] = date( 'Y' );
    $replacements['{support_email}'] = get_option( 'admin_email' );
    
    return $replacements;
}, 10, 1 );
```

#### `sjs_cert_csv_import_data`

Filter student data before importing from CSV.

**Parameters:**
- `$data` (array) - Student data array
- `$row` (array) - Raw CSV row data
- `$row_index` (int) - Row number in CSV

**Example:**
```php
add_filter( 'sjs_cert_csv_import_data', function( $data, $row, $row_index ) {
    // Add default course if not specified
    if ( empty( $data['course_id'] ) ) {
        $data['course_id'] = 1; // Default course ID
    }
    
    // Convert phone format
    $data['phone'] = preg_replace( '/[^0-9]/', '', $data['phone'] );
    
    return $data;
}, 10, 3 );
```

---

## Model Classes

### Student Model

**Namespace:** `SJS_Cert\Model\Student`

#### Methods

##### `create( array $data ): int|false`

Create a new student record.

**Parameters:**
- `$data` (array) - Student data
  - `admission_number` (string) required
  - `full_name` (string) required
  - `email` (string) required
  - `phone` (string) optional
  - `course_id` (int) required
  - `issue_status` (string) optional - 'pending' or 'issued'
  - `photo_url` (string) optional

**Returns:** Student ID on success, false on error

**Example:**
```php
$student_model = new \SJS_Cert\Model\Student();
$student_id = $student_model->create( array(
    'admission_number' => 'ADM001',
    'full_name' => 'John Doe',
    'email' => 'john@example.com',
    'phone' => '+1234567890',
    'course_id' => 1,
    'issue_status' => 'issued',
    'photo_url' => 'https://example.com/photo.jpg'
) );

if ( $student_id ) {
    echo "Student created with ID: $student_id";
}
```

##### `get_by_admission_number( string $admission_number ): object|null`

Retrieve student by admission number.

**Example:**
```php
$student_model = new \SJS_Cert\Model\Student();
$student = $student_model->get_by_admission_number( 'ADM001' );

if ( $student ) {
    echo $student->full_name;
    echo $student->email;
}
```

##### `update( int $id, array $data ): int|false`

Update student record.

**Example:**
```php
$student_model = new \SJS_Cert\Model\Student();
$updated = $student_model->update( 123, array(
    'issue_status' => 'issued',
    'download_count' => 5
) );
```

##### `delete( int $id ): int|false`

Delete student record.

**Example:**
```php
$student_model = new \SJS_Cert\Model\Student();
$deleted = $student_model->delete( 123 );
```

##### `get_all( array $args = array() ): array`

Get all students.

**Example:**
```php
$student_model = new \SJS_Cert\Model\Student();
$students = $student_model->get_all();

foreach ( $students as $student ) {
    echo $student->full_name . '<br>';
}
```

##### `get_stats(): object`

Get student statistics.

**Returns:**
```php
object(
    'total_students' => 100,
    'issued' => 75,
    'pending' => 25,
    'downloads' => 250
)
```

**Example:**
```php
$student_model = new \SJS_Cert\Model\Student();
$stats = $student_model->get_stats();

echo "Total Students: " . $stats->total_students;
echo "Issued: " . $stats->issued;
echo "Pending: " . $stats->pending;
echo "Total Downloads: " . $stats->downloads;
```

### Course Model

**Namespace:** `SJS_Cert\Model\Course`

#### Methods

##### `create( array $data ): int|false`

Create a new course.

**Example:**
```php
$course_model = new \SJS_Cert\Model\Course();
$course_id = $course_model->create( array(
    'course_name' => 'Web Development Bootcamp',
    'template_config' => json_encode( $config_array )
) );
```

##### `get( int $id ): object|null`

Get course by ID (with caching).

**Example:**
```php
$course_model = new \SJS_Cert\Model\Course();
$course = $course_model->get( 1 );

if ( $course ) {
    echo $course->course_name;
    $config = json_decode( $course->template_config, true );
}
```

##### `get_with_normalized_config( int $id ): object|null`

Get course with migrated template configuration.

**Example:**
```php
$course_model = new \SJS_Cert\Model\Course();
$course = $course_model->get_with_normalized_config( 1 );

// template_config is already an array
$heading_color = $course->template_config['header']['title']['color'];
```

##### `update( int $id, array $data ): int|false`

Update course (clears cache automatically).

**Example:**
```php
$course_model = new \SJS_Cert\Model\Course();
$updated = $course_model->update( 1, array(
    'course_name' => 'Advanced Web Development',
    'template_config' => json_encode( $new_config )
) );
```

##### `delete( int $id ): int|false`

Delete course (clears cache automatically).

---

## Service Classes

### NotificationService

**Namespace:** `SJS_Cert\Service\NotificationService`

#### Methods

##### `notify_issued( int $student_id ): void`

Send email notification when certificate is issued.

**Example:**
```php
$notification_service = new \SJS_Cert\Service\NotificationService();
$notification_service->notify_issued( 123 );
```

### PDF Factory

**Namespace:** `SJS_Cert\Service\PDF\PDFFactory`

#### Methods

##### `get_driver( string $driver_name ): PDFDriverInterface`

Get PDF driver instance.

**Supported Drivers:**
- `dompdf` - DomPDF driver (default)

**Example:**
```php
use SJS_Cert\Service\PDF\PDFFactory;

$pdf_driver = PDFFactory::get_driver( 'dompdf' );
$pdf_content = $pdf_driver->generate( $html, 'certificate.pdf', array(
    'orientation' => 'landscape',
    'page_size' => 'A4'
) );

// Output to browser
header( 'Content-Type: application/pdf' );
echo $pdf_content;
```

---

## JavaScript Events

### Certificate Editor Events

#### `sjsCert:previewUpdated`

Triggered after certificate preview is updated.

**Example:**
```javascript
jQuery(document).on('sjsCert:previewUpdated', function(event, config) {
    console.log('Preview updated with config:', config);
});
```

#### `sjsCert:templateApplied`

Triggered when a template is applied from the template reel.

**Example:**
```javascript
jQuery(document).on('sjsCert:templateApplied', function(event, templateName) {
    console.log('Template applied:', templateName);
});
```

### CSV Import Events

#### `sjsCert:importComplete`

Triggered when CSV import completes.

**Example:**
```javascript
jQuery(document).on('sjsCert:importComplete', function(event, result) {
    console.log('Imported:', result.imported);
    console.log('Errors:', result.errors);
});
```

---

## Complete Usage Example

```php
<?php
/**
 * Example: Create student, issue certificate, send email
 */

// 1. Create student
$student_model = new \SJS_Cert\Model\Student();
$student_id = $student_model->create( array(
    'admission_number' => 'ADM' . time(),
    'full_name' => 'Jane Smith',
    'email' => 'jane@example.com',
    'course_id' => 1,
    'issue_status' => 'pending'
) );

// 2. Update status to issued
if ( $student_id ) {
    $student_model->update( $student_id, array(
        'issue_status' => 'issued'
    ) );
    
    // 3. Send notification
    $notification_service = new \SJS_Cert\Service\NotificationService();
    $notification_service->notify_issued( $student_id );
    
    echo "Certificate issued and email sent!";
}

// 4. Get statistics
$stats = $student_model->get_stats();
echo "Total certificates issued: " . $stats->issued;
```

---

## Advanced Customization

### Custom PDF Template

```php
// Create custom template file: /wp-content/themes/your-theme/sjs-cert/certificate-custom.php
// Then use it:

sjs_cert_get_template( 'certificate-custom', array(
    'student' => $student,
    'course' => $course,
    'custom_data' => $my_data
) );
```

### Custom Validation

```php
add_filter( 'sjs_cert_before_student_create', function( $data ) {
    // Custom validation
    if ( empty( $data['phone'] ) ) {
        wp_die( 'Phone number is required!' );
    }
    
    return $data;
} );
```

### Modify Certificate Download Counter

```php
add_action( 'sjs_cert_after_pdf_download', function( $student_id ) {
    // Custom tracking
    update_post_meta( $student_id, 'last_download', current_time( 'mysql' ) );
}, 10, 1 );
```

---

## Support

For more information, see:
- [README.md](README.md) - General plugin documentation
- [DATABASE_SCHEMA.md](DATABASE_SCHEMA.md) - Database structure
- [PROJECT_STATUS.md](PROJECT_STATUS.md) - Development roadmap

## Version

API Version: 1.0.0  
Last Updated: January 2026
