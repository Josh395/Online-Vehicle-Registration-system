# Online Vehicle Registration System (OVRS)

A lightweight PHP + MySQL web application for vehicle registration, transfer of ownership, payments, and admin management. Built for local deployment (XAMPP/LAMP) and designed to demonstrate a full CRUD workflow with user verification, admin reviews, and PDF certificate generation.

## Features

- User registration and authentication
- Vehicle registration application submission with file uploads
- Admin dashboard to review, approve, reject applications
- Transfer of ownership workflow with document upload and admin approval
- Notifications system for users
- Payments integration placeholder (MobileMoney/Bank/Card)
- PDF generation for certificates (uses `tcpdf`)

## Quick GitHub description (one line)

Online Vehicle Registration System — PHP/MySQL app for vehicle registration, transfers, and admin management.

## Requirements

- PHP 7.4+ (or PHP 8.x)
- MySQL / MariaDB
- XAMPP / WAMP / LAMP (recommended for local testing)
- Composer (optional, for libraries like TCPDF)

## Installation

1. Clone the repository to your web root:

```bash
git clone <your-repo-url> Online_Vehicle_Registration_System
cd Online_Vehicle_Registration_System
```

2. Import the database schema using the provided SQL file `vor.sql` (use phpMyAdmin or MySQL CLI):

```bash
mysql -u root -p vor < vor.sql
```

3. Configure database credentials in `config.php` if needed (defaults target a local XAMPP setup):

- `DB_HOST` = `localhost`
- `DB_NAME` = `vor`
- `DB_USER` = `root`
- `DB_PASS` = `` (empty by default)

4. Place the project in your web server document root (e.g., `C:\xampp\htdocs`) and open:

```
http://localhost/Online_Vehicle_Registration_System/
```

## Admin account

A default admin user is created in `vor.sql`:

- Username: `admin`
- Password: `admin123` (hashed in SQL)

Change the password after first login using the admin settings if available.

## Notes & Configuration

- Email sending on localhost uses PHP `mail()`; configure SMTP in `php.ini` or use a library (PHPMailer) with SMTP credentials for production.
- `tcpdf/` is included for PDF generation. If you update dependencies, run `composer install` where appropriate.
- Uploaded files are stored under `uploads/` and `transfer_docs/` — ensure the web server has write permissions for these folders.
- For security, remove sample data and change default credentials before publishing publicly.

## Development

- Code entry points:
  - `index.php` — public landing and application flow
  - `admin/` — administrative interface
  - `generate-transfer-certificate.php` — PDF generator called on approval

- Styling is in `style.css` and `admin/admin-style.css`.

## How to push to GitHub (local steps)

```bash
# inside project root
git init
git add .
git commit -m "Initial commit: Online Vehicle Registration System"
# create a repo on GitHub, then add remote and push
git remote add origin https://github.com/<your-username>/<repo-name>.git
git branch -M main
git push -u origin main
```

## Contributing

Contributions are welcome. Please sanitize inputs, secure file uploads, and add tests for critical flows before submitting PRs.

## License

Add your preferred license (e.g., MIT) to a `LICENSE` file before publishing.

---

If you want, I can:

- Add this `README.md` file to the repository (already done),
- Create a short `LICENSE` file (MIT),
- Or generate a concise GitHub repo description and topics you can copy into the GitHub UI.

Tell me which next step you'd like.