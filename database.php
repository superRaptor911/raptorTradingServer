<?php
include('logger.php');
include('secret.php');


function connectToDB() {
    $servername = "localhost";
    $username = getSqlUsername();
    $password = getSqlPassword();
    $db = "cucekTrading";

    $conn = new mysqli($servername, $username, $password, $db);
    // Check connection
    if ($conn->connect_error) {
        $logger = new Logger();
        $logger->addLog(__FUNCTION__, "Connection failed: " . $conn->connect_error);
        return null;
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
        $logger = new Logger();
        $logger->addLog(__FUNCTION__, "Connection failed: " . $conn->connect_error);
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


// Function to verify admin
function verifyAdmin($hash) {
    return $hash == getAdminPassword();
}

?>
