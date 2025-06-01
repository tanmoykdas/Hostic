# Hostic – Online Doctor Appointment System

**Hostic** is a comprehensive web-based platform designed for efficient online doctor appointment scheduling and management. The system streamlines workflows for both patients and doctors, offering secure authentication, flexible appointment scheduling, session management, and customizable profiles.

---

## Key Features

### For Patients
- Role-based registration and secure profile management
- Search and book appointments by region, hospital, department, and date
- Access to upcoming appointments and detailed appointment history
- Profile editing and photo upload

### For Doctors
- Professional registration with details such as qualifications and hospital affiliation
- Creation, editing, and deletion of appointment sessions (including time slots, pricing, and ticket limits)
- Dashboard with statistics on sessions and appointments
- Profile editing and photo upload

### For All Users
- Secure authentication with role-based access
- Real-time feedback through flash messaging
- Modern, responsive user interface
- Dynamic addition of regions and hospitals during registration
- Session-based authentication and authorization
- Password hashing and input sanitization for security

---

## System Overview

- **Home Page:** Role selection (patient or doctor), workflow explanation, and partner showcase
- **Registration:** Role-based forms; doctors provide hospital details, patients provide region and personal information
- **Login:** Clean, unified interface for all roles
- **Doctor Dashboard:** Profile overview, session/ticket statistics, session management, and profile editing
- **Patient Dashboard:** Profile overview, links to profile editing, appointment booking, and history
- **Session Management:** Doctors can create, edit, and manage session slots
- **Booking & History:** Patients can search for doctors, book appointments, and review their appointment history

---

## Project Structure

```
Backup/         # Database and file backups
assets/         # Static assets (CSS, JS, images)
doctor/         # Doctor-specific dashboard and logic
patient/        # Patient-specific dashboard and logic
templates/      # Shared HTML/PHP templates
uploads/        # Uploaded profile images and documents
config.php      # Database configuration and utilities
index.php       # Landing page
login.php       # Login handler
register.php    # Registration handler
logout.php      # Logout handler
```

---

## Getting Started

1. **Clone the repository**
    ```bash
    git clone https://github.com/tanmoykdas/Hostic.git
    cd Hostic
    ```

2. **Configure your environment**
    - Install PHP and MySQL.
    - Create a database (e.g., `doctor_appointment`) and import the schema.
    - Update `config.php` with your database credentials.

3. **Run the application**
    - Deploy the project files to your web server's root directory.
    - Access the application at `http://localhost/Hostic/`.

---

## Security Considerations

- Passwords are securely hashed using PHP’s `password_hash`.
- All user input is sanitized to mitigate SQL injection and other vulnerabilities.
- Session management ensures proper authentication and authorization.

---

## Customization

- **Styling:** Modify CSS in `assets/css/`.
- **Templates:** Edit layout and content in `templates/`.
- **Database:** Extend the schema for new features such as notifications or payment integration.

---

## Contribution Guidelines

1. Fork the repository.
2. Create a new feature branch (`git checkout -b feature/YourFeature`).
3. Commit your changes with clear messages.
4. Push to your branch and open a pull request.

---

## Support & Contact

For questions, suggestions, or contributions, please open an issue or contact the maintainer via [GitHub](https://github.com/tanmoykdas/Hostic/issues).

---

Thank you for your interest in Hostic!
