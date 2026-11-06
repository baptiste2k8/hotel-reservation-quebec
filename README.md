**1. Project Description**

A Hotel Reservation System developed using PHP, MariaDB, HTML, and CSS for educational purposes.
The application includes hotel room management, customer reservations, authentication, reviews, and intentionally vulnerable modules for practicing software security testing.

**2. Requirements**

Kali Linux
Apache2
PHP with MySQL support
MariaDB
Git
Firefox or another web browser 

**3. Installation Instructions**

*Step 1 — Install the required software*

Open the Kali Linux terminal and execute:

sudo apt update
sudo apt install -y apache2 mariadb-server php php-mysql libapache2-mod-php git

Start Apache and MariaDB:

sudo systemctl enable --now apache2
sudo systemctl enable --now mariadb 

*Step 2 — Clone the GitHub repository*

cd /var/www/html
sudo git clone https://github.com/baptiste2k8/hotel-reservation-quebec.git hotel-quebec

The project will be installed in:
/var/www/html/hotel-quebec/

*Step 3 — Create the database*
Open MariaDB:

sudo mariadb

Execute:
CREATE DATABASE hotel_quebec
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

CREATE USER 'hotel_user'@'localhost'
IDENTIFIED BY 'admin!2026!';

GRANT ALL PRIVILEGES ON hotel_quebec.*
TO 'hotel_user'@'localhost';

EXIT;

*Step 4 — Import the database*
Go to the project directory:
cd /var/www/html/hotel-quebec

Import the complete database:
mariadb -u hotel_user -p hotel_quebec < database/hotel_quebec.sql

Enter the database password when prompted.
Verify that the tables were imported: mariadb -u hotel_user -p hotel_quebec -e "SHOW TABLES;"

*Step 5 — Verify the database configuration*
Open:
sudo nano /var/www/html/hotel-quebec/config/database.php

Verify that the configuration uses:
$host = "localhost";
$dbname = "hotel_quebec";
$username = "hotel_user";
$password = "admin!2026!;

**Step4-Access the application**
Open Firefox:

http://localhost/hotel-quebec/auth/login.php
