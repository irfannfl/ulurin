<?php
$db = new MySQLi('SERVER', 'USERNAME', 'PASSWORD', 'DATABASE');
$db->set_charset('utf8mb4');

if ($db->connect_errno) {
    die("Database connection error");
}