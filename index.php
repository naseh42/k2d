<?php
// مدیریت افزودن کاربر برای VLESS
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_vless_user'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // به‌روزرسانی تنظیمات Xray برای VLESS
    $xray_config = json_decode(file_get_contents('/usr/local/etc/xray/config.json'), true);
    $xray_config['inbounds'][0]['settings']['clients'][] = [
        "password" => $password,
        "email" => $username
    ];
    file_put_contents('/usr/local/etc/xray/config.json', json_encode($xray_config, JSON_PRETTY_PRINT));
    shell_exec("systemctl restart xray");
    
    $message = "کاربر VLESS با موفقیت اضافه شد!";
}

// مدیریت افزودن کاربر برای Trojan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_trojan_user'])) {
    $trojan_username = $_POST['trojan_username'];
    $trojan_password = $_POST['trojan_password'];
    
    // به‌روزرسانی تنظیمات Xray برای Trojan
    $xray_config = json_decode(file_get_contents('/usr/local/etc/xray/config.json'), true);
    $xray_config['inbounds'][1]['settings']['clients'][] = [
        "password" => $trojan_password,
        "email" => $trojan_username
    ];
    file_put_contents('/usr/local/etc/xray/config.json', json_encode($xray_config, JSON_PRETTY_PRINT));
    shell_exec("systemctl restart xray");
    
    $message = "کاربر Trojan با موفقیت اضافه شد!";
}

// مدیریت افزودن کاربر برای Hysteria
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_hysteria_user'])) {
    $hysteria_username = $_POST['hysteria_username'];
    $hysteria_password = $_POST['hysteria_password'];
    
    // به‌روزرسانی تنظیمات Sing-box
    $singbox_config = json_decode(file_get_contents('/etc/sing-box/config.json'), true);
    $singbox_config['inbounds'][0]['settings']['auth']['passwords'][] = $hysteria_password;
    file_put_contents('/etc/sing-box/config.json', json_encode($singbox_config, JSON_PRETTY_PRINT));
    shell_exec("systemctl restart sing-box");
    
    $message = "کاربر Hysteria با موفقیت اضافه شد!";
}
?>
