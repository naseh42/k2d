#!/bin/bash

echo "شروع نصب بسته‌های مورد نیاز..."
sudo apt update
sudo apt install -y apache2 mysql-server php libapache2-mod-php php-mysql \
certbot python3-certbot-apache dnscrypt-proxy shadowsocks-libev \
openvpn easy-rsa wireguard jq wget

# نصب و پیکربندی Xray Core
echo "نصب Xray Core..."
bash <(curl -L -s https://raw.githubusercontent.com/XTLS/Xray-install/main/install-release.sh)

echo "پیکربندی اولیه Xray Core..."
sudo bash -c 'cat << EOF > /usr/local/etc/xray/config.json
{
    "inbounds": [
        {
            "port": 443,
            "protocol": "vless",
            "settings": {
                "clients": [],
                "decryption": "none"
            },
            "streamSettings": {
                "network": "ws",
                "security": "tls",
                "tlsSettings": {
                    "certificates": [{
                        "certificateFile": "/etc/ssl/certs/ssl-cert-snakeoil.pem",
                        "keyFile": "/etc/ssl/private/ssl-cert-snakeoil.key"
                    }]
                },
                "wsSettings": {
                    "path": "/ws"
                }
            }
        }
    ],
    "outbounds": [{
        "protocol": "freedom",
        "settings": {}
    }]
}
EOF'
sudo systemctl restart xray

# دانلود، نصب و تنظیم Sing-box
echo "دانلود و نصب Sing-box..."
wget https://github.com/SagerNet/sing-box/releases/download/v1.11.5/sing-box-linux-amd64.tar.gz
tar -xvzf sing-box-linux-amd64.tar.gz
sudo mv sing-box /usr/local/bin/
sudo chmod +x /usr/local/bin/sing-box

echo "ایجاد فایل سرویس و کانفیگ برای Sing-box..."
sudo mkdir -p /etc/sing-box
sudo bash -c 'cat << EOF > /etc/sing-box/config.json
{
    "inbounds": [{
        "type": "hysteria",
        "listen": ":443",
        "settings": {
            "auth": {
                "mode": "passwords",
                "passwords": ["example_password"]
            },
            "obfs": {
                "type": "http",
                "host": "cdn.example.com"
            }
        }
    }],
    "outbounds": [{
        "type": "direct"
    }]
}
EOF'
sudo bash -c 'cat << EOF > /etc/systemd/system/sing-box.service
[Unit]
Description=Sing-box Service
After=network.target

[Service]
ExecStart=/usr/local/bin/sing-box run -config /etc/sing-box/config.json
Restart=always
RestartSec=3
LimitNOFILE=65535

[Install]
WantedBy=multi-user.target
EOF'
sudo systemctl daemon-reload
sudo systemctl enable sing-box
sudo systemctl restart sing-box

# نصب و پیکربندی OpenConnect (ocserv)
echo "نصب و پیکربندی OpenConnect..."
sudo apt install -y ocserv
sudo bash -c 'cat << EOF > /etc/ocserv/ocserv.conf
auth = "plain[passwd=/etc/ocserv/ocpasswd]"
tcp-port = 443
udp-port = 443
run-as-group = nogroup
run-as-user = nobody
server-cert = /etc/ssl/certs/ssl-cert-snakeoil.pem
server-key = /etc/ssl/private/ssl-cert-snakeoil.key
EOF'
sudo ocpasswd -c /etc/ocserv/ocpasswd username
sudo systemctl enable ocserv
sudo systemctl restart ocserv

# بررسی وضعیت سرویس‌ها
echo "بررسی وضعیت سرویس‌ها..."
systemctl status xray
systemctl status sing-box
systemctl status ocserv

echo "تمام مراحل نصب و پیکربندی با موفقیت انجام شد!"
