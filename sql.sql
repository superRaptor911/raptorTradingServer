-- RUN THESE COMMANDS IN UR SQL DB
create database cucekTrading;
GRANT ALL PRIVILEGES ON cucekTrading.* TO 'my_fcuking_username'@'localhost';
CREATE USER 'tempUser'@'localhost' IDENTIFIED BY 'password';
GRANT SELECT ON cucekTrading.* TO 'tempUser'@'localhost';
FLUSH PRIVILEGES;
USE cucekTrading;
CREATE TABLE users(
    name VARCHAR(64) UNIQUE KEY,
    email VARCHAR(64) UNIQUE KEY,
    avatar VARCHAR(255)
);
CREATE TABLE coins (
    name VARCHAR(16) UNIQUE KEY,
    id VARCHAR(16) UNIQUE KEY,
    avatar VARCHAR(255)
);
CREATE TABLE transactions (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(64),
    coin VARCHAR(16),
    coinCount FLOAT(24),
    cost FLOAT(24),
    transType BOOLEAN DEFAULT 1,
    fee FLOAT(24) DEFAULT 0,
    time DATETIME,
    FOREIGN KEY (coin) REFERENCES coins(name) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (username) REFERENCES users(name) ON DELETE CASCADE ON UPDATE CASCADE
);
CREATE TABLE userCoins (
    username varchar(64),
    FOREIGN KEY (username) REFERENCES users(name) ON DELETE CASCADE ON UPDATE CASCADE
);
CREATE TABLE investments(
    username varchar(64),
    investment FLOAT(24) DEFAULT 0.0,
    FOREIGN KEY (username) REFERENCES users(name) ON DELETE CASCADE ON UPDATE CASCADE
);
CREATE TABLE wallet (
    username varchar(64),
    amount FLOAT(24),
    FOREIGN KEY (username) REFERENCES users(name) ON DELETE CASCADE ON UPDATE CASCADE
);
CREATE TABLE fundTransferHistory (
    username varchar(64),
    amount FLOAT(24),
    transType BOOLEAN,
    fee FLOAT(24),
    donation FLOAT(24) DEFAULT 0.0,
    time DATETIME,
    externalTransfer BOOLEAN,
    FOREIGN KEY (username) REFERENCES users(name) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE donations (
    username varchar(64),
    amount FLOAT(24),
    FOREIGN KEY (username) REFERENCES users(name) ON DELETE CASCADE ON UPDATE CASCADE
);

-- RUN THESE TOO
CREATE TABLE userAuth (
    username varchar(64),
    hash varchar(96),
    FOREIGN KEY (username) REFERENCES users(name) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
ALTER TABLE transactions ADD COLUMN note VARCHAR(196) DEFAULT "";
-- ALTER DATABASE cucekTrading CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- SHOW CREATE TABLE transactions\G;
