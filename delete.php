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

    // حذف کاربر بر اساس id
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND uuid = ?");
    $stmt->bind_param("is", $id, $uuid);

    if ($stmt->execute()) {
        echo "کاربر با موفقیت حذف شد.";
        echo "<br><a href='index.php'>بازگشت به صفحه اصلی</a>";
    } else {
        echo "خطا در حذف کاربر: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "اطلاعات کافی برای حذف کاربر موجود نیست.";
}

$conn->close();
?>
