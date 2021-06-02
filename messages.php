<?php

function MSG_AccessDenied() {
    $result = array(
        'result' => false, 
        'err'    => "ACCESS DENIED"
    );

    return $result;
}

function MSG_FieldsNotSet() {
    $result = array(
        'result' => false, 
        'err'    => "Required Fields not Filled"   
    );

    return $result;
}

function MSG_InvalidRequest() {
    $result = array(
        'result' => false, 
        'err'    => "Required Fields not Filled"   
    );

    return $result;
}

function TXT_FailedToConnectDB() {
    return "Could not connect to database";
}

function TXT_SqlError($conn) {
    return "SQL ERROR: " . $conn->error;
}
?>
