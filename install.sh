#!/bin/bash
echo "شروع نصب بسته‌های مورد نیاز..."
sudo apt update
sudo apt install -y apache2 mysql-server php libapache2-mod-php php-mysql \
certbot python3-certbot-apache dnscrypt-proxy shadowsocks-libev \
openvpn easy-rsa wireguard ocserv

echo "نصب V2Ray..."
bash <(curl -L -s https://install.direct/go.sh)

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
                "clients": []
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
        },
        {
            "port": 443,
            "protocol": "trojan",
            "settings": {
                "clients": []
            },
            "streamSettings": {
                "security": "tls",
                "tlsSettings": {
                    "certificates": [{
                        "certificateFile": "/etc/ssl/certs/ssl-cert-snakeoil.pem",
                        "keyFile": "/etc/ssl/private/ssl-cert-snakeoil.key"
                    }]
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

echo "نصب Sing-box..."
bash <(curl -Ls https://raw.githubusercontent.com/SagerNet/sing-box/main/install/install.sh)

echo "پیکربندی اولیه Sing-box..."
sudo bash -c 'cat << EOF > /etc/sing-box/config.json
{
    "inbounds": [{
        "type": "hysteria",
        "listen": ":443",
        "settings": {
            "auth": {
                "mode": "passwords",
                "passwords": []
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
sudo systemctl restart sing-box

echo "نصب و پیکربندی OpenConnect..."
sudo apt install ocserv
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

echo "تمام مراحل نصب با موفقیت انجام شد!"
