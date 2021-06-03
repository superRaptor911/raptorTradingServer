<?php

include('../database.php');

$db = "cucekTrading";

// Function To create user
function createUser($username, $email, $avatar) {
    global $db;
    $conn = connectToDBEnhanced($db);

    $sql = "INSERT INTO users(name, email, avatar) VALUES('$username', '$email', '$avatar')";
    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception(TXT_SqlError($conn));
    }
}



?>
