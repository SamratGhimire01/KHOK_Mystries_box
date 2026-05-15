# K HO K — Mystery Reward Platform

## Stack
- PHP (no framework) + MySQL
- HTML5 + CSS3 + Vanilla JS
- eSewa + Fonepay payments
- WhatsApp click-to-chat delivery

## Setup

### 1. Create the database
```bash
mysql -u root -p < khok_schema.sql
```

### 2. Configure your credentials
Edit `config/db.php` — set your MySQL user and password.

### 3. Place project in web server root
```bash
# Apache / XAMPP
cp -r khok/ /var/www/html/
# or
cp -r khok/ /opt/lampp/htdocs/
```

### 4. Enable mod_rewrite (Apache)
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### 5. Visit
```
http://localhost/khok/
```

## Admin login
- Email: admin@khok.com
- Password: admin123

## Folder Structure
```
khok/
├── config/          # db.php, app.php, session.php
├── core/            # router.php, helpers.php
├── components/      # header.php, footer.php
├── pages/           # one file per page
├── admin/
│   ├── pages/       # admin-only pages
│   └── components/  # admin partials
├── public/
│   ├── css/         # one CSS file per page + globals
│   ├── js/          # one JS file per page
│   └── assets/
├── uploads/
│   └── delivery_proof/
├── .htaccess
├── index.php
└── khok_schema.sql
```
