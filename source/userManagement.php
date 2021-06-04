<?php

include_once(dirname(__FILE__) .'/../database.php');

$db = "cucekTrading";

// Function To create user
function createUser($username, $email, $avatar) {
    $conn = connectToDBEnhanced();

    $sql = "INSERT INTO users(name, email, avatar) VALUES('$username', '$email', '$avatar')";
    executeSql($conn, $sql);

    createWallets($username);
}


function createWallets($username) {
    $conn = connectToDBEnhanced();

    $sql = "INSERT INTO wallet(username, amount) VALUES('$username', 0)";
    executeSql($conn, $sql);

    $sql = "INSERT INTO userCoins(username) VALUES('$username')";
    executeSql($conn, $sql);

    $sql = "INSERT INTO investments(username) VALUES('$username')";
    executeSql($conn, $sql);
}



?>
