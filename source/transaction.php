<?php

include('../database.php');

$DEPOSIT = 1;
$WITHDRAW = 0;

function buyCoin($username, $coin, $amount, $coinPrice, $fee) {

}

// Funtion to deposit Money
function depositFund($username, $amount, $donation, $fee) {
    global $DEPOSIT;
    $curBalance = getWalletBalance($username);
    $netAmount = $amount - $donation - $fee;

    // Increase Balance
    $curBalance += $netAmount;

    addFundTransferHistory($username, $amount, $donation, $fee, $DEPOSIT, 1);
    setWalletBalance($username, $$curBalance);
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
    setWalletBalance($username, $$curBalance);
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
    $result = $conn->query($sql);
    if (!$result) {
        throw new Exception(TXT_SqlError($conn));
    }

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

    $result = $conn->query($sql);
    if (!$result) {
        throw new Exception(TXT_SqlError($conn));
    }
}


// Function to add donation
function addDonation($username, $amount) {
    $conn = connectToDBEnhanced();

    $sql = "INSERT INTO donations(username, amount) VALUES('$username', $amount)";
    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception(TXT_SqlError($conn));
    }
}

// Function to add Fund Transfer history
function addFundTransferHistory($username, $amount, $donation, $fee, $type, $isExternal) {
    $conn = connectToDBEnhanced();

    $sql = "INSERT INTO fundTransferHistory(username, amount, transType, fee, externalTransfer, donation,time)
        VALUES('$username', $amount, $type, $fee, $isExternal, $donation, NOW())";

    $result = $conn->query($sql);
    if (!$result) {
        throw new Exception(TXT_SqlError($conn));
    }
}

function updateInvestment($username, $change) {
    $conn = connectToDBEnhanced();

    $sql = "UPDATE investments
        SET investment = investment + $change 
        WHERE username = '$username'";

    $result = $conn->query($sql);
    if (!$result) {
        throw new Exception(TXT_SqlError($conn));
    }
}
?>
