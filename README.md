# DIU Roommate Finder
### A Simple Roommate Finding Website for Daffodil International University Students

---

## Project Overview
This is a simple PHP + MySQL web application where DIU students can post and search for roommate ads.

---

## Tech Stack
- **Backend:** PHP (plain, no framework)
- **Database:** MySQL (via MySQLi)
- **Frontend:** HTML + Tailwind CSS (CDN)
- **Hosting:** InfinityFree

---

## Folder Structure
```
diu-roommate-finder/
├── config/
│   └── db.php              ← Database connection + helper functions
├── includes/
│   ├── header.php          ← Common navbar (included in all pages)
│   └── footer.php          ← Common footer (included in all pages)
├── auth/
│   ├── login.php           ← Login page
│   ├── register.php        ← Register page (only @diu.edu.bd emails)
│   └── logout.php          ← Logout (destroys session)
├── ads/
│   ├── post.php            ← Post a new roommate ad
│   ├── details.php         ← View full ad details
│   └── delete.php          ← Delete an ad
├── dashboard/
│   └── index.php           ← User's own ads (active/expired)
├── admin/
│   └── index.php           ← Admin panel (view + delete all ads)
├── uploads/
│   └── .htaccess           ← Security: blocks PHP execution in uploads
├── index.php               ← Homepage (ad cards + filter)
└── database.sql            ← SQL schema to import in phpMyAdmin
```

---

## Setup Instructions (InfinityFree)

### Step 1: Set Up the Database
1. Log in to InfinityFree control panel
2. Go to **MySQL Databases → phpMyAdmin**
3. Select your database: `if0_41672259_diu_web_project`
4. Click **Import** tab
5. Upload `database.sql` file
6. Click **Go** to run the SQL

### Step 2: Upload Files
1. Log in to InfinityFree File Manager (or use FTP)
2. Go to `htdocs/` folder (this is your website root)
3. Upload ALL files from this project (keeping the folder structure)
4. Make sure the `uploads/` folder exists and has write permissions (chmod 755)

### Step 3: Test
- Visit your website URL
- Try registering with a `@diu.edu.bd` email
- Post an ad and check if it appears on the homepage

---

## Default Admin Account
- **Email:** admin@diu.edu.bd
- **Password:** password
- ⚠️ IMPORTANT: Change this password immediately after first login!

To change admin password:
1. Go to phpMyAdmin
2. Run: `UPDATE users SET password = '<hash>' WHERE email = 'admin@diu.edu.bd';`
3. Replace `<hash>` with a proper password hash generated using PHP's `password_hash()`

---

## Key Features
| Feature | Description |
|---------|-------------|
| Registration | Only @diu.edu.bd emails allowed |
| Login/Logout | Session-based authentication |
| Post Ads | Title, description, rent, location, images, contact |
| Filter Ads | By gender, room type, location, rent range |
| Ad Details | Full details + contact info only for logged-in users |
| Auto Expiry | Ads expire after 7/15/30/45 days |
| Dashboard | View your own ads with Active/Expired status |
| Admin Panel | View all ads, delete inappropriate ones |

---

## Database Tables
| Table | Description |
|-------|-------------|
| `users` | Registered students |
| `ads` | Roommate ads |
| `ad_images` | Images for each ad |
