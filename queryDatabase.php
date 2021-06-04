<?php
include('database.php');
include('utility.php');

function executeQuery() {
    // Return value
    $return_val = array(
        'result' => true, // success
        'err'    => "",   // err msg
        'rows'  => Array(),
        'columns' => array(),
    );

    $conn = readOnlyConnectToDB();
    if (!$conn) {
        $return_val['result'] = false;
        $return_val['err'] = "*Connection to database failed.";
        return $return_val;
    }

    $sql = $_POST["query"];
    $result = $conn->query($sql);
    if (!$result) {
        $return_val['result'] = false;
        $return_val['err'] = "Error failed to get, " . $conn->error;
        return $return_val;
    }
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
           array_push($return_val['rows'], $row);
        }
    }
    
    if (count($return_val['rows']) > 0) {
        $return_val['columns'] = array_keys($return_val['rows'][0]);
    }

    return $return_val;
}

// ------------------Execution starts here-----------------
$_POST = json_decode(file_get_contents('php://input'), true);
if (empty($_POST["type"])) {
    echo json_encode(showInvalidRequest("Type not specified"));
}

$type = $_POST["type"];

switch ($type) {
    case 'execute':
        echo json_encode(executeQuery());
        break;

    default:
        echo json_encode(MSG_InvalidRequest());
        break;
}

?>
