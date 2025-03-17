<?php
include('config.php');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (\$conn->connect_error) {
    die("خطا در اتصال به دیتابیس: " . \$conn->connect_error);
}

if (isset(\$_GET['id'])) {
    \$id = intval(\$_GET['id']);
    \$stmt = \$conn->prepare("SELECT * FROM users WHERE id = ?");
    \$stmt->bind_param("i", \$id);
    \$stmt->execute();
    \$result = \$stmt->get_result();
    \$user = \$result->fetch_assoc();
} else {
    die("خطای دریافت اطلاعات کاربر");
}

if (\$_SERVER['REQUEST_METHOD'] === 'POST') {
    \$username = \$_POST['username'];
    \$expire_date = \$_POST['expire_date'];
    \$volume = \$_POST['volume'] * 1024 * 1024 * 1024;
    \$protocols = implode(',', \$_POST['protocols']);

    \$stmt = \$conn->prepare("UPDATE users SET username=?, expire_date=?, volume=?, protocols=? WHERE id=?");
    \$stmt->bind_param("ssisi", \$username, \$expire_date, \$volume, \$protocols, \$id);
    \$stmt->execute();

    echo "<p>اطلاعات کاربر بروزرسانی شد.</p>";
    header("Refresh:2; url=index.php");
}
?>

<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ویرایش کاربر</title>
</head>
<body>
    <h1>ویرایش کاربر</h1>
    <form method="POST">
        <input type="text" name="username" value="<?= \$user['username'] ?>" required>
        <input type="date" name="expire_date" value="<?= \$user['expire_date'] ?>" required>
        <input type="number" name="volume" value="<?= round(\$user['volume'] / (1024*1024*1024), 2) ?>" required> GB
        <label><input type="checkbox" name="protocols[]" value="vless" <?= strpos(\$user['protocols'], 'vless') !== false ? 'checked' : '' ?>> VLESS</label>
        <label><input type="checkbox" name="protocols[]" value="vmess" <?= strpos(\$user['protocols'], 'vmess') !== false ? 'checked' : '' ?>> VMess</label>
        <label><input type="checkbox" name="protocols[]" value="trojan" <?= strpos(\$user['protocols'], 'trojan') !== false ? 'checked' : '' ?>> Trojan</label>
        <label><input type="checkbox" name="protocols[]" value="hysteria" <?= strpos(\$user['protocols'], 'hysteria') !== false ? 'checked' : '' ?>> Hysteria</label>
        <button type="submit">بروزرسانی</button>
    </form>
</body>
</html>
