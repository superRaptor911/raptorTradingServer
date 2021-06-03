<?php
include('secret.php');
include('messages.php');

function connectToDB() {
    $servername = "localhost";
    $username = getSqlUsername();
    $password = getSqlPassword();
    $db = "cucekTrading";

    $conn = new mysqli($servername, $username, $password, $db);
    // Check connection
    if ($conn->connect_error) {
        return null;
    }
    return $conn;
}

function connectToDBEnhanced($db) {
    $servername = "localhost";
    $username = getSqlUsername();
    $password = getSqlPassword();

    $conn = new mysqli($servername, $username, $password, $db);
    // Check connection
    if ($conn->connect_error) {
        throw new Exception(TXT_FailedToConnectDB());
    }
    return $conn;
}

function readOnlyConnectToDB() {
    $servername = "localhost";
    $username = "tempUser";
    $password = "password";
    $db = "cucekTrading";

    $conn = new mysqli($servername, $username, $password, $db);
    // Check connection
    if ($conn->connect_error) {
        return null;
    }
    return $conn;
}

function showInvalidRequest($msg = "") {
    // Return value
    $return_val = array(
        'result' => false, 
        'err'    => "INVALID_REQUEST $msg"
    );

    return $return_val;
}


function executeSql($conn, $sql) {
    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception(TXT_SqlError($conn));
    }

    return $result;
}

// Function to verify admin
function verifyAdmin($hash) {
    return $hash == getAdminPassword();
}

?>
