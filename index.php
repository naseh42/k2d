<?php 
include('config.php'); // فایل تنظیمات شامل اطلاعات
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME); 
if ($conn->connect_error) {
    die("خطا در اتصال به دیتابیس: " . $conn->connect_error);
}

// تنظیم زبان
$lang = isset($_GET['lang']) && $_GET['lang'] == 'en' ? 'en' : 'fa'; 
$translations = [
    'fa' => [
        'welcome' => 'به پنل مدیریت خوش آمدید',
        'add_user' => 'افزودن کاربر',
        'manage_servers' => 'مدیریت سرورها',
        'server_info' => 'مشخصات سرور',
        'add' => 'افزودن کاربر',
        'list_users' => 'لیست کاربران'
    ], 
    'en' => [
        'welcome' => 'Welcome to the Admin Panel',
        'add_user' => 'Add User',
        'manage_servers' => 'Manage Servers',
        'server_info' => 'Server Info',
        'add' => 'Add User',
        'list_users' => 'List Users'
    ]
]; 
$current_translations = $translations[$lang];

// افزودن کاربر جدید
if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
    if (isset($_POST['add_user'])) {
        $username = trim($_POST['username']); 
        $uuid = trim($_POST['uuid']); 
        $expire_date = date('Y-m-d', strtotime("+30 days")); 
        $volume = 50 * 1024 * 1024 * 1024; // 50GB 
        $protocols = isset($_POST['protocols']) ? implode(',', $_POST['protocols']) : '';

        // ذخیره در دیتابیس
        $stmt = $conn->prepare("INSERT INTO users (username, uuid, expire_date, volume, protocols) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssis", $username, $uuid, $expire_date, $volume, $protocols);
        $stmt->execute(); 
        $stmt->close();

        // به‌روزرسانی Xray
        $xray_config_path = '/usr/local/etc/xray/config.json'; 
        if (file_exists($xray_config_path)) {
            $xray_config = json_decode(file_get_contents($xray_config_path), true); 
            if (isset($xray_config['inbounds'][0]['settings']['clients'])) {
                foreach ($_POST['protocols'] as $protocol) {
                    $xray_config['inbounds'][0]['settings']['clients'][] = [
                        "id" => $uuid, 
                        "email" => $username
                    ];
                }
                file_put_contents($xray_config_path, json_encode($xray_config, JSON_PRETTY_PRINT));
                shell_exec("systemctl restart xray");
            }
        }

        echo "<p>کاربر با موفقیت اضافه شد!</p>";
    }
}

// دریافت لیست کاربران
$result = $conn->query("SELECT * FROM users"); 
?>
<!DOCTYPE html> 
<html lang="<?= $lang ?>"> 
<head>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KurdVPN - مدیریت کاربران</title> 
    <style>
        body { font-family: 'Arial', sans-serif; background-color: #f4f7fa; margin: 0; padding: 0; direction: <?= $lang === 'fa' ? 'rtl' : 'ltr' ?>; color: #333; }
        header { background-color: #28a745; padding: 20px; text-align: center; color: white; font-size: 24px; }
        nav { background-color: #333; color: white; padding: 10px; text-align: center; }
        nav a { color: white; margin: 0 15px; text-decoration: none; font-size: 16px; }
        nav a:hover { text-decoration: underline; }
        .container { padding: 30px; width: 80%; margin: 20px auto; background-color: white; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); }
        .form-group { margin-bottom: 20px; }
        .form-group input, .form-group button { width: 100%; padding: 10px; font-size: 16px; margin: 5px 0; border: 1px solid #ddd; border-radius: 4px; }
        button[type="submit"] { background-color: #28a745; color: white; border: none; cursor: pointer; }
        button[type="submit"]:hover { background-color: #218838; }
        table { width: 100%; margin-top: 20px; border-collapse: collapse; }
        th, td { padding: 12px; text-align: center; border: 1px solid #ddd; }
        th { background-color: #28a745; color: white; }
        tr:hover { background-color: #f1f1f1; }
        .link { color: #007bff; text-decoration: none; }
        .link:hover { text-decoration: underline; }
        footer { background-color: #333; color: white; text-align: center; padding: 10px; position: fixed; width: 100%; bottom: 0; }
    </style> 
</head> 
<body> 
    <header> <?= $current_translations['welcome'] ?> </header> 
    <nav> <a href="?lang=fa">فارسی</a> | <a href="?lang=en">English</a> </nav> 
    <div class="container"> 
        <h2><?= $current_translations['add_user'] ?></h2> 
        <form method="POST">
            <div class="form-group"> 
                <input type="text" name="username" placeholder="نام کاربری" required> 
            </div> 
            <div class="form-group"> 
                <input type="text" name="uuid" placeholder="UUID" required> 
            </div> 
            <div class="form-group"> 
                <label><input type="checkbox" name="protocols[]" value="vless"> VLESS</label> 
                <label><input type="checkbox" name="protocols[]" value="vmess"> VMess</label> 
                <label><input type="checkbox" name="protocols[]" value="trojan"> Trojan</label> 
                <label><input type="checkbox" name="protocols[]" value="hysteria"> Hysteria</label> 
            </div> 
            <div class="form-group"> 
                <button type="submit" name="add_user"><?= $current_translations['add'] ?></button> 
            </div> 
        </form> 
    </div> 
    <div class="container">
        <h2><?= $current_translations['list_users'] ?></h2>
        <table>
            <tr> 
                <th>نام کاربری</th> 
                <th>UUID</th>
                <th>پروتکل‌ها</th> 
                <th>حجم باقی‌مانده</th>
                <th>تاریخ انقضا</th> 
                <th>لینک ساب</th>
                <th>عملیات</th>
            </tr> 
            <?php while ($row = $result->fetch_assoc()): ?> 
            <tr>
                <td><?= $row['username'] ?></td> 
                <td><?= $row['uuid'] ?></td> 
                <td><?= $row['protocols'] ?></td> 
                <td><?= round($row['volume'] / (1024*1024*1024), 2) ?> GB</td> 
                <td><?= $row['expire_date'] ?></td> 
                <td><a class="link" href="sub.php?uuid=<?= $row['uuid'] ?>">لینک</a></td> 
                <td>
                    <a class="link" href="edit.php?id=<?= $row['id'] ?>">ویرایش</a> | 
                    <a class="link" href="delete.php?id=<?= $row['id'] ?>&uuid=<?= $row['uuid'] ?>">حذف</a>
                </td> 
            </tr> 
            <?php endwhile; ?> 
        </table>
    </div> 
    <footer>
        KurdVPN © 2025 | همه حقوق محفوظ است. 
    </footer>
</body>
</html>
