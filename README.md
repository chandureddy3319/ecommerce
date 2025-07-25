# 🛒 Modern E-commerce Platform

A complete, responsive e-commerce website built with PHP, MySQL, and Bootstrap. Features both user and admin modules with full CRUD operations, shopping cart functionality, and order management.

## ✨ Features

### 👤 User Module
- **User Authentication**: Registration, login, and profile management
- **Product Browsing**: Browse products with filters, search, and categories
- **Shopping Cart**: Add, remove, and update cart items
- **Checkout System**: Complete order placement with shipping details
- **Order Management**: View order history and track order status
- **Responsive Design**: Mobile-friendly interface

### 🛠️ Admin Module
- **Admin Dashboard**: Overview statistics and quick actions
- **Product Management**: Add, edit, delete products with image upload
- **Category Management**: Organize products by categories
- **Order Management**: View and update order status
- **User Management**: View and manage user accounts
- **Reports**: Sales and inventory reports

## 🚀 Quick Start

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- XAMPP/WAMP/MAMP (recommended for local development)

### Installation

1. **Clone or Download the Project**
   ```bash
   git clone <repository-url>
   cd ecommerce
   ```

2. **Set Up Database**
   - Create a MySQL database named `ecommerce_db`
   - The database and tables will be automatically created when you first access the site

3. **Configure Database Connection**
   - Edit `config/database.php` if you need to change database credentials
   - Default settings:
     - Host: localhost
     - Username: root
     - Password: (empty)
     - Database: ecommerce_db

4. **Start Your Web Server**
   - If using XAMPP: Start Apache and MySQL services
   - Place the project in your web server's document root

5. **Access the Application**
   - User Frontend: `http://localhost/ecommerce/`
   - Admin Panel: `http://localhost/ecommerce/admin/`

## 🔐 Default Credentials

### Admin Access
- **URL**: `/admin/`
- **Username**: admin
- **Password**: admin123

### Test User Account
- Register a new account through the user registration page
- Or use the default admin credentials to create test orders

## 📁 Project Structure

```
ecommerce/
├── admin/                 # Admin panel files
│   ├── index.php         # Admin dashboard
│   ├── login.php         # Admin login
│   └── ...               # Other admin pages
├── ajax/                  # AJAX handlers
│   └── cart_actions.php  # Cart operations
├── assets/               # Static assets
│   ├── css/             # Stylesheets
│   ├── js/              # JavaScript files
│   └── images/          # Images and product photos
├── config/              # Configuration files
│   └── database.php     # Database connection
├── includes/            # PHP includes
│   ├── functions.php    # Helper functions
│   ├── header.php       # Site header
│   └── footer.php       # Site footer
├── index.php            # Home page
├── login.php            # User login
├── register.php         # User registration
├── products.php         # Product listing
├── product.php          # Product details
├── cart.php             # Shopping cart
├── checkout.php         # Checkout process
├── orders.php           # User orders
├── logout.php           # Logout functionality
└── README.md            # This file
```

## 🛍️ User Features

### Product Browsing
- View featured products on homepage
- Browse products by category
- Search products by name or description
- Filter by price range
- Sort by various criteria

### Shopping Cart
- Add products to cart
- Update quantities
- Remove items
- View cart total
- Cart preview in navigation

### Checkout Process
- Review cart items
- Enter shipping information
- Choose payment method
- Place order
- Order confirmation

### Order Management
- View order history
- Track order status
- View order details
- Download order receipts

## 🔧 Admin Features

### Dashboard
- Overview statistics
- Recent orders
- Low stock alerts
- Quick action buttons

### Product Management
- Add new products
- Edit existing products
- Upload product images
- Manage product categories
- Set featured products
- Update stock levels

### Order Management
- View all orders
- Update order status
- Process payments
- Generate invoices
- Track shipping

### User Management
- View user accounts
- Manage user profiles
- View user orders
- User statistics

## 🎨 Customization

### Styling
- Edit `assets/css/style.css` to customize the design
- Modify Bootstrap classes for layout changes
- Add custom CSS for specific components

### Functionality
- Extend `includes/functions.php` with new features
- Add new AJAX handlers in `ajax/` directory
- Modify database schema in `config/database.php`

### Content
- Update product information in the database
- Modify categories and their icons
- Customize admin dashboard widgets

## 🔒 Security Features

- Password hashing using PHP's `password_hash()`
- SQL injection prevention with prepared statements
- XSS protection with `htmlspecialchars()`
- Session-based authentication
- Input validation and sanitization
- CSRF protection (basic implementation)

## 📱 Responsive Design

- Mobile-first approach
- Bootstrap 5 framework
- Responsive navigation
- Touch-friendly interface
- Optimized for all screen sizes

## 🚀 Performance Optimization

- Optimized database queries
- Efficient image handling
- Minified CSS and JavaScript
- Caching strategies
- Lazy loading for images

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check database credentials in `config/database.php`
   - Ensure MySQL service is running
   - Verify database exists

2. **Image Upload Issues**
   - Check folder permissions for `assets/images/`
   - Ensure PHP has write access
   - Verify file size limits in PHP configuration

3. **Session Issues**
   - Check PHP session configuration
   - Ensure cookies are enabled
   - Verify session storage permissions

4. **404 Errors**
   - Check web server configuration
   - Verify file paths and permissions
   - Ensure .htaccess is properly configured

### Debug Mode
To enable debug mode, add this to the top of any PHP file:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## 📈 Future Enhancements

- Payment gateway integration (Stripe, PayPal)
- Email notifications
- Advanced search with filters
- Product reviews and ratings
- Wishlist functionality
- Multi-language support
- Advanced reporting
- Inventory management
- Shipping calculator
- Discount codes and coupons

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📄 License

This project is open source and available under the [MIT License](LICENSE).

## 📞 Support

For support and questions:
- Create an issue in the repository
- Check the troubleshooting section
- Review the code comments for guidance

## 🙏 Acknowledgments

- Bootstrap for the responsive framework
- Font Awesome for icons
- PHP community for best practices
- MySQL for database management

---

**Happy Shopping! 🛒✨** #   e c o m m e r c e  
 #   e c o m m e r c e  
 