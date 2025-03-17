<?php
include('config.php'); // فایل تنظیمات شامل اطلاعات دیتابیس

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (\$conn->connect_error) {
    die("خطا در اتصال به دیتابیس: " . \$conn->connect_error);
}

if (isset(\$_GET['id']) && isset(\$_GET['uuid'])) {
    \$id = intval(\$_GET['id']);
    \$uuid = \$_GET['uuid'];

    // حذف کاربر از دیتابیس
    \$stmt = \$conn->prepare("DELETE FROM users WHERE id = ?");
    \$stmt->bind_param("i", \$id);
    \$stmt->execute();

    // حذف کاربر از Xray
    \$xray_config = json_decode(file_get_contents('/usr/local/etc/xray/config.json'), true);
    foreach (\$xray_config['inbounds'][0]['settings']['clients'] as \$key => \$client) {
        if (\$client['id'] === \$uuid) {
            unset(\$xray_config['inbounds'][0]['settings']['clients'][\$key]);
        }
    }
    \$xray_config['inbounds'][0]['settings']['clients'] = array_values(\$xray_config['inbounds'][0]['settings']['clients']);
    file_put_contents('/usr/local/etc/xray/config.json', json_encode(\$xray_config, JSON_PRETTY_PRINT));
    shell_exec("systemctl restart xray");

    echo "<p>کاربر با موفقیت حذف شد.</p>";
    header("Refresh:2; url=index.php");
} else {
    echo "<p>پارامترهای نامعتبر!</p>";
}
?>
