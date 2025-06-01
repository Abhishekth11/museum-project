National Museum of Art & Culture (NMAC) Website
===============================================

Website URL: http://infs7009.cdms.westernsydney.edu.au/your-student-id/museum/

Database Name: your-student-id_museum_db

Admin Login:
Username: admin@nmac.org
Password: admin123

Regular User Login:
Username: user@example.com
Password: user123

Setup Instructions:
==================
1. Create a database named 'your-student-id_museum_db'
2. Import the SQL file 'database.sql' into your database
3. Upload all files to the server directory
4. Update database connection details in 'includes/db.php':
   - DB_HOST: your database host
   - DB_USER: your database username
   - DB_PASS: your database password
   - DB_NAME: your database name
5. Ensure the 'uploads' directory has write permissions (chmod 755)
6. Create the following directories if they don't exist:
   - uploads/exhibitions/
   - uploads/events/
   - uploads/collections/

Features Implemented:
====================
✓ Responsive design for mobile and desktop
✓ PHP/MySQL backend for dynamic content
✓ Admin dashboard for managing exhibitions, events, and users
✓ AJAX for dynamic content loading and form submissions
✓ Chart.js for data visualization in the admin dashboard
✓ Dark/Light mode toggle with cookie persistence
✓ Search functionality across exhibitions, events, and collections
✓ User authentication and registration system
✓ Newsletter subscription with AJAX
✓ Event booking system
✓ Image upload functionality for admin
✓ Form validation and error handling
✓ SQL injection protection using prepared statements
✓ Session management and security

Technology Stack:
================
- Frontend: HTML5, CSS3, JavaScript (ES6+)
- Backend: PHP 7.4+
- Database: MySQL 5.7+
- AJAX: Vanilla JavaScript Fetch API
- Charts: Chart.js 3.x
- Icons: Font Awesome 6.x
- Fonts: Google Fonts (Playfair Display, Raleway)

New Technology Implementation:
=============================
Chart.js has been implemented in the admin dashboard to provide:
1. Pie chart showing distribution of exhibition categories
2. Bar chart displaying upcoming events by month
3. Interactive and responsive data visualization
4. Real-time data fetching via AJAX API endpoints

File Structure:
==============
/
├── index.php                 # Homepage
├── exhibitions.php           # Exhibitions listing
├── events.php               # Events calendar
├── login.php                # User authentication
├── search.php               # Search functionality
├── logout.php               # User logout
├── includes/                # PHP includes
│   ├── db.php              # Database connection
│   ├── functions.php       # Helper functions
│   ├── header.php          # Site header
│   └── footer.php          # Site footer
├── admin/                   # Admin dashboard
│   ├── dashboard.php       # Main dashboard
│   ├── login.php           # Admin login
│   ├── api/                # API endpoints
│   └── css/                # Admin styles
├── api/                     # Public API endpoints
│   ├── subscribe.php       # Newsletter subscription
│   └── get-exhibitions.php # Exhibition data
├── js/                      # JavaScript files
│   ├── main.js             # Main functionality
│   ├── exhibitions.js      # Exhibition filters
│   └── auth.js             # Authentication
├── css/                     # Stylesheets
│   └── style.css           # Main styles
├── uploads/                 # File uploads
└── database.sql            # Database structure

Security Features:
=================
- Password hashing using PHP password_hash()
- SQL injection protection with prepared statements
- XSS protection with htmlspecialchars()
- CSRF protection for forms
- Session security and timeout
- Input validation and sanitization
- File upload restrictions and validation

Browser Compatibility:
=====================
- Chrome 80+
- Firefox 75+
- Safari 13+
- Edge 80+
- Mobile browsers (iOS Safari, Chrome Mobile)

Performance Optimizations:
=========================
- Optimized database queries with indexes
- Image optimization and lazy loading
- CSS and JavaScript minification ready
- Efficient AJAX requests with error handling
- Responsive images with appropriate sizing

Testing Checklist:
==================
□ Homepage loads with dynamic content
□ Exhibition filtering works with AJAX
□ Event calendar displays correctly
□ User registration and login functional
□ Admin dashboard accessible to admin users
□ Charts display data correctly
□ Search functionality returns results
□ Newsletter subscription works
□ Dark/light mode toggle persists
□ Mobile responsiveness on all pages
□ Form validation and error messages
□ Database CRUD operations work
□ File upload functionality (admin)
□ Session management and logout

Contact Information:
===================
For technical support or questions about this project:
Student ID: 22143847, 22167315
Course: INFS7009 Web Technologies
Institution: Western Sydney University

Last Updated: June 2024
