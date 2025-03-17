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
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پنل مدیریت حرفه‌ای</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <header>
        <h1>پنل مدیریت حرفه‌ای</h1>
        <p>مدیریت پیشرفته کاربران و پروتکل‌ها</p>
        <button id="toggleDarkMode">تغییر به حالت تاریک</button>
    </header>
    <main>
        <section>
            <h2>افزودن کاربر برای V2Ray</h2>
            <form method="post">
                <label for="username">نام کاربری:</label>
                <input type="text" id="username" name="username" required>
                
                <label for="password">رمز عبور:</label>
                <input type="password" id="password" name="password" required>
                
                <label for="bandwidth_limit">محدودیت حجم (بایت):</label>
                <input type="number" id="bandwidth_limit" name="bandwidth_limit">
                
                <label for="time_limit">تاریخ انقضا:</label>
                <input type="datetime-local" id="time_limit" name="time_limit">
                
                <label for="max_connections">حداکثر اتصال همزمان:</label>
                <input type="number" id="max_connections" name="max_connections" min="1" required>
                
                <button type="submit" name="add_user">ایجاد کاربر</button>
            </form>
        </section>
        <section>
            <h2>افزودن کاربر برای Cisco AnyConnect</h2>
            <form method="post">
                <label for="cisco_username">نام کاربری:</label>
                <input type="text" id="cisco_username" name="cisco_username" required>

                <label for="cisco_password">رمز عبور:</label>
                <input type="password" id="cisco_password" name="cisco_password" required>

                <button type="submit" name="add_cisco_user">ایجاد کاربر</button>
            </form>
        </section>
        <section>
            <h2>وضعیت زنده سرور</h2>
            <table>
                <tr><td>دمای پردازنده:</td><td><?php echo $server_status['cpu_temp']; ?></td></tr>
                <tr><td>بار سیستم:</td><td><?php echo $server_status['load_avg']; ?></td></tr>
                <tr><td>وضعیت شبکه:</td><td><?php echo $server_status['network_status']; ?></td></tr>
            </table>
        </section>
        <section>
            <h2>نمودار مصرف حجم کاربران</h2>
            <canvas id="usageChart" width="400" height="200"></canvas>
            <script>
                const ctx = document.getElementById('usageChart').getContext('2d');
                const data = {
                    labels: <?php echo json_encode(array_column($user_data, 'username')); ?>,
                    datasets: [{
                        label: 'مصرف حجم (بایت)',
                        data: <?php echo json_encode(array_column($user_data, 'data_usage')); ?>,
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                };
                const config = {
                    type: 'bar',
                    data: data,
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                };
                new Chart(ctx, config);
            </script>
        </section>
    </main>
    <footer>
        <p>&copy; 2025 پنل مدیریت حرفه‌ای | تمام حقوق محفوظ است.</p>
    </footer>
    <script>
        const toggleDarkMode = document.getElementById('toggleDarkMode');
        toggleDarkMode.addEventListener('click', () => {
            document.body.classList.toggle('dark-mode');
        });
    </script>
</body>
</html>
