<?php
// تنظیمات پایگاه‌داده
$host = "localhost";
$username = "root";
$password = "";
$dbname = "vpn_users";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("خطای اتصال به پایگاه‌داده: " . $conn->connect_error);
}

// تنظیم زبان
$lang = isset($_GET['lang']) && $_GET['lang'] == 'en' ? 'en' : 'fa';
$translations = [
    'fa' => [
        'welcome' => 'به پنل مدیریت خوش آمدید',
        'add_user' => 'افزودن کاربر',
        'manage_servers' => 'مدیریت سرورها',
        'server_info' => 'مشخصات سرور',
    ],
    'en' => [
        'welcome' => 'Welcome to the Admin Panel',
        'add_user' => 'Add User',
        'manage_servers' => 'Manage Servers',
        'server_info' => 'Server Info',
    ]
];
$current_translations = $translations[$lang];
?>
