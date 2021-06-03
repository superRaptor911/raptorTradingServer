<?php 

include_once('../database.php');

$db = "cucekTrading";

// Function to add a new coin
function addCoin($coin, $coinID, $avatar) {

}

// Function to get coidID from coin name
function getCoinID($coin) {
    global $db;
    $conn = connectToDBEnhanced($db);

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
