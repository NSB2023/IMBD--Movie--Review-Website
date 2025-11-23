## Movie Review & Booking Website (PHP + MySQL)
This is a dynamic Movie Review and Ticket Booking web application built using PHP, MySQL, HTML and CSS. 
Users can browse movies, view details, post reviews, watch trailers, subscribe to newsletters and book movie tickets. 
Admin users can manage movies and view newsletter subscribers.

## Features
User:
 -User registration & login system
 -Browse movies with posters, genres and descriptions
 -Auto-sliding featured movie banner
 -Watch official trailers via YouTube modal
 -Movie review system (add & delete your own reviews)
 -Ticket booking (date, time, seat selection)
 -Newsletter subscription

Admin:
 - Admin login
 - Movie management
 - View newsletter subscribers

Project Structure:
## Project Structure
imbd/
├── index.php
├── movie.php
├── booking.php
├── login.php
├── register.php
├── logout.php
├── db.php
├── subscribe.php
├── admin_dashboard.php
├── admin_newsletter.php
├── style.css
├── images/
│   ├── inception.png
│   ├── interstellar.png
│   └── ...more posters
└── README.md


##Technologies Used
- PHP 8
- MySQL / phpMyAdmin
- MAMP (macOS)
- HTML5, CSS3
- JavaScript (for slider + trailer modal)

## How to Run This Project (macOS + MAMP)
   -Install MAMP
    Download & install from: https://www.mamp.info/en/
   -Move project into htdocs
   Place your project folder inside:
        /Applications/MAMP/htdocs/imbd
   -Start Servers
       Open MAMP → Start Servers (Ensure Apache & MySQL turn green)
   -Create the database
       1. Go to: http://localhost/phpmyadmin
       2. Create a database named:    imbd_db
       3. Import your SQL file or run the table-creation queries.
   -Run the website
       Open in browser:
       http://localhost:8888/imbd/
   -Login / Register
       - Register a new user
       - For admin access, set the user’s role = admin in phpMyAdmin manually
