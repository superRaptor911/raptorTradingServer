<?php
include_once('secret.php');
include_once('messages.php');

$GLOBALS["database"] = "cucekTrading";

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

function connectToDBEnhanced() {
    global $database;
    $servername = "localhost";
    $username = getSqlUsername();
    $password = getSqlPassword();
    $db = $database;

    if (empty($db)) {
        throw new Exception("DB not set");
    }

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
function verifyAdmin() {
    $hash = $_POST["hash"];
    if ($hash != getAdminPassword()) {
        throw new Exception(TXT_AccessDenied());
    }
}

// Exception handler
function topLevelExceptionHandler($exception) {
    // Return value
    $return_val = array(
        'result' => false, 
        'err'    => $exception->getMessage()
    );

    echo json_encode($return_val);
}

set_exception_handler('topLevelExceptionHandler');
?>
