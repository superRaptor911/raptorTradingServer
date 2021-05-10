<?php
include('logger.php');

function connectToDB() {
    $servername = "localhost";
    $username = "my_fcuking_username";
    $password = "my_fcuking_db_password";
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


// Function to verify user using name and pass
function verifyUser($hash) {
    return $hash == "my_fcuking_admin_password";
}

?>
