<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('config.php'); // فایل تنظیمات شامل اطلاعات دیتابیس

// بررسی اتصال دیتابیس
if (!$conn) {
    die("خطا در اتصال به دیتابیس: " . mysqli_connect_error());
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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $username = trim($_POST['username']);
    $uuid = trim($_POST['uuid']);
    $expire_date = date('Y-m-d', strtotime("+30 days"));
    $volume = 50 * 1024 * 1024 * 1024; // 50GB

    $protocols = isset($_POST['protocols']) ? implode(',', $_POST['protocols']) : '';

    // ذخیره در دیتابیس
    $stmt = $conn->prepare("INSERT INTO users (username, uuid, expire_date, volume, protocols) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        die("خطا در آماده‌سازی کوئری: " . $conn->error);
    }
    $stmt->bind_param("sssis", $username, $uuid, $expire_date, $volume, $protocols);

    if ($stmt->execute()) {
        echo "<p>کاربر با موفقیت اضافه شد!</p>";
    } else {
        echo "<p>خطا در اضافه کردن کاربر: " . $stmt->error . "</p>";
    }

    $stmt->close();
}

// دریافت لیست کاربران
$result = $conn->query("SELECT * FROM users");
if (!$result) {
    die("خطا در اجرای کوئری: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KurdVPN - مدیریت کاربران</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7fa;
            margin: 0;
            padding: 0;
            direction: <?= $lang === 'fa' ? 'rtl' : 'ltr' ?>;
            color: #333;
        }
        header {
            background-color: #28a745;
            padding: 20px;
            text-align: center;
            color: white;
            font-size: 24px;
        }
        .container {
            padding: 30px;
            width: 80%;
            margin: 20px auto;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: center;
            border: 1px solid #ddd;
        }
        th {
            background-color: #28a745;
            color: white;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
    </style>
</head>
<body>

<header><?= $current_translations['welcome'] ?></header>

<div class="container">
    <h2><?= $current_translations['add_user'] ?></h2>
    <form method="POST">
        <input type="text" name="username" placeholder="نام کاربری" required>
        <input type="text" name="uuid" placeholder="UUID" required>
        <button type="submit" name="add_user"><?= $current_translations['add'] ?></button>
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
            <th>عملیات</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= htmlspecialchars($row['uuid']) ?></td>
                <td><?= htmlspecialchars($row['protocols']) ?></td>
                <td><?= round($row['volume'] / (1024*1024*1024), 2) ?> GB</td>
                <td><?= htmlspecialchars($row['expire_date']) ?></td>
                <td>
                    <a href="delete.php?id=<?= $row['id'] ?>&uuid=<?= $row['uuid'] ?>">حذف</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

</body>
</html>
