#!/bin/bash
echo "شروع نصب..."
sudo apt update
sudo apt install -y apache2 mysql-server php libapache2-mod-php php-mysql certbot python3-certbot-apache dnscrypt-proxy shadowsocks-libev

echo "پیکربندی پایگاه‌داده..."
sudo systemctl start mysql
sudo mysql < sql_setup.sql

echo "پیکربندی DNS امن..."
sudo systemctl restart dnscrypt-proxy

echo "پیکربندی ShadowSocks..."
sudo bash -c 'cat << EOF > /etc/shadowsocks-libev/config.json
{
    "server": "0.0.0.0",
    "server_port": 8388,
    "password": "your_password",
    "method": "aes-256-gcm"
}
EOF'
sudo systemctl restart shadowsocks-libev

echo "نصب کامل شد! لطفاً به مرورگر مراجعه کنید."
