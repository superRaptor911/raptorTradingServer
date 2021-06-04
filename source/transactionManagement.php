<?php

include_once('../database.php');
include_once('coinManagement.php');

$DEPOSIT = 1;
$WITHDRAW = 0;


// Function to buy coin
function buyCoin($username, $coin, $count, $coinPrice, $fee) {
    global $DEPOSIT;
    $totalCost = $count * $coinPrice + $fee;
    $balance = getWalletBalance($username);
    $coinBalance = getCoinCount($username, $coin);

    $curBalance = $balance - $totalCost;
    $curCoinBalance = $coinBalance + $count;
    if ($curBalance < 0) {
        throw new Exception("INSUFFICIENT BALANCE");
    }

    addTransactionHistory($username, $coin, $count, $coinPrice, $fee, $DEPOSIT);
    setWalletBalance($username, $curBalance);
    setCoinCount($username, $coin, $curCoinBalance);
}


// Function to sell coin
function sellCoin($username, $coin, $count, $coinPrice, $fee) {
    global $WITHDRAW;
    $netAmount = $count * $coinPrice - $fee;
    $balance = getWalletBalance($username);
    $coinBalance = getCoinCount($username, $coin);

    $curBalance = $balance + $netAmount;
    $curCoinBalance = $coinBalance - $count;
    if ($curCoinBalance < 0) {
        throw new Exception("INSUFFICIENT COINS");
    }

    addTransactionHistory($username, $coin, $count, $coinPrice, $fee, $WITHDRAW);
    setWalletBalance($username, $curBalance);
    setCoinCount($username, $coin, $curCoinBalance);
}


// Funtion to deposit Money
function depositFund($username, $amount, $donation, $fee) {
    global $DEPOSIT;
    $curBalance = getWalletBalance($username);
    $netAmount = $amount - $donation - $fee;

    // Increase Balance
    $curBalance += $netAmount;

    addFundTransferHistory($username, $amount, $donation, $fee, $DEPOSIT, 1);
    setWalletBalance($username, $curBalance);
    updateInvestment($username, $amount);

    if ($donation > 0) {
        addDonation($username, $donation);
    }
}


// Funtion to withdraw Money
function withdrawFund($username, $amount, $donation, $fee) {
    global $WITHDRAW;
    $curBalance = getWalletBalance($username);
    $netAmount = $amount + $donation + $fee;

    // Decrease Balance
    $curBalance -= $netAmount;

    if ($curBalance < 0) {
        throw new Exception("INSUFFICIENT BALANCE");
    }

    addFundTransferHistory($username, $amount, $donation, $fee, $WITHDRAW, 1);
    setWalletBalance($username, $curBalance);
    updateInvestment($username, -$amount);

    if ($donation > 0) {
        addDonation($username, $donation);
    }
}


// Function to get wallet Balance
function getWalletBalance($username) {
    $conn = connectToDBEnhanced();

    // GET user wallet
    $sql = "SELECT * FROM wallet WHERE username='$username'";
    $result = executeSql($conn, $sql);

    // Check if usr exist?
    if ($result->num_rows != 1) {
        throw new Exception("Failed to get $username's wallet balance");
    }

    // Return Balance
    $row = $result->fetch_assoc();
    $curBalance = $row["amount"];
    return $curBalance;
}


// Function To set Wallet Balance
function setWalletBalance($username, $amount) {
    $conn = connectToDBEnhanced();

    // Sql query
    $sql = "UPDATE wallet 
        SET
        amount=$amount
        WHERE
        username='$username'";

    executeSql($conn, $sql);
}


// Function to add donation
function addDonation($username, $amount) {
    $conn = connectToDBEnhanced();

    $sql = "INSERT INTO donations(username, amount) VALUES('$username', $amount)";
    executeSql($conn, $sql);
}

// Function to add Fund Transfer history
function addFundTransferHistory($username, $amount, $donation, $fee, $type, $isExternal) {
    $conn = connectToDBEnhanced();

    $sql = "INSERT INTO fundTransferHistory(username, amount, transType, fee, externalTransfer, donation,time)
        VALUES('$username', $amount, $type, $fee, $isExternal, $donation, NOW())";

    executeSql($conn, $sql);
}

function updateInvestment($username, $change) {
    $conn = connectToDBEnhanced();

    $sql = "UPDATE investments
        SET investment = investment + $change 
        WHERE username = '$username'";

    executeSql($conn, $sql);
}

// Function To add Coin Transaction History
function addTransactionHistory($username, $coin, $count, $coinPrice, $fee, $type) {
    $conn = connectToDBEnhanced();

    $sql = "INSERT INTO transactions(username, coin, coinCount, cost, fee, transType, time)
        VALUES('$username', '$coin', '$count', '$coinPrice', $fee, $type, NOW())";

    executeSql($conn, $sql);
}

// Function to get coin count of user
function getCoinCount($username, $coin) {
    $conn = connectToDBEnhanced();

    $sql = "SELECT uc.*, c.id FROM userCoins uc 
        LEFT JOIN
            coins c ON c.name = '$coin'
        WHERE uc.username='$username'";

    $result = executeSql($conn, $sql);
    // Check if usr exist?
    if ($result->num_rows != 1) {
        throw new Exception("Failed to get $username's coin count");
    }

    // Return Balance
    $row = $result->fetch_assoc();

    $coinId = $row['id'];
    if (!isset($row["$coinId"])) {
        throw new Exception("Failed to get Coin $coin");
    }

    $coinsLeft = $row["$coinId"]; 
    return $coinsLeft;
}


// Function to set coin count of user
function setCoinCount($username, $coin, $count) {
    $conn = connectToDBEnhanced();

    $coinId = getCoinID($coin);
    // Update coins
    $sql = "UPDATE userCoins
        SET 
            $coinId = $count
        WHERE
            username='$username'";

    executeSql($conn, $sql);
}

?>
