<?php
include 'config.php';

// مدیریت افزودن کاربر برای V2Ray
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $bandwidth_limit = $_POST['bandwidth_limit'];
    $time_limit = $_POST['time_limit'];
    $max_connections = $_POST['max_connections'];
    $v2ray_uuid = shell_exec("uuidgen"); // تولید UUID برای V2Ray

    $expiry_date = date('Y-m-d H:i:s', strtotime($time_limit));
    $stmt = $conn->prepare("INSERT INTO users (username, password, bandwidth_limit, time_limit, expiry_date, max_connections, v2ray_uuid) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssissis", $username, $password, $bandwidth_limit, $time_limit, $expiry_date, $max_connections, $v2ray_uuid);

    if ($stmt->execute()) {
        $message = "کاربر با موفقیت ایجاد شد!";
        // به‌روزرسانی تنظیمات V2Ray
        $v2ray_config = json_decode(file_get_contents('/etc/v2ray/config.json'), true);
        $v2ray_config['inbounds'][0]['settings']['clients'][] = [
            "id" => $v2ray_uuid,
            "email" => $username
        ];
        file_put_contents('/etc/v2ray/config.json', json_encode($v2ray_config, JSON_PRETTY_PRINT));
        shell_exec("systemctl restart v2ray");
    } else {
        $message = "مشکلی پیش آمد. لطفاً دوباره تلاش کنید.";
    }
}

// مدیریت افزودن کاربر برای Cisco AnyConnect
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_cisco_user'])) {
    $cisco_username = $_POST['cisco_username'];
    $cisco_password = $_POST['cisco_password'];

    // اضافه‌کردن به ocpasswd
    shell_exec("echo '$cisco_username:$cisco_password' | ocpasswd -c /etc/ocserv/ocpasswd");
    $message = "کاربر جدید Cisco با موفقیت اضافه شد!";
}

// مدیریت افزودن کاربر برای Hysteria
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_hysteria_user'])) {
    $hysteria_username = $_POST['hysteria_username'];
    $hysteria_password = $_POST['hysteria_password'];

    // اضافه‌کردن کاربر به تنظیمات Sing-box
    $singbox_config = json_decode(file_get_contents('/etc/sing-box/config.json'), true);
    $singbox_config['inbounds'][0]['settings']['auth']['passwords'][] = $hysteria_password;
    file_put_contents('/etc/sing-box/config.json', json_encode($singbox_config, JSON_PRETTY_PRINT));
    shell_exec("systemctl restart sing-box");

    $message = "کاربر Hysteria با موفقیت اضافه شد!";
}

// داده‌های مصرف کاربران برای نمودار
$user_data = [];
$result = $conn->query("SELECT username, data_usage FROM users");
while ($row = $result->fetch_assoc()) {
    $user_data[] = $row;
}

// مشخصات سرور و وضعیت زنده
$server_status = [
    'cpu_temp' => shell_exec("sensors | grep 'Core 0'"),
    'load_avg' => shell_exec("uptime"),
    'network_status' => shell_exec("vnstat -l")
];
?>
<!DOCTYPE html>
<html lang="fa">
<!-- ادامه کد HTML و اسکریپت مربوطه همانند نسخه‌ای که پیش‌تر ارائه شد -->
