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
    date DATE
);
CREATE TABLE userCoins (
    username varchar(64),
    dogeinr INT(6) DEFAULT 0,
    trxinr INT(6) DEFAULT 0,
    FOREIGN KEY (username) REFERENCES users(name) ON DELETE CASCADE ON UPDATE CASCADE
);
CREATE TABLE investments(
    username varchar(64),
    investment INT(6) DEFAULT 0,
    FOREIGN KEY (username) REFERENCES users(name) ON DELETE CASCADE ON UPDATE CASCADE
);
CREATE TABLE wallet (
    username varchar(64),
    amount FLOAT(24),
    FOREIGN KEY (username) REFERENCES users(name) ON DELETE CASCADE ON UPDATE CASCADE
);
-- RUN THESE TOO
ALTER TABLE transactions ADD FOREIGN KEY (username) REFERENCES users(name) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE transactions ADD FOREIGN KEY (coin) REFERENCES coins(name) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE transactions ADD COLUMN transType VARCHAR(10) DEFAULT 'DEPOSIT';
--
ALTER TABLE transactions DROP COLUMN transType;
ALTER TABLE transactions ADD COLUMN transStatus BOOLEAN DEFAULT 1;
ALTER TABLE transactions ADD COLUMN transType BOOLEAN DEFAULT 1;
--
ALTER TABLE investments MODIFY COLUMN investment FLOAT(24) DEFAULT 0.0;
ALTER TABLE transactions MODIFY COLUMN cost FLOAT(24);
-----------------------------------------------------------------------------
-- ALTER DATABASE cucekTrading CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- SHOW CREATE TABLE transactions\G;
-- ALTER TABLE transactions DROP FOREIGN KEY transactions_ibfk_1;
-- ALTER TABLE transactions DROP FOREIGN KEY transactions_ibfk_2;
