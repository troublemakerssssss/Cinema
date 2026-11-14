# 🎬 CineBook — Cinema Ticket Booking System

A simple, clean, and beginner-friendly cinema ticket booking web application inspired by TIX ID, built with **PHP Native**, **MySQL**, **Bootstrap 5**, and **AJAX**.

---

## 🚀 Features

### User Features
- Register & Login with secure password hashing
- Browse Now Showing & Coming Soon movies
- Movie detail page with synopsis and showtimes
- Select showtime by date tab
- Interactive visual seat map (click to select)
- Real-time seat status: Available / Selected / Booked
- Instant booking with booking confirmation ticket
- My Tickets — full booking history with pagination
- User Dashboard with stats

### Admin Features
- Admin Dashboard with stats (movies, users, bookings, revenue)
- Manage Movies (Add / Edit / Delete)
- Manage Showtimes (Add / Edit / Delete)
- Manage Bookings (View, filter, update status)
- Manage Users (View, toggle role, delete)

---

## 🗂️ Project Structure

```
Cinema/
├── admin/                    # Admin panel pages
│   ├── partials/             # Admin header, sidebar, footer includes
│   ├── index.php             # Admin dashboard
│   ├── movies.php            # Manage movies
│   ├── showtimes.php         # Manage showtimes
│   ├── bookings.php          # Manage bookings
│   └── users.php             # Manage users
│
├── ajax/                     # AJAX endpoints
│   └── get_showtimes.php     # Returns showtimes JSON for a movie + date
│
├── assets/
│   ├── css/style.css         # Main stylesheet (dark blue theme)
│   ├── js/main.js            # Seat selection logic + AJAX calls
│   └── images/               # Movie poster images (place .jpg files here)
│
├── booking/
│   ├── seats.php             # Seat selection page
│   ├── process.php           # Booking processor (transaction-safe)
│   └── confirmation.php      # Booking confirmation / e-ticket
│
├── config/
│   └── database.php          # PDO connection + constants
│
├── database/
│   └── cinema_booking.sql    # Full SQL schema + seed data
│
├── includes/
│   ├── auth.php              # Session helpers, sanitize, formatters
│   ├── header.php            # Public navbar + HTML head
│   └── footer.php            # Public footer + JS includes
│
├── user/
│   ├── dashboard.php         # User dashboard with stats
│   └── my-tickets.php        # Booking history with ticket cards
│
├── index.php                 # Home page (hero + now showing + coming soon)
├── movies.php                # Movies listing with search/filter
├── movie-detail.php          # Movie detail + showtime selection
├── login.php                 # Login page
├── register.php              # Registration page
└── logout.php                # Session destroy + redirect
```

---

## ⚙️ Installation

### Requirements
- XAMPP / WAMP / LAMP (PHP 8.0+, MySQL 5.7+)
- A modern web browser

### Steps

1. **Copy project** to your web server root:
   ```
   C:\xampp\htdocs\Cinema\
   ```

2. **Import the database**:
   - Open `http://localhost/phpmyadmin`
   - Create a database named `cinema_booking`
   - Click **Import** → select `database/cinema_booking.sql`
   - Click **Go**

3. **Configure database credentials** (if different from defaults):
   - Edit `config/database.php`
   - Update `DB_HOST`, `DB_USER`, `DB_PASS`

4. **Configure BASE_URL**:
   - In `config/database.php`, update:
     ```php
     define('BASE_URL', 'http://localhost/Cinema');
     ```

5. **Visit the site**:
   ```
   http://localhost/Cinema/
   ```

---

## 🔐 Default Accounts

| Role  | Email               | Password   |
|-------|---------------------|------------|
| Admin | admin@cinema.com    | password   |
| User  | john@example.com    | password   |

> Change passwords after first login in production!

---

## 🗄️ Database Schema

### ERD Overview

```
users ──────────────────────────────────────┐
  id (PK)                                   │
  name, email, password, phone, role        │
                                            │ (1:N)
movies ─────────────────────┐              bookings
  id (PK)                   │               id (PK)
  title, genre, duration    │  (1:N)         user_id (FK → users)
  synopsis, poster          │               showtime_id (FK → showtimes)
  rating, status            │               booking_code
  release_date              ▼               total_seats, total_price, status
                        showtimes               │
                          id (PK)              │ (1:N)
                          movie_id (FK)   booking_details
                          show_date           id (PK)
                          show_time           booking_id (FK → bookings)
                          hall, price         seat_id (FK → seats)
                              │
                              │ (1:N)
                           seats
                             id (PK)
                             showtime_id (FK)
                             seat_row, seat_number
                             status (available/booked)
```

### Tables

| Table            | Key Fields                                      |
|------------------|-------------------------------------------------|
| `users`          | id, name, email, password, phone, role          |
| `movies`         | id, title, genre, duration, synopsis, poster, rating, status |
| `showtimes`      | id, movie_id↗, show_date, show_time, hall, price |
| `seats`          | id, showtime_id↗, seat_row, seat_number, status |
| `bookings`       | id, user_id↗, showtime_id↗, booking_code, total_seats, total_price, status |
| `booking_details`| id, booking_id↗, seat_id↗                       |

---

## 🎨 UI / Color Scheme

| Token      | Hex       | Usage                          |
|------------|-----------|--------------------------------|
| Dark Blue  | `#0d1b2a` | Page background                |
| Mid Blue   | `#1b2a3b` | Navbar, cards, sidebar         |
| Accent     | `#1a73e8` | Buttons, active states         |
| Warning    | `#f9a825` | Highlights, CTA buttons        |
| Light Gray | `#f4f6f9` | Light surfaces (rarely used)   |
| Muted      | `#8899aa` | Secondary text                 |

---

## 🛡️ Security

| Measure                   | Implementation                            |
|---------------------------|-------------------------------------------|
| Password hashing          | `password_hash()` + `password_verify()`   |
| SQL injection prevention  | PDO Prepared Statements throughout        |
| XSS prevention            | `htmlspecialchars()` via `sanitize()`     |
| Session authentication    | `$_SESSION` checked on every protected page |
| Input validation          | Type casting, `filter_var()`, trim        |
| Booking integrity         | MySQL transactions in `booking/process.php` |

---

## 🖼️ Adding Movie Posters

Place `.jpg` poster images in `assets/images/` and set the filename in the Admin → Movies panel.

Recommended poster dimensions: **300×450 px** (2:3 ratio).

---

## 📱 Pages

| Page                        | URL                                   |
|-----------------------------|---------------------------------------|
| Home                        | `/index.php`                          |
| Movies                      | `/movies.php`                         |
| Movie Detail                | `/movie-detail.php?id=X`              |
| Seat Selection              | `/booking/seats.php?showtime_id=X`    |
| Booking Confirmation        | `/booking/confirmation.php?booking_id=X` |
| Login                       | `/login.php`                          |
| Register                    | `/register.php`                       |
| User Dashboard              | `/user/dashboard.php`                 |
| My Tickets                  | `/user/my-tickets.php`                |
| Admin Dashboard             | `/admin/index.php`                    |
| Admin → Movies              | `/admin/movies.php`                   |
| Admin → Showtimes           | `/admin/showtimes.php`                |
| Admin → Bookings            | `/admin/bookings.php`                 |
| Admin → Users               | `/admin/users.php`                    |

---

## 📄 License

This project is for educational purposes. Free to use and modify for college projects.

---

