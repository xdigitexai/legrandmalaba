# Le Grand Malaba

Social media boosting dashboard with automated engagement services, payment integration, and comprehensive customer management.

## Overview

Le Grand Malaba is a full-featured social media marketing panel. It automates the delivery of social media engagement services, integrates payment gateways (AssanPay, mobile money), provides child/agent panel management, and offers a complete customer dashboard for order tracking and balance management.

Built with PHP and Laravel components, it runs on standard cPanel/Apache hosting.

## Features

- **Service catalog** — multi-category social media engagement services
- **Automated order processing** — cron-based delivery with retry logic
- **Multi-gateway payments** — AssanPay, mobile money integration
- **Child panel management** — agent/reseller panel creation and management
- **User dashboard** — order history, balance, service status
- **Admin panel** — secure administration with role-based access
- **API integration** — service provider connectivity

## Technology Stack

- **Backend:** PHP, Laravel (partial)
- **Database:** MySQL
- **Frontend:** HTML5, CSS3, JavaScript
- **Server:** Apache / cPanel
- **Payments:** AssanPay, mobile money

## Project Structure

```
legrandmalaba/
├── index.php              # Landing page
├── dashboard.php          # User dashboard
├── api.php                # Service provider API
├── add-funds.php          # Balance top-up
├── add_funds.php          # Alternative payment
├── childpanels.php        # Agent/reseller management
├── admin-cron.php         # Admin cron management
├── assanpay_cron.php      # Payment processing
├── composer.json          # PHP dependencies
└── includes/              # Core modules & configuration
```

## Requirements

- PHP 7.4 or higher
- MySQL 5.7+
- Apache with mod_rewrite
- Composer
- cURL PHP extension

## Installation

```bash
git clone https://github.com/xdigitexai/legrandmalaba.git
cd legrandmalaba
composer install
# Import database schema
mysql -u root -p < database/schema.sql
# Configure database connection
```

## Configuration

Edit your database configuration file:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

**Never commit real credentials or API keys.**

## Cron Jobs

The platform requires cron jobs for automated order processing:

```
*/5 * * * * php /path/to/legrandmalaba/cron/process_orders.php
```

## Security

- Prepared statements for database queries
- Password hashing with bcrypt
- Session-based user management
- Admin security middleware

## Status

Active — serving Le Grand Malaba customers.

## License

Proprietary. All rights reserved.
