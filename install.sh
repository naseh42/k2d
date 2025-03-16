#!/bin/bash

echo "Updating system and installing dependencies..."
sudo apt-get update && sudo apt-get install -y \
    python3 \
    python3-pip \
    python3-venv \
    curl \
    unzip \
    iputils-ping \
    && rm -rf /var/lib/apt/lists/*

echo "Creating Python virtual environment..."
python3 -m venv /root/venv
source /root/venv/bin/activate

echo "Installing FastAPI and uvicorn..."
pip install --upgrade pip
pip install fastapi uvicorn

echo "Downloading and installing XRay-core..."
curl -L -o /usr/local/bin/xray https://github.com/XTLS/Xray-core/releases/latest/download/Xray-linux-64
chmod +x /usr/local/bin/xray

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

echo "Creating FastAPI main.py..."
cat <<EOL > /root/main.py
from fastapi import FastAPI
import json

app = FastAPI()

@app.get("/")
def read_root():
    return {"message": "Kurdan Panel is running"}

@app.post("/update_config/")
def update_config(config: dict):
    with open("/root/config.json", "w") as f:
        json.dump(config, f, indent=4)
    return {"message": "Config updated"}
EOL

echo "Starting FastAPI service..."
nohup /root/venv/bin/uvicorn main:app --host 0.0.0.0 --port 8000 > /root/panel.log 2>&1 &

echo "Starting XRay..."
nohup /usr/local/bin/xray -config /root/config.json > /root/xray.log 2>&1 &

echo "Installation complete! The panel is running at http://your-server-ip:8000"
