<?php
include('database.php');
include('utility.php');

function getUserCoins() {
    // Return value
    $return_val = array(
        'result' => true, // success
        'err'    => "",   // err msg
        'userCoins'  => array()
    );
    if (empty($_POST["name"])) {
        $return_val['result'] = false;
        $return_val['err'] = "*name not set";
        return $return_val;
    }
    $name = $_POST["name"];

    $conn = connectToDB();
    if (!$conn) {
        $logger = new Logger();
        $logger->addLog(__FUNCTION__, "*Connection to database failed.");
        $return_val['result'] = false;
        $return_val['err'] = "*Connection to database failed.";
        return $return_val;
    }
    // SQL DB
    $sql = "SELECT * FROM userCoins WHERE username='$name'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $keys = array_keys($row);

        $sql = "SELECT * FROM coins";
        $result = $conn->query($sql);
        $coins = array();
        if ($result->num_rows > 0) {
            while ($rw = $result->fetch_assoc()) {
                array_push($coins, $rw);
            }
        }

        $data = array();
        foreach($keys as $key) {
            if ($key != "username" && $row["$key"] > 0) {
                $coinInfo = array();
                foreach ($coins as $c => $coin) {
                    if ($coin["id"] == $key) {
                        $coinInfo = $coin;
                        break;
                    }
                }
                array_push($data, array(
                    "coin" => $key, 
                    "count" => $row["$key"], 
                    "coinInfo" => $coinInfo,
                    "profit" => 0,
                    "percent" => 0
                ));
            }
        }
        $return_val['userCoins'] = $data;
    }
    return $return_val;
}

function getCoinList() {
    // Return value
    $return_val = array(
        'result' => true, // success
        'err'    => "",   // err msg
        'coins'  => Array()
    );

    $conn = connectToDB();
    if (!$conn) {
        $return_val['result'] = false;
        $return_val['err'] = "*Connection to database failed.";
        return $return_val;
    }

    $sql = "SELECT * FROM coins";
    $result = $conn->query($sql);
    if (!$result) {
        $return_val['result'] = false;
        $return_val['err'] = "Error failed to get";
        return $return_val;
    }
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            array_push($return_val['coins'], $row);
        }
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
case 'info':
    echo json_encode(getUserCoins());
    break;

case 'list':
    echo json_encode(getCoinList());
    break;

default:
    echo json_encode(showInvalidRequest("INVALID_TYPE $type"));
    break;
}

?>

