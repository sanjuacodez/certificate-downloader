# Certificate Downloader for WordPress

A high-performance WordPress plugin for managing and issuing digital certificates to students. Features a complete admin management system, CSV bulk import, customizable certificate templates, and a frontend lookup portal.

## 📚 Documentation

- [Database Schema](DATABASE_SCHEMA.md) - Complete database structure and field descriptions
- [Project Status](PROJECT_STATUS.md) - Feature completion status and roadmap

## 🌟 Features

### For Administrators
- **Student Management**: Add, edit, and manage student records with photo uploads
- **Bulk Import**: CSV import with progress tracking, validation, and remote image download support
- **Certificate Templates**: Visual editor with live preview for designing certificates
- **Course Management**: Create and manage multiple courses with unique certificate designs
- **Email Notifications**: Automatic email notifications when certificates are issued
- **Dashboard**: Overview statistics of issued and pending certificates
- **Bulk Actions**: Issue, delete, or update multiple students at once
- **Accessibility**: ARIA labels, keyboard navigation, and screen reader support
- **Error Handling**: Comprehensive validation with detailed error messages

### For Students
- **Certificate Lookup**: Simple two-factor lookup form using admission number and email
- **Live Preview**: View certificate before downloading (optimized compact size)
- **PDF Download**: Download certificate as PDF with download counter
- **Mobile Responsive**: Works seamlessly on all devices
- **Secure Validation**: Case-insensitive email matching for better UX

### Certificate Customization
- **Flexible Design**: Customize colors, fonts, sizes, and positions
- **Multiple Sections**: Header, body, signatures, seals, footer, QR codes
- **Image Support**: Upload logos, signatures, seal images, background images
- **Student Photos**: Display student photos on certificates (with validation)
- **DomPDF Core Fonts**: Helvetica, Times-Roman, Courier (guaranteed compatibility)
- **Unicode Support**: Full UTF-8 support for special characters in names

### Quality & Accessibility
- **WCAG Compliant**: Proper labels, ARIA attributes, keyboard navigation
- **Empty States**: Helpful guidance when no data exists
- **Validation**: Image size (2MB max), file types, duplicate detection
- **Error Messages**: Detailed, actionable error messages for all operations
- **User Experience**: Intuitive interface with helpful hints and CTAs

## 📋 Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher

## 🚀 Installation

### From GitHub

1. Download the plugin ZIP or clone the repository:
   ```bash
   git clone https://github.com/yourusername/certificate-downloader.git
   ```

2. Upload to your WordPress plugins directory:
   ```
   wp-content/plugins/certificate-downloader/
   ```

3. Install dependencies (if composer.json exists):
   ```bash
   cd wp-content/plugins/certificate-downloader
   composer install --no-dev
   ```

4. Activate the plugin through the WordPress admin panel:
   - Go to **Plugins > Installed Plugins**
   - Find "Certificate Downloader"
   - Click **Activate**

5. The plugin will automatically create required database tables on activation.

### Manual Installation

1. Download the plugin files
2. Upload to `/wp-content/plugins/certificate-downloader/`
3. Ensure `vendor/` directory exists with DomPDF dependencies
4. Activate through WordPress admin

## 📖 Usage

### Admin Setup

1. **Navigate to Certificates > Courses**
   - Create a new course
   - Click "Edit" to customize the certificate template

2. **Design Your Certificate**
   - Use the visual editor to customize all elements
   - Upload logos, signatures, and seal images
   - Adjust colors, fonts, and sizes
   - Preview changes in real-time

3. **Add Students**
   - **Single**: Go to **Certificates > Students** and click "Add New"
   - **Bulk**: Import CSV file with student data
   - CSV format: `admission_number, full_name, email, phone, course_id, photo_url`

4. **Configure Settings**
   - Go to **Certificates > Settings**
   - **General Branding**:
     - Upload default logo using WordPress media library
     - Set organization name (available as `{organization_name}` placeholder)
   - **Email Notifications**:
     - Enable/disable automatic email notifications
     - Toggle PDF attachment to emails
     - Customize email subject and body with template tags

### Frontend Usage

1. Add the lookup shortcode to any page or post:
   ```
   [certificate_lookup]
   ```

2. Students enter their:
   - Admission Number
   - Full Name

3. Upon validation, they can:
   - Preview their certificate
   - Download as PDF
   - Print directly

## 📁 Database Schema

**Full documentation**: [DATABASE_SCHEMA.md](DATABASE_SCHEMA.md)

### cert_students Table
```sql
- id: Primary Key
- admission_number: Unique identifier (VARCHAR 50)
- full_name: Student name (VARCHAR 255)
- email: Email address (VARCHAR 100)
- phone: Phone number (VARCHAR 20)
- course_id: Foreign key to courses
- issue_status: 'pending' or 'issued'
- photo_url: URL to student photo (VARCHAR 255)
- download_count: Number of downloads (INT)
- created_at: Record creation date
- updated_at: Last update date
```

### cert_courses Table
```sql
- id: Primary Key
- course_name: Course title (VARCHAR 255)
- template_config: JSON configuration (LONGTEXT)
```

### Settings (WordPress Options)
```
- sjs_cert_logo_url: Default logo URL
- sjs_cert_organization_name: Organization name for certificates
- sjs_cert_enable_email: Enable email notifications (boolean)
- sjs_cert_attach_pdf: Attach PDF to emails (boolean)
- sjs_cert_email_subject: Email subject template
- sjs_cert_email_body: Email body template
```

## 🎨 Customization

### Certificate Template Configuration

The certificate template is stored as JSON in the `template_config` field. You can customize:

- **Header**: Logo, title, subtitle, colors
- **Body**: Certificate text, fonts, alignment
- **Student Details**: Name, course, date formatting
- **Signatures**: Multiple signature support with images
- **Seal/Badge**: Position and size
- **Footer**: Text, logo, styling
- **Background**: Background image or color
- **QR Code**: Enable/disable and positioning
- **Student Photo**: Size and position

### Fonts

Currently supports DomPDF core fonts:
- Helvetica (default)
- Times-Roman
- Courier

### Template Tags

Available placeholders for certificate templates and emails:

- `{name}` - Student's full name
- `{course}` - Course name
- `{admission_number}` - Student's admission number
- `{download_url}` - Certificate download URL (emails only)
- `{organization_name}` - Organization name from settings

### Adding Custom Styles

Modify `assets/css/admin.css` for admin styling or `assets/css/frontend.css` for frontend styles.

## 🔐 Security Features

- ABSPATH checks in all PHP files
- Nonce verification for all forms
- CSRF protection on imports
- Data sanitization and late escaping
- SQL injection prevention
- SVG sanitization for uploads

## 📸 CSV Import Format

```csv
admission_number,full_name,email,phone,course_id,photo_url
ADM001,John Doe,john@example.com,1234567890,1,https://example.com/photo.jpg
ADM002,Jane Smith,jane@example.com,0987654321,1,
```

**Notes:**
- `photo_url` is optional
- Supports remote URLs (JPG, PNG, GIF, SVG, WebP)
- Images are downloaded and stored locally
- Empty photo_url will show default placeholder

## 🐛 Troubleshooting

### Images Not Uploading from CSV
- Check WordPress upload directory permissions
- Ensure remote URLs are accessible
- Check debug.log for specific errors

### PDF Not Generating
- Verify DomPDF is installed in `vendor/` directory
- Check PHP memory limit (recommended: 256MB)
- Ensure GD or Imagick extension is enabled

### Certificate Not Found
- Verify admission number matches exactly (case-sensitive)
- Check that certificate status is "issued"
- Ensure course is properly configured

## 🤝 Contributing

Contributions are welcome! Please:

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License.

## 👨‍💻 Development

### Project Structure
```
certificate-downloader/
├── assets/
│   ├── css/          # Stylesheets
│   ├── js/           # JavaScript files
│   └── images/       # Plugin images
├── includes/
│   ├── Admin/        # Admin UI classes
│   ├── Controller/   # Controllers
│   ├── Core/         # Core plugin classes
│   ├── Model/        # Database models
│   └── Service/      # Services (Email, PDF)
├── templates/
│   ├── admin/        # Admin templates
│   └── frontend/     # Frontend templates
├── vendor/           # Composer dependencies
└── certificate-downloader.php  # Main plugin file
```

### Running Locally

1. Set up local WordPress environment
2. Clone repository to plugins directory
3. Run `composer install`
4. Activate plugin
5. Enable WordPress debug mode for development:
   ```php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   define('WP_DEBUG_DISPLAY', false);
   ```

## 🗺️ Roadmap

See [PROJECT_STATUS.md](PROJECT_STATUS.md) for detailed feature completion status and upcoming features.

### Planned Features
- PDF Factory Pattern (support for FPDF, TCPDF)
- Email template tags ({name}, {course}, {download_url})
- PDF attachment to emails
- Download counter tracking
- Certificate templates library
- Advanced analytics dashboard
- Bulk PDF generation with ZIP download

## 📞 Support

For issues, questions, or suggestions:
- Open an issue on GitHub
- Check existing issues for solutions
- Review documentation

## 🙏 Credits

- Built with [DomPDF](https://github.com/dompdf/dompdf)
- Uses WordPress core APIs and best practices
- Follows MVC architecture pattern

## 📊 Stats

- **Version**: 1.0.0
- **Tested up to**: WordPress 6.4
- **PHP Version**: 7.4+
- **Database Version**: 1.0

---

Made with ❤️ for educational institutions worldwide
