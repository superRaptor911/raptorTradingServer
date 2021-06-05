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

    $name = $_POST["name"];
    $conn = connectToDBEnhanced();
    // SQL DB
    $sql = "SELECT * FROM userCoins WHERE username='$name'";
    $result = executeSql($conn, $sql);
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

    $conn = connectToDBEnhanced();

    $sql = "SELECT * FROM userCoins";
    $result = executeSql($conn, $sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            array_push($return_val['coins'], $row);
        }
    }

    return $return_val;
}

function getCoinCount() {
    // Return value
    $return_val = array(
        'result' => true, // success
        'err'    => "",   // err msg
        'coins'  => Array()
    );

    $conn = connectToDBEnhanced();

    $sql = "SELECT * FROM userCoins";
    $result = executeSql($conn, $sql);
    if ($result->num_rows > 0) {
        $coins = array();
        while ($row = $result->fetch_assoc()) {
            foreach ($row as $coin => $value) {
                if ($coin != "username") {
                    if (!isset($coins["$coin"])) {
                        $coins["$coin"] = 0;
                    }
                    $coins["$coin"] += $value;
                }
            }
        }
        $return_val['coins'] = $coins;
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

    case 'count':
        echo json_encode(getCoinCount());
        break;

    default:
        echo json_encode(MSG_InvalidRequest());
        break;
}

?>

