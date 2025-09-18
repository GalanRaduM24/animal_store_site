# Virtual Store

This is a simple PHP-based virtual store web application. It allows users to browse products, manage a shopping cart, and simulate purchases. The project is structured for easy setup and extension.

## Features
- Product browsing by department
- Shopping cart management (add, remove, update, view)
- Purchase simulation
- User navigation components
- Database setup and reset scripts

## Project Structure
```
index.php                # Main entry point
reset_db.php             # Script to reset the database
setup.sql                # SQL script to set up the database schema and initial data
test_db.php              # Script to test database connection
cart/                    # Shopping cart operations
components/              # Reusable UI components (navbars)
config/                  # Database configuration
departments/             # Department listing and browsing
images/                  # Product images
products/                # Product management and inventory
```

## Setup Instructions
1. **Requirements:**
   - PHP 7.x or higher
   - MySQL or MariaDB
   - Web server (e.g., Apache, XAMPP)

2. **Database Setup:**
   - Import `setup.sql` into your MySQL database to create tables and insert sample data.
   - Update `config/database.php` with your database credentials.
   - (Optional) Use `reset_db.php` to reset the database to its initial state.

3. **Running the Application:**
   - Place the project folder in your web server's root directory (e.g., `htdocs` for XAMPP).
   - Access `index.php` via your browser (e.g., `http://localhost/virtual_store/`).

## About This Project

This project was developed as a university assignment to learn and practice PL/SQL concepts in a web application context. The backend uses SQL scripts for database setup and management, providing a practical environment for applying PL/SQL skills alongside PHP and web development.

## Notes
- Product images are located in the `images/` directory.
- The application is for demonstration and educational purposes.

## Screenshots

Below are example screenshots of the main pages. Replace the placeholder files in the `screenshots/` directory with actual images as needed.

| Page                        | Screenshot                                      |
|-----------------------------|-------------------------------------------------|
| Home Page                   | ![Home Page](screenshots/home.png)         |
| Products Page               | ![Products Page](screenshots/products.png)  |
| Cart Page                   | ![Cart Page](screenshots/cart.png)          |
| Inventory Page              | ![Inventory Page](screenshots/inventory.png) |
