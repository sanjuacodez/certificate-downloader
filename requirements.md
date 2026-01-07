# Project Specification: Certificate Downloader for WordPress

## 1. Core Objective
A high-performance WordPress plugin allowing admins to manage student eligibility via custom database tables and providing students a frontend portal to verify details, preview, and download/print certificates.

## 2. Technical Architecture (QA & Standards)
- **Pattern:** Strict MVC (Model, View, Controller) separation.
- **Database:** Use a **Custom Database Table** (not wp_posts) for student records to ensure performance and data integrity.
- **PDF Engine:** Implement a **Factory Pattern** for PDF generation. 
    - Default: `dompdf` (allows HTML/CSS templates).
    - Requirement: Must be able to switch to `fpdf` or `tcpdf` by changing a single constant/config without rewriting business logic.
- **Security:** - `defined('ABSPATH') || exit;` in every file.
    - Nonce verification for all admin actions and frontend lookups.
    - CSRF protection on CSV imports.
    - Data sanitization (late escaping) on all outputs.

## 3. Data Schema (The Model)
### Table: `{prefix}_cert_students`
- `id`: BigInt (Primary Key)
- `admission_number`: VarChar(50) (UNIQUE INDEX) - Primary lookup key.
- `full_name`: VarChar(255)
- `email`: VarChar(100)
- `phone`: VarChar(20)
- `course_id`: BigInt (Foreign Key logic)
- `issue_status`: Enum('pending', 'issued')
- `download_count`: Int (Default 0)
- `created_at`: Datetime

### Table: `{prefix}_cert_courses`
- `id`: BigInt (Primary Key)
- `course_name`: VarChar(255)
- `template_config`: LongText (JSON storage for colors, fonts, and design settings)

## 4. Admin Features (The Controller)
- **Dashboard:** Overview of total certificates issued vs. pending.
- **Bulk Import:** CSV/Excel uploader. On import, check for duplicate `admission_number`.
- **General Settings:**
    - Certificate Orientation: Portrait or Landscape.
    - Branding: Upload Logo, Signature Image, and Footer Stamps.
    - Typography: Select fonts(Primary, Secondary fonts) and primary/secondary colors for the PDF.
- **Email Notifications:**
    - Toggle: "Send email on status change to Issued."
    - Feature: **Attach PDF to email.** Generate the certificate on-the-fly and attach via `wp_mail`.
    - Content: Customizable email body with tags like `{name}`, `{course}`, and `{download_url}`.
- **UI UX Design:**
    - Visual Feedback on Import: Show progress bar and success/failure messages.
    - Bulk Actions: Provide bulk actions for students and courses. Use WordPress List Table for students and courses. Allow admins to select multiple students to "Issue" or "Delete" in one click.
    - Live Previewer: Allow admins to preview the certificate before issuing it to students. Use an Iframe or AJAX-based Live Preview. As the admin changes colors or fonts, the certificate should update visually on the screen before they hit "Save."
    - Responsive Design: Ensure the admin interface is responsive and works well on mobile devices.


## 5. Frontend Experience (The View)
- **Shortcode:** `[certificate_lookup]`
- **Workflow:** 1. Student enters Admission Number and Name.
    2. System validates against custom DB table.
    3. If match found: Display an HTML/CSS Preview of the certificate.
    4. Provide "Download PDF" and "Print" buttons.
- **Micro-interactions:** > - Inline Validation: Show error messages inline as the user types.
    - Loading States: Show loading indicators when processing imports or generating certificates.
    - Success/Failure Feedback: Show success messages when imports are successful or when certificates are generated.
    - Responsive Design: Ensure the frontend interface is responsive and works well on mobile devices.
- Empty States: Show empty states when no data is available.
- Print vs. Download: Provide a clear distinction between Print and Download options.
- Print Preview: Allow students to preview the certificate before downloading it. Offer a dedicated Print Button that triggers the browser's print dialog specifically for the certificate container, ensuring CSS is optimized for paper.

## 6. Development Phases
1. **Phase 1:** Setup MVC folder structure and `blueprint.json`.
2. **Phase 2:** Database migration script using `dbDelta()` for custom tables.
3. **Phase 3:** Implement the Admin Settings UI using the WordPress Settings API but styled with modern CSS (Flexbox/Grid) for the Live Preview.
4. **Phase 4:** CSV Import logic and Student Management. Add the AJAX Progress Bar for CSV imports.
5. **Phase 5:** PDF Factory implementation (Base Class + Dompdf Driver). 
6. **Phase 6:** Frontend Shortcode and Emailer with attachments. Build the Responsive Shortcode Form. Ensure it looks great on mobile, as many students will check for their certificates on their phones.
7. **Phase 7:** Accessibility (a11y) Audit: Ensure form fields have proper <label> tags and the PDF generation supports Unicode (for special characters in names).