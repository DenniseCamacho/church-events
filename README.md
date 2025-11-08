# Project Name: Church Events Application

## Project Description
The Church Events Application is a PHP and MySQL-based web application designed to help churches manage events, volunteers, and administrative activities. The application includes user authentication, role-based access control, and CRUD functionality for managing churches and events. It follows the MVC (Model-View-Controller) design pattern to maintain a clean and organized code structure.

## Project Tasks
- **Task 1: Set up the development environment**
  - Installed XAMPP to run Apache, PHP, and MySQL
  - Configured database connection and local environment
  - Initialized Git and created a GitHub repository

- **Task 2: Design the database and models**
  - Created relational tables for users, roles, churches, and events
  - Added foreign keys and constraints to maintain data integrity

- **Task 3: Implement authentication and authorization**
  - Built registration and login functionality using PHP sessions
  - Added role-based access for Admin, Organizer, and Volunteer users

- **Task 4: Develop CRUD functionality**
  - **Admin:** Create, read, update, and delete churches and events
  - **Organizer:** Manage and view events under their assigned church
  - **Volunteer:** View events, sign up to volunteer, and remove themselves from the list

- **Task 5: Build helpers and security features**
  - Added CSRF token generation and validation
  - Used password hashing with PHP’s `password_hash()` function
  - Implemented input validation and session management

- **Task 6: Create dynamic views**
  - Developed reusable partials for headers, footers, and navigation
  - Built simple role-based dashboards for each user type

- **Task 7: Test and debug**
  - Verified CRUD routes and role permissions
  - Fixed session handling and routing logic issues

- **Task 8: Prepare for deployment**
  - Finalized MVC folder structure
  - Exported `.sql` database file for easy setup
  - Confirmed application runs on localhost via XAMPP

## Project Skills Learned
- Backend development with PHP and MySQL
- MVC architecture for code organization
- Role-based authentication and session handling
- Database design and relational modeling
- Secure coding practices (password hashing, CSRF protection)
- Version control with Git and GitHub
- Debugging and testing PHP applications

## Language Used
- **PHP:** For backend logic and MVC structure  
- **MySQL:** For database management  
- **HTML, CSS:** For frontend layout and structure  
- **XAMPP:** For local development environment setup

## Development Process Used
- **Incremental Development:** Focused first on backend functionality, followed by frontend structure  
- **MVC Pattern:** Separated application logic, database models, and views for scalability  
- **Version Control:** Managed updates and commits through Git and GitHub

## Notes
- Import the provided `.sql` file into phpMyAdmin before running the application  
- Update database credentials in `db.php` to match your local setup  
- Start Apache and MySQL through XAMPP  
- Access the application via `http://localhost/churchevents`  
- Ensure PHP sessions are enabled in your configuration  