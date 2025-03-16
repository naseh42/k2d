#!/bin/bash

echo "Updating system and installing dependencies..."
sudo apt-get update && sudo apt-get install -y \
    python3 \
    python3-venv \
    python3-pip \
    curl \
    unzip \
    iputils-ping \
    docker.io \
    docker-compose \
    && rm -rf /var/lib/apt/lists/*

# ایجاد و فعال‌سازی Virtual Environment برای پایتون
echo "Setting up Python Virtual Environment..."
python3 -m venv /env
source /env/bin/activate
pip install --upgrade pip
pip install fastapi uvicorn

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
    }
  ],
  "outbounds": [
    {
      "protocol": "freedom"
    }
  ]
}
EOL

# ایجاد فایل FastAPI (main.py)
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

# بررسی نصب داکر
if ! command -v docker &> /dev/null; then
    echo "Docker is not installed. Installing..."
    sudo apt-get install -y docker.io
fi

# ساخت و اجرای کانتینر داکر
echo "Running Docker container..."
docker run -d --name kurdan-panel -p 8000:8000 -p 443:443 -p 1080:1080 -v /root:/root ubuntu:latest /bin/bash -c "source /env/bin/activate && uvicorn main:app --host 0.0.0.0 --port 8000"

# موفقیت نصب
echo "Installation complete! The panel is running at http://your-server-ip:8000"
