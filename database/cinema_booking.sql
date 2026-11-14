-- ============================================================
-- CineBook — Cinema Booking Database
-- Import this file via phpMyAdmin or mysql CLI
-- ============================================================

CREATE DATABASE IF NOT EXISTS cinema_booking
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE cinema_booking;

-- ============================================================
-- TABLE: users
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100)        NOT NULL,
    email       VARCHAR(150)        NOT NULL UNIQUE,
    password    VARCHAR(255)        NOT NULL,
    phone       VARCHAR(20)         DEFAULT NULL,
    role        ENUM('user','admin') NOT NULL DEFAULT 'user',
    created_at  TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: movies
-- ============================================================
CREATE TABLE IF NOT EXISTS movies (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title        VARCHAR(200)        NOT NULL,
    genre        VARCHAR(100)        NOT NULL,
    duration     SMALLINT UNSIGNED   NOT NULL COMMENT 'minutes',
    synopsis     TEXT                DEFAULT NULL,
    poster       VARCHAR(255)        NOT NULL DEFAULT 'default.jpg',
    rating       ENUM('G','PG','PG-13','R','NC-17') NOT NULL DEFAULT 'PG-13',
    status       ENUM('now_showing','coming_soon','ended') NOT NULL DEFAULT 'now_showing',
    release_date DATE                DEFAULT NULL,
    created_at   TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: showtimes
-- ============================================================
CREATE TABLE IF NOT EXISTS showtimes (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    movie_id    INT UNSIGNED NOT NULL,
    show_date   DATE         NOT NULL,
    show_time   TIME         NOT NULL,
    hall        VARCHAR(50)  NOT NULL DEFAULT 'Hall 1',
    price       DECIMAL(10,2) NOT NULL DEFAULT 55000.00,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: seats
-- ============================================================
CREATE TABLE IF NOT EXISTS seats (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    showtime_id  INT UNSIGNED NOT NULL,
    seat_row     CHAR(1)      NOT NULL COMMENT 'Row A through H',
    seat_number  TINYINT UNSIGNED NOT NULL COMMENT '1 through 10',
    status       ENUM('available','booked') NOT NULL DEFAULT 'available',
    UNIQUE KEY uq_seat (showtime_id, seat_row, seat_number),
    FOREIGN KEY (showtime_id) REFERENCES showtimes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: bookings
-- ============================================================
CREATE TABLE IF NOT EXISTS bookings (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED NOT NULL,
    showtime_id     INT UNSIGNED NOT NULL,
    booking_code    VARCHAR(20)  NOT NULL UNIQUE,
    total_seats     TINYINT UNSIGNED NOT NULL DEFAULT 1,
    total_price     DECIMAL(10,2)    NOT NULL,
    status          ENUM('pending','confirmed','cancelled') NOT NULL DEFAULT 'confirmed',
    created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)     REFERENCES users(id)     ON DELETE CASCADE,
    FOREIGN KEY (showtime_id) REFERENCES showtimes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: booking_details
-- ============================================================
CREATE TABLE IF NOT EXISTS booking_details (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id  INT UNSIGNED NOT NULL,
    seat_id     INT UNSIGNED NOT NULL,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (seat_id)    REFERENCES seats(id)    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- SEED: Default Users
-- password for both = "password"
-- Hash generated with: password_hash('password', PASSWORD_BCRYPT)
-- ============================================================
INSERT IGNORE INTO users (name, email, password, role) VALUES
('Administrator', 'admin@cinema.com',
 '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77bAHi', 'admin'),
('John Doe', 'john@example.com',
 '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77bAHi', 'user');

-- ============================================================
-- SEED: Sample Movies
-- ============================================================
INSERT IGNORE INTO movies (title, genre, duration, synopsis, poster, rating, status, release_date) VALUES
('Avengers: Secret Wars',   'Action, Sci-Fi',    150,
 'The Avengers assemble once more to face a threat that spans across the multiverse, challenging everything they know.',
 'avengers.jpg',     'PG-13', 'now_showing',  '2026-05-01'),

('Interstellar 2',          'Sci-Fi, Drama',     169,
 'A new crew ventures beyond the known galaxy on a desperate mission to find humanity a second home.',
 'interstellar.jpg', 'PG-13', 'now_showing',  '2026-04-15'),

('The Dark Knight Returns', 'Action, Crime',     152,
 'Bruce Wayne dons the cape once more to defend Gotham from an unstoppable foe who knows his every move.',
 'batman.jpg',       'PG-13', 'now_showing',  '2026-06-01'),

('Dune: Messiah',           'Sci-Fi, Adventure', 165,
 'Paul Atreides grapples with the consequences of his rise to power as Emperor of Arrakis.',
 'dune.jpg',         'PG-13', 'now_showing',  '2026-03-20'),

('Moana 3',                 'Animation, Family', 100,
 'Moana sets sail on a brand-new adventure across the ocean alongside her beloved companions.',
 'moana.jpg',        'G',     'coming_soon',  '2026-12-01'),

('Spider-Man: Beyond',      'Action, Adventure', 130,
 'Peter Parker faces his most personal battle yet when a villain from another timeline targets everyone he loves.',
 'spiderman.jpg',    'PG-13', 'coming_soon',  '2027-02-14');

-- ============================================================
-- SEED: Showtimes (today + next 2 days)
-- ============================================================
INSERT IGNORE INTO showtimes (movie_id, show_date, show_time, hall, price) VALUES
-- Movie 1 (Avengers)
(1, CURDATE(),                            '10:00:00', 'Hall 1', 55000),
(1, CURDATE(),                            '13:30:00', 'Hall 2', 55000),
(1, CURDATE(),                            '19:00:00', 'Hall 1', 65000),
(1, DATE_ADD(CURDATE(), INTERVAL 1 DAY),  '10:00:00', 'Hall 1', 55000),
(1, DATE_ADD(CURDATE(), INTERVAL 1 DAY),  '19:00:00', 'Hall 1', 65000),
(1, DATE_ADD(CURDATE(), INTERVAL 2 DAY),  '14:00:00', 'Hall 2', 55000),

-- Movie 2 (Interstellar 2)
(2, CURDATE(),                            '11:00:00', 'Hall 3', 60000),
(2, CURDATE(),                            '17:00:00', 'Hall 3', 60000),
(2, DATE_ADD(CURDATE(), INTERVAL 1 DAY),  '14:00:00', 'Hall 3', 60000),

-- Movie 3 (Dark Knight)
(3, CURDATE(),                            '12:00:00', 'Hall 2', 55000),
(3, CURDATE(),                            '20:30:00', 'Hall 2', 65000),
(3, DATE_ADD(CURDATE(), INTERVAL 2 DAY),  '20:30:00', 'Hall 2', 65000),

-- Movie 4 (Dune)
(4, CURDATE(),                            '14:00:00', 'Hall 1', 60000),
(4, DATE_ADD(CURDATE(), INTERVAL 1 DAY),  '17:30:00', 'Hall 1', 60000);
