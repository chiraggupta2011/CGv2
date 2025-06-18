# CG Shoes - E-commerce Website

A fully functional e-commerce website for selling footwear, built with PHP, MySQL, HTML, CSS, Bootstrap, and JavaScript.

## 🚀 Features

### Customer Features
- **User Authentication**: Register, login, and logout functionality
- **Product Browsing**: Browse products by category with search and filtering
- **Product Details**: Detailed product pages with image galleries
- **Shopping Cart**: Add, remove, and update cart items
- **Checkout Process**: Complete order placement
- **Order History**: View past orders and order details
- **User Profile**: Manage personal information

### Admin Features
- **Admin Dashboard**: Overview with statistics and recent activities
- **Product Management**: Add, edit, and delete products
- **Order Management**: View and update order status
- **User Management**: View customer information
- **Admin Logs**: Track admin actions
- **Image Upload**: Upload product images

### Technical Features
- **Responsive Design**: Mobile-friendly interface using Bootstrap 5
- **Security**: Password hashing, input sanitization, SQL injection prevention
- **Session Management**: Secure user sessions
- **Database**: MySQL with proper relationships and constraints
- **Modern UI**: Clean, professional design with animations

## 🛠️ Tech Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, Bootstrap 5, JavaScript
- **Icons**: Font Awesome 6
- **Server**: Apache/Nginx (XAMPP/WAMP/MAMP)

## 📁 Project Structure

```
/cg-shoes/
│
├── /admin/                      # Admin dashboard
│   ├── index.php               # Admin dashboard
│   ├── manage-products.php     # Product management
│   ├── manage-orders.php       # Order management
│   ├── manage-users.php        # User management
│   └── logs.php                # Admin logs
│
├── /assets/
│   ├── /css/
│   │   └── style.css           # Custom styles
│   ├── /js/
│   │   └── script.js           # Custom JavaScript
│   └── /images/                # Product & UI images
│
├── /includes/
│   ├── header.php              # Site header
│   ├── footer.php              # Site footer
│   ├── db.php                  # Database connection
│   └── functions.php           # Utility functions
│
├── /user/
│   ├── cart.php                # Shopping cart
│   ├── cart-actions.php        # Cart AJAX handler
│   ├── orders.php              # Order history
│   └── profile.php             # User profile
│
├── /products/
│   └── details.php             # Product details page
│
├── /auth/
│   ├── login.php               # User login
│   ├── register.php            # User registration
│   └── logout.php              # User logout
│
├── index.php                   # Homepage
├── shop.php                    # Product listing
├── about.php                   # About page
├── contact.php                 # Contact page
├── database.sql                # Database setup
└── README.md                   # This file
```

## 🗄️ Database Structure

### Tables
1. **users** - Customer and admin accounts
2. **categories** - Product categories
3. **products** - Product information
4. **orders** - Customer orders
5. **order_items** - Products within orders
6. **admin_logs** - Admin action tracking

## 🚀 Installation & Setup

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- XAMPP, WAMP, or MAMP (recommended for local development)

### Step 1: Clone/Download Project
```bash
# Clone the repository or download the files
# Place the project in your web server directory
# Example: C:\xampp\htdocs\cg-shoes\
```

### Step 2: Database Setup
1. Open your MySQL client (phpMyAdmin, MySQL Workbench, etc.)
2. Create a new database named `cg_shoes`
3. Import the `database.sql` file to set up tables and sample data

### Step 3: Configure Database Connection
1. Open `includes/db.php`
2. Update the database credentials:
```php
$host = 'localhost';
$dbname = 'cg_shoes';
$username = 'root';        // Your MySQL username
$password = '';            // Your MySQL password
```

### Step 4: Web Server Configuration
1. Start your web server (Apache) and MySQL
2. Navigate to `http://localhost/cg-shoes/` in your browser

### Step 5: Create Assets Directory
```bash
# Create the images directory for product uploads
mkdir assets/images
# Set appropriate permissions (755 for directories, 644 for files)
```

## 👤 Demo Accounts

### Admin Account
- **Email**: admin@cgshoes.com
- **Password**: admin123

### Customer Account
- **Email**: customer@cgshoes.com
- **Password**: customer123

## 🔧 Configuration

### File Permissions
Ensure the following directories are writable:
- `assets/images/` (for product image uploads)

### PHP Settings
Make sure these PHP settings are enabled:
- `file_uploads = On`
- `upload_max_filesize = 10M`
- `post_max_size = 10M`

### Security Notes
- Change default admin credentials after first login
- Update database credentials in production
- Enable HTTPS in production environment
- Regularly backup your database

## 🎨 Customization

### Styling
- Modify `assets/css/style.css` for custom styles
- Update Bootstrap theme colors in CSS variables
- Add custom fonts in the CSS file

### Functionality
- Extend functions in `includes/functions.php`
- Add new admin features in the `/admin/` directory
- Modify database queries for custom requirements

### Images
- Replace placeholder images in `assets/images/`
- Update hero images and team photos
- Add product images through admin panel

## 📱 Responsive Design

The website is fully responsive and works on:
- Desktop computers
- Tablets
- Mobile phones
- All modern browsers

## 🔒 Security Features

- Password hashing using PHP's `password_hash()`
- Input sanitization to prevent XSS attacks
- Prepared statements to prevent SQL injection
- Session management with security checks
- Admin access control

## 🚀 Deployment

### Local Development
1. Use XAMPP/WAMP/MAMP for local development
2. Access via `http://localhost/cg-shoes/`

### Production Deployment
1. Upload files to your web server
2. Configure database connection for production
3. Set up SSL certificate for HTTPS
4. Configure proper file permissions
5. Update admin credentials
6. Set up regular database backups

## 🐛 Troubleshooting

### Common Issues

**Database Connection Error**
- Check database credentials in `includes/db.php`
- Ensure MySQL service is running
- Verify database name exists

**Image Upload Issues**
- Check `assets/images/` directory permissions
- Verify PHP file upload settings
- Check file size limits

**Admin Access Issues**
- Verify admin account exists in database
- Check `is_admin` field is set to 1
- Clear browser cookies and cache

**Page Not Found Errors**
- Check web server configuration
- Verify file paths are correct
- Ensure .htaccess is properly configured

## 📞 Support

For support or questions:
- Check the FAQ section on the contact page
- Review the code comments for implementation details
- Ensure all prerequisites are met

## 📄 License

This project is created for educational and demonstration purposes.

## 🔄 Updates

### Version 1.0.0
- Initial release with all core features
- Admin dashboard and management tools
- Responsive design implementation
- Security features and validation

---

**Note**: This is a demonstration project. For production use, implement additional security measures, error handling, and performance optimizations. 