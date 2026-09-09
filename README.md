# 📱 Gestion d'Absence - Flutter App

## 📌 Project Overview

This project is a **Student Attendance Management System** built using **Flutter (frontend)** and **PHP/MySQL (backend)**.

It allows administrators, teachers, and students to manage and track attendance efficiently.

---

## 🚀 Features

### 👨‍💼 Admin

* Manage students (add, view, delete)
* Manage teachers (add, view, delete)
* Manage classes
* Manage sessions (séances)

### 👨‍🏫 Teacher

* View assigned sessions
* Track student attendance

### 🎓 Student

* Login to account
* View attendance status

---

## 🛠️ Technologies Used

### Frontend

* Flutter
* Dart
* HTTP package

### Backend

* PHP (API)
* MySQL (Database)
* XAMPP (Local server)

---

## 📂 Project Structure

```
backend/                  # PHP API
 ├── admin/               # Classes, subjects, sessions, teachers, students
 ├── auth/login.php       # Login endpoint
 ├── config/database.php  # DB connection + CORS headers
 ├── enseignant/          # Teacher endpoints (sessions, roll call)
 ├── etudiant/            # Student endpoints (absences, profile)
 └── gest_absence.sql     # Schema + sample data
frontend/                 # Flutter app
 └── lib/
      ├── config/         # API configuration
      ├── models/         # Data models
      ├── screens/        # admin / enseignant / etudiant screens
      ├── services/       # API services
      └── main.dart       # Entry point
```

---

## ⚙️ Setup Instructions

### 1. Clone the repository

```bash
git clone <your-repo-url>
```

---

### 2. Install dependencies

```bash
cd frontend
flutter pub get
```

---

### 3. Configure Backend

* Copy the `backend` folder to:

```
C:\xampp\htdocs\gest_absence_api
```

* Start:

  * Apache ✅
  * MySQL ✅

* Open:

```
http://localhost/phpmyadmin
```

* Import `backend/gest_absence.sql` (creates the `gest_absence` database with sample data)

---

### 4. Configure API URL

Open:

```
frontend/lib/config/api_config.dart
```

Change `defaultValue`, or pass it at run time without editing the file:

```bash
flutter run --dart-define=API_BASE_URL=http://<your-ip>/gest_absence_api
```

Use your machine's LAN IP when testing on a real phone (`localhost` points to the phone itself).

---

### 5. Run the app

```bash
flutter run
```

---

## 🔑 Default Login Credentials

### Admin

* Email: [admin@school.tn](mailto:admin@school.tn)
* Password: admin123

### Teacher

* Email: [sami@school.tn](mailto:sami@school.tn)
* Password: prof123

### Student

* Email: [amine@school.tn](mailto:amine@school.tn)
* Password: etu123

---

## 🧪 Testing

* Ensure XAMPP is running
* Verify API endpoints in browser:

```
http://localhost/gest_absence_api/admin/etudiants.php
```

---

## ⚠️ Notes

* This project is for educational purposes
* Passwords are hashed with `password_hash()`; sample accounts stored in plain text are re-hashed on their first login
* All queries use prepared statements
* Database credentials can be set with `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` (defaults match XAMPP)
* CORS is open (`*`) for development; restrict it before deploying

---

## 👨‍💻 Authors

* Aymen Jeddou
* Abderrahim Neji

---
