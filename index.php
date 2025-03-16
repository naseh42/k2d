<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پنل مدیریت حرفه‌ای</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>پنل مدیریت حرفه‌ای</h1>
        <p>خوش آمدید به داشبورد مدیریت</p>
    </header>

    <main>
        <section>
            <h2>افزودن کاربر</h2>
            <form method="post">
                <label for="username">نام کاربری:</label>
                <input type="text" id="username" name="username" required>
                
                <label for="password">رمز عبور:</label>
                <input type="password" id="password" name="password" required>
                
                <label for="bandwidth_limit">محدودیت حجم (بایت):</label>
                <input type="number" id="bandwidth_limit" name="bandwidth_limit">
                
                <label for="time_limit">تاریخ انقضا:</label>
                <input type="datetime-local" id="time_limit" name="time_limit">
                
                <button type="submit" name="add_user">افزودن کاربر</button>
            </form>
        </section>
        <section>
            <h2>مدیریت سرورها</h2>
            <form method="post">
                <label for="server_name">نام سرور:</label>
                <input type="text" id="server_name" name="server_name" required>
                
                <label for="ip_address">آدرس IP:</label>
                <input type="text" id="ip_address" name="ip_address" required>
                
                <button type="submit" name="add_server">افزودن سرور</button>
            </form>
        </section>
    </main>

    <footer>
        <p>&copy; 2025 پنل مدیریت حرفه‌ای | تمام حقوق محفوظ است.</p>
    </footer>
</body>
</html>
