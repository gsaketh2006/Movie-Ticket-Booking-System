# Movie Ticket Booking System

An online **Movie Ticket Booking Web Application** developed using **PHP, MySQL, HTML5, CSS3, JavaScript, and Bootstrap**.  
The system allows users to browse movies, select showtimes, choose seats, and book tickets online.  
It also includes an **admin panel** for managing movies, theatres, and show schedules.

🔗 **Live Website:** http://ticketbookingmoviemate.infy.uk

---

## Project Description
The Movie Ticket Booking System provides a complete end-to-end solution for online movie reservations.  
It supports user authentication, interactive seat selection, booking management, and payment tracking.  
An admin dashboard is provided for backend operations such as managing movies, theatres, and shows.

This project demonstrates practical implementation of **full-stack web development concepts** including frontend design, backend logic, and database management.

---

## Features

### User Features
- User Registration and Login
- Browse Movies with Posters and Details
- Select Showtimes
- Interactive Seat Selection
- Booking Summary and Confirmation

### Admin Features
- Add, Update, and Delete Movies
- Manage Theatres and Show Schedules
- View User Bookings
- Track Payment Status

---

## Tech Stack
- **Frontend:** HTML5, CSS3, JavaScript, Bootstrap  
- **Backend:** PHP  
- **Database:** MySQL  

---

## Database Schema

### Tables Used
- `add_movie(movie_id, name, description, genre, language, duration, release_date, rating, poster, banner)`
- `theatres(theatre_id, theatre_name, location, address)`
- `shows(show_id, theatre_id, movie_id, show_time, start_date, end_date)`
- `registration(id, name, phone, password)`
- `booking(booking_id, id, movie_id, show_id, seats_selected, cost, status, booking_date, no_of_seats_selected)`
- `payments(payment_id, booking_id, total, mode_of_payment, payment_status)`

---

## Contributors
- Guggilam Leela Naga Sai Sri Saketh
- Vemuri Sethu Sai Bhargav
- Syed Arsh
- Seshagiri Bharadwaj Sai

---

## How to Run Locally

1. Clone the repository
   ```bash
   git clone https://github.com/yourusername/movie-ticket-booking-system.git
