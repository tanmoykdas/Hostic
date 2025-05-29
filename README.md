# Hostic – Online Doctor Appointment System

Hostic is a full-featured web platform for booking and managing doctor appointments online. It provides a seamless workflow for both patients and doctors, featuring secure authentication, appointment scheduling, session management, and profile customization.

---

## Screenshots

### Home Page
- Users select their role (patient or doctor) to log in.
- Explains the workflow for booking and managing appointments.
- Showcases partners (doctors and hospitals).

### Registration
- Role-based registration form.
- Doctors provide hospital details; patients fill in region and personal info.

### Login
- Clean login interface for both roles.
---

### Doctor Dashboard
- Doctor profile overview, including hospital, region, qualifications, department, age, and experience.
- Summary of total sessions and tickets sold.
- Quick access to manage sessions, edit profile, or logout.

### Manage Sessions
- Doctors can view and edit their session slots.
- Easily create new sessions by setting time, price, and ticket limits.
---

### Patient Dashboard
- Patient profile overview, including region, age, gender, and address.
- Quick links for editing profile, booking appointments, and viewing history.

### Book an Appointment
- Patients search for doctors by region, hospital, department, and date.
- View available session details and confirm bookings.

### Appointment History
- Patients can review all past appointments with detailed information.

---

## Features

### For Patients
- Register and manage a secure profile.
- Book appointments by searching for doctors based on region, hospital, department, and date.
- View upcoming appointments and detailed history.
- Update profile and upload a profile picture.

### For Doctors
- Register and manage a professional profile, including qualifications and hospital affiliation.
- Create, edit, and delete appointment sessions with time slots, prices, and ticket limits.
- Track total sessions and tickets sold.
- Update profile and upload a professional photo.

### Common
- Secure authentication and role-based access.
- Flash messaging for instant feedback.
- Responsive, modern user interface.
- Dynamic creation of regions and hospitals during registration if new.

---

## Project Structure

```
Backup/         # For backups
assets/         # Static assets (CSS, JS, images)
doctor/         # Doctor dashboard and features
patient/        # Patient dashboard and features
templates/      # Reusable HTML/PHP templates
uploads/        # Profile pictures and other uploads
config.php      # Database configuration and utility functions
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
   - Create a database (e.g., `doctor_appointment`) and import the required schema.
   - Update `config.php` with your database credentials.

3. **Run the application**
   - Place the project files in your web server’s root directory.
   - Open your browser and navigate to `http://localhost/Hostic/`.

---

## Security

- Passwords are hashed using PHP’s `password_hash`.
- Input is sanitized to protect against SQL injection.
- Session management for authentication and authorization.

---

## Customization

- **Styling:** Edit CSS in `assets/css/`.
- **Templates:** Change layout and content in `templates/`.
- **Database:** Extend the schema as needed for new features (e.g., notifications, payments).

---

## Contributing

1. Fork the repository.
2. Create your feature branch (`git checkout -b feature/YourFeature`)
3. Commit your changes.
4. Push to the branch.
5. Open a Pull Request.

---
