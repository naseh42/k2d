#!/bin/bash

# به‌روزرسانی پکیج‌ها و نصب پیش‌نیازها
echo "Updating system and installing dependencies..."
sudo apt-get update && sudo apt-get install -y \
    python3 \
    python3-pip \
    curl \
    unzip \
    iputils-ping \
    docker.io \
    docker-compose \
    && rm -rf /var/lib/apt/lists/*

# نصب FastAPI و uvicorn
echo "Installing FastAPI and uvicorn..."
pip3 install fastapi uvicorn

# دانلود و نصب XRay-core
echo "Downloading and installing XRay-core..."
curl -L -o /usr/local/bin/xray https://github.com/XTLS/Xray-core/releases/latest/download/Xray-linux-64
chmod +x /usr/local/bin/xray

# ایجاد فایل پیکربندی XRay
echo "Creating XRay config.json..."
cat <<EOL > /root/config.json
{
  "inbounds": [
    {
      "port": 443,
      "protocol": "vless",
      "settings": {
        "clients": [
          {
            "id": "your-uuid-here",
            "flow": "xtls-rprx-direct"
          }
        ]
      },
      "streamSettings": {
        "network": "tcp",
        "security": "tls"
      }
    },
    {
      "port": 1080,
      "protocol": "vmess",
      "settings": {
        "clients": [
          {
            "id": "your-uuid-here",
            "alterId": 0,
            "security": "auto"
          }
        ]
      },
      "streamSettings": {
        "network": "ws",
        "wsSettings": {
          "path": "/ws"
        }
      }
    },
    {
      "port": 10443,
      "protocol": "hysteria",
      "settings": {
        "clients": [
          {
            "id": "your-uuid-here"
          }
        ]
      },
      "streamSettings": {
        "network": "udp"
      }
    },
    {
      "port": 10555,
      "protocol": "xtcp",
      "settings": {
        "clients": [
          {
            "id": "your-uuid-here"
          }
        ]
      },
      "streamSettings": {
        "network": "tcp"
      }
    }
  ],
  "outbounds": [
    {
      "protocol": "freedom"
    }
  ]
}
EOL

# ایجاد فایل اصلی FastAPI (main.py)
echo "Creating FastAPI main.py..."
cat <<EOL > /root/main.py
from fastapi import FastAPI
from pydantic import BaseModel
import json
app = FastAPI()

class Config(BaseModel):
    id: str
    protocol: str
    port: int
    settings: dict
    streamSettings: dict

@app.post("/update_config/")
async def update_config(config: Config):
    config_dict = json.loads(config.json())
    with open("/root/config.json", "r+") as f:
        data = json.load(f)
        for inbound in data['inbounds']:
            if inbound['protocol'] == config_dict['protocol'] and inbound['port'] == config_dict['port']:
                inbound.update(config_dict)
                f.seek(0)
                json.dump(data, f, indent=4)
                return {"message": f"Config updated for {config_dict['protocol']} on port {config_dict['port']}"}
    return {"message": "Config not found"}
EOL

# ساخت Docker image و اجرای آن
echo "Building Docker image..."
docker build -t kurdan-panel /root

# اجرای Docker container
echo "Running Docker container..."
docker run -d -p 8000:8000 -p 443:443 -p 1080:1080 kurdan-panel

# موفقیت نصب
echo "Installation complete! The panel is running at http://your-server-ip:8000"
