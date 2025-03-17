#!/bin/bash

echo "شروع نصب بسته‌های مورد نیاز..."
sudo apt update
sudo apt install -y apache2 mysql-server php libapache2-mod-php php-mysql \
certbot python3-certbot-apache dnscrypt-proxy shadowsocks-libev \
openvpn easy-rsa wireguard ocserv

echo "نصب V2Ray..."
bash <(curl -L -s https://install.direct/go.sh)

echo "پیکربندی اولیه V2Ray..."
sudo bash -c 'cat << EOF > /etc/v2ray/config.json
{
    "inbounds": [{
        "port": 10086,
        "protocol": "vmess",
        "settings": {
            "clients": []
        }
    }],
    "outbounds": [{
        "protocol": "freedom",
        "settings": {}
    }]
}
EOF'
sudo systemctl restart v2ray

echo "نصب و پیکربندی OpenConnect..."
sudo systemctl enable ocserv
sudo bash -c 'cat << EOF > /etc/ocserv/ocserv.conf
auth = "plain[passwd=/etc/ocserv/ocpasswd]"
tcp-port = 443
udp-port = 443
run-as-group = nogroup
run-as-user = nobody
server-cert = /etc/ssl/certs/ssl-cert-snakeoil.pem
server-key = /etc/ssl/private/ssl-cert-snakeoil.key
EOF'
sudo systemctl restart ocserv

echo "ایجاد پشتیبان از پایگاه‌داده..."
sudo mysqldump -u root -p vpn_users > backup.sql

echo "تمام مراحل نصب با موفقیت انجام شد!"
