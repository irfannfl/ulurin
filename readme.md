# ulur in
Simple URL shortener for apache

By : Naufal Irfan

https://ulur.in

# Installing
1. run set.sql command to create database table
2. put 'ulur.in' files (index.php & .htaccess) into home directory (example.com)
3. put 'add.ulur.in' folder's files into subdomain directory (sub.example.com)
4. edit variable in "/backend/sql.php" based on your SQL server.
5. edit API url in "/assets/js/main.js", and "/user.php" into 'example.com/api'.
6. edit index.php in home directory "require sql.php" make sure path is correct based on your local dir.
7. edit SITE_KEY and SECRET_TOKEN for reCAPTCHA from your own reCAPTCHA account (or just delete reCAPTCHA code).
