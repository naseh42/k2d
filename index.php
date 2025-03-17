<?php
$servername = "localhost"; // نام سرور پایگاه داده
$username = "root"; // نام کاربری پایگاه داده
$password = ""; // پسورد پایگاه داده
$dbname = "vpn_users"; // نام پایگاه داده

// ایجاد اتصال به پایگاه داده
$conn = new mysqli($servername, $username, $password, $dbname);

// بررسی اتصال
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// بررسی ارسال فرم
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $uuid = $_POST['uuid'];
    $expire_date = date('Y-m-d', strtotime("+30 days")); // تاریخ انقضا بعد از 30 روز
    $volume = 50 * 1024 * 1024 * 1024; // حجم 50 گیگابایت به بایت
    $protocols = isset($_POST['protocols']) ? implode(',', $_POST['protocols']) : '';

    // ذخیره در دیتابیس
    $stmt = $conn->prepare("INSERT INTO users (username, uuid, expire_date, volume, protocols) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssis", $username, $uuid, $expire_date, $volume, $protocols);

    if ($stmt->execute()) {
        echo "کاربر با موفقیت اضافه شد.";
    } else {
        echo "خطا در ذخیره کاربر: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پنل مدیریت VPN</title>
    <style>
        body { font-family: Arial, sans-serif; direction: rtl; }
        .container { padding: 30px; width: 80%; margin: 20px auto; background-color: white; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); }
        table { width: 100%; margin-top: 20px; border-collapse: collapse; }
        th, td { padding: 12px; text-align: center; border: 1px solid #ddd; }
        .form-container { margin-bottom: 20px; }
        label { display: block; margin: 10px 0 5px; }
        input[type="text"], select { width: 100%; padding: 8px; margin: 5px 0; border-radius: 5px; border: 1px solid #ccc; }
        input[type="submit"] { background-color: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        input[type="submit"]:hover { background-color: #45a049; }
    </style>
</head>
<body>
    <div class="container">
        <h2>افزودن کاربر جدید</h2>
        <form method="POST" action="">
            <div class="form-container">
                <label for="username">نام کاربری</label>
                <input type="text" id="username" name="username" required>

                <label for="uuid">UUID</label>
                <input type="text" id="uuid" name="uuid" required>

                <label for="protocols">پروتکل‌ها</label>
                <select id="protocols" name="protocols[]" multiple>
                    <option value="VLESS">VLESS</option>
                    <option value="VMess">VMess</option>
                    <option value="Trojan">Trojan</option>
                    <option value="Hysteria">Hysteria</option>
                </select>
            </div>

            <input type="submit" value="اضافه کردن کاربر">
        </form>

        <h2>لیست کاربران</h2>
        <?php
        // نمایش کاربران
        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $sql = "SELECT id, username, uuid, expire_date, volume, protocols FROM users";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            echo "<table><tr><th>نام کاربری</th><th>UUID</th><th>تاریخ انقضا</th><th>حجم</th><th>پروتکل‌ها</th><th>عملیات</th></tr>";
            while($row = $result->fetch_assoc()) {
                echo "<tr><td>" . $row['username'] . "</td><td>" . $row['uuid'] . "</td><td>" . $row['expire_date'] . "</td><td>" . number_format($row['volume'] / (1024 * 1024 * 1024), 2) . " GB</td><td>" . $row['protocols'] . "</td><td><a href='delete.php?id=" . $row['id'] . "&uuid=" . $row['uuid'] . "'>حذف</a></td></tr>";
            }
            echo "</table>";
        } else {
            echo "هیچ کاربری وجود ندارد.";
        }

        $conn->close();
        ?>
    </div>
</body>
</html>
