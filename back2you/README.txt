BACK2YOU - LOST AND FOUND MANAGEMENT SYSTEM
===========================================

TECHNOLOGIES
- HTML, CSS, vanilla JavaScript
- PHP 8+
- MySQL/MariaDB through XAMPP
- PDO prepared statements

INSTALLATION
1. Copy the entire "back2you" folder into XAMPP's htdocs folder.
   Windows example: C:\xampp\htdocs\back2you
   macOS example: /Applications/XAMPP/xamppfiles/htdocs/back2you
2. Start Apache and MySQL in XAMPP.
3. Open http://localhost/phpmyadmin
4. Import database.sql.
5. Check config/database.php. Default XAMPP settings are:
   host=127.0.0.1, database=back2you_db, username=root, password blank.
6. Visit http://localhost/back2you/scripts/setup_superadmin.php ONCE.
7. Initial super administrator:
   Username: Ellagariah
   Password: 1admin
8. Immediately log in and change the password.
9. DELETE setup_superadmin.php after successful setup.
10. Visit http://localhost/back2you

IMPORTANT
- This is a school-project localhost starter. Before real deployment, configure HTTPS,
  secure database credentials, email-based recovery, backups, and production server rules.
- The recovery secret SIOMEOW is a classroom demonstration safeguard, not production security.
- Expiration processing runs only when scripts/expire_items.php is executed.
  Configure Windows Task Scheduler or run it manually. XAMPP does not run PHP scripts
  in the background while the computer/server is off.
- Make sure PHP has PDO MySQL and fileinfo enabled.
- Uploaded images are stored in uploads/items. The folder blocks PHP execution using .htaccess.

ROLE SUMMARY
User: create/manage own lost-item posts, browse active items, notify posters, view notifications.
Admin: manage item records and office found/recovered workflow; view poster names.
Super Admin: all admin functions plus account and role management, audit logs and settings.

This project uses one common database table (lost_items) for missing-item posts and office records.
found_items and recovered_items are separate office records linked to the original post where possible.
The item history and audit log preserve important actions.
