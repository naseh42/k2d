CREATE DATABASE vpn_users;
USE vpn_users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50),
    password VARCHAR(255),
    bandwidth_limit BIGINT,
    time_limit DATETIME
);

CREATE TABLE servers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    ip_address VARCHAR(50)
);
