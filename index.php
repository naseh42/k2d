<?php
include 'config.php';

// عملیات کاربران
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_user'])) {
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $bandwidth_limit = $_POST['bandwidth_limit'];
        $time_limit = $_POST['time_limit'];

        $stmt = $conn->prepare("INSERT INTO users (username, password, bandwidth_limit, time_limit) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssis", $username, $password, $bandwidth_limit, $time_limit);
        $stmt->execute();
    } elseif (isset($_POST['delete_user'])) {
        $user_id = $_POST['user_id'];
        $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
    }

    // عملیات سرورها
    if (isset($_POST['add_server'])) {
        $server_name = $_POST['server_name'];
        $ip_address = $_POST['ip_address'];

        $stmt = $conn->prepare("INSERT INTO servers (name, ip_address) VALUES (?, ?)");
        $stmt->bind_param("ss", $server_name, $ip_address);
        $stmt->execute();
    } elseif (isset($_POST['delete_server'])) {
        $server_id = $_POST['server_id'];
        $stmt = $conn->prepare("DELETE FROM servers WHERE id=?");
        $stmt->bind_param("i", $server_id);
        $stmt->execute();
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="styles.css">
    <title><?php echo $current_translations['welcome']; ?></title>
</head>
<body>
    <h1><?php echo $current_translations['welcome']; ?></h1>
    <h2><?php echo $current_translations['add_user']; ?></h2>
    <form method="post">
        <input type="text" name="username" placeholder="نام کاربری">
        <input type="password" name="password" placeholder="رمز عبور">
        <input type="number" name="bandwidth_limit" placeholder="محدودیت حجم (بایت)">
        <input type="datetime-local" name="time_limit">
        <button type="submit" name="add_user">افزودن کاربر</button>
    </form>

    <h2><?php echo $current_translations['manage_servers']; ?></h2>
    <form method="post">
        <input type="text" name="server_name" placeholder="نام سرور">
        <input type="text" name="ip_address" placeholder="آدرس IP">
        <button type="submit" name="add_server">افزودن سرور</button>
    </form>

    <h2><?php echo $current_translations['server_info']; ?></h2>
    <pre><?php echo shell_exec("lscpu && free -h && df -h /"); ?></pre>
</body>
</html>
