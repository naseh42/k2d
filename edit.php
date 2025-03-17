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

// بررسی پارامترهای ورودی
if (isset($_GET['id']) && isset($_GET['uuid'])) {
    $id = $_GET['id'];
    $uuid = $_GET['uuid'];

    // دریافت اطلاعات کاربر از دیتابیس
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND uuid = ?");
    $stmt->bind_param("is", $id, $uuid);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        echo "کاربری با این مشخصات یافت نشد.";
        exit;
    }

    // ویرایش داده‌ها پس از ارسال فرم
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = $_POST['username'];
        $uuid = $_POST['uuid'];
        $protocols = isset($_POST['protocols']) ? implode(',', $_POST['protocols']) : '';

        $stmt = $conn->prepare("UPDATE users SET username = ?, uuid = ?, protocols = ? WHERE id = ?");
        $stmt->bind_param("sssi", $username, $uuid, $protocols, $id);

        if ($stmt->execute()) {
            echo "اطلاعات کاربر با موفقیت به روز رسانی شد.";
            echo "<br><a href='index.php'>بازگشت به صفحه اصلی</a>";
        } else {
            echo "خطا در به روز رسانی اطلاعات: " . $stmt->error;
        }

        $stmt->close();
    }
} else {
    echo "اطلاعات کافی برای ویرایش کاربر موجود نیست.";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ویرایش کاربر</title>
    <style>
        body { font-family: Arial, sans-serif; direction: rtl; }
        .container { padding: 30px; width: 80%; margin: 20px auto; background-color: white; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); }
        label { display: block; margin: 10px 0 5px; }
        input[type="text"], select { width: 100%; padding: 8px; margin: 5px 0; border-radius: 5px; border: 1px solid #ccc; }
        input[type="submit"] { background-color: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        input[type="submit"]:hover { background-color: #45a049; }
    </style>
</head>
<body>
    <div class="container">
        <h2>ویرایش کاربر: <?php echo $user['username']; ?></h2>
        <form method="POST" action="">
            <label for="username">نام کاربری</label>
            <input type="text" id="username" name="username" value="<?php echo $user['username']; ?>" required>

            <label for="uuid">UUID</label>
            <input type="text" id="uuid" name="uuid" value="<?php echo $user['uuid']; ?>" required>

            <label for="protocols">پروتکل‌ها</label>
            <select id="protocols" name="protocols[]" multiple>
                <option value="VLESS" <?php echo in_array('VLESS', explode(',', $user['protocols'])) ? 'selected' : ''; ?>>VLESS</option>
                <option value="VMess" <?php echo in_array('VMess', explode(',', $user['protocols'])) ? 'selected' : ''; ?>>VMess</option>
                <option value="Trojan" <?php echo in_array('Trojan', explode(',', $user['protocols'])) ? 'selected' : ''; ?>>Trojan</option>
                <option value="Hysteria" <?php echo in_array('Hysteria', explode(',', $user['protocols'])) ? 'selected' : ''; ?>>Hysteria</option>
            </select>

            <input type="submit" value="بروزرسانی اطلاعات">
        </form>
    </div>
</body>
</html>
