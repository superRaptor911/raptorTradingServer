<?php 

include_once(dirname(__FILE__) .'/../database.php');


// Function to add a new coin
function addCoin($coin, $coinID, $avatar) {
    $conn = connectToDBEnhanced();

    $sql = "INSERT INTO coins(name, avatar, id)
        VALUES('$coin', '$avatar', '$coinID')";
    executeSql($conn, $sql);

    // Add entry
    $sql = "ALTER TABLE userCoins ADD COLUMN $coinID FLOAT(24) DEFAULT 0";
    executeSql($conn, $sql);
}

// Function to get coidID from coin name
function getCoinID($coin) {
    $conn = connectToDBEnhanced();

    $sql = "SELECT id FROM coins
        WHERE name='$coin'";

    $result = executeSql($conn, $sql);
    // Check if usr exist?
    if ($result->num_rows != 1) {
        throw new Exception("Failed to get $coin's ID");
    }

    // Return Balance
    $row = $result->fetch_assoc();
    $coinId = $row['id'];
    return $coinId;
}

?>
