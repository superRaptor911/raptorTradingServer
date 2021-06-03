<?php

include_once('../database.php');

$db = "cucekTrading";

// Function To create user
function createUser($username, $email, $avatar) {
    global $db;
    $conn = connectToDBEnhanced($db);

    $sql = "INSERT INTO users(name, email, avatar) VALUES('$username', '$email', '$avatar')";
    executeSql($conn, $sql);

    createWallets($username);
}


function createWallets($username) {
    global $db;
    $conn = connectToDBEnhanced($db);

    $sql = "INSERT INTO wallet(username, amount) VALUES('$username', 0)";
    executeSql($conn, $sql);

    $sql = "INSERT INTO userCoins(username) VALUES('$username')";
    executeSql($conn, $sql);

    $sql = "INSERT INTO investments(username) VALUES('$username')";
    executeSql($conn, $sql);
}



?>
