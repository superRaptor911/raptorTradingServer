-- RUN THESE COMMANDS IN UR SQL DB
create database cucekTrading;
GRANT ALL PRIVILEGES ON cucekTrading.* TO 'my_fcuking_username'@'localhost';
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
    coinCount INT(6),
    cost FLOAT(6,4),
    transType BOOLEAN DEFAULT 1,
    fee FLOAT(24) DEFAULT 0,
    time DATETIME,
    FOREIGN KEY (coin) REFERENCES coins(name) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (username) REFERENCES users(name) ON DELETE CASCADE ON UPDATE CASCADE
);
CREATE TABLE userCoins (
    username varchar(64),
    dogeinr INT(6) DEFAULT 0,
    trxinr INT(6) DEFAULT 0,
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
    time DATETIME,
    externalTransfer BOOLEAN,
    FOREIGN KEY (username) REFERENCES users(name) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- RUN THESE TOO
ALTER TABLE investments MODIFY COLUMN investment FLOAT(24) DEFAULT 0.0;
ALTER TABLE transactions MODIFY COLUMN cost FLOAT(24);
ALTER TABLE transactions DROP COLUMN transStatus;


ALTER TABLE transactions MODIFY COLUMN coinCount FLOAT(24);
ALTER TABLE userCoins MODIFY COLUMN dogeinr FLOAT(24);
ALTER TABLE userCoins MODIFY COLUMN trxinr FLOAT(24);
ALTER TABLE userCoins MODIFY COLUMN hotinr FLOAT(24);

-- ALTER DATABASE cucekTrading CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- SHOW CREATE TABLE transactions\G;
