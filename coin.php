<?php
include('database.php');
include('utility.php');

// Function to register user
function registerCoin() {
    // Return value
    $return_val = array(
        'result' => true, // success
        'err'    => "",   // err msg
        'pass_err' => ""  // wrong pass err msg
    );

    $hash = $_POST['hash'];
    if (!verifyUser($hash)) {
        $return_val['result'] = false;
        $return_val['err'] = "Permission Denied";
        return $return_val;
    }
    // Logger
    $logger = new Logger();

    // Chk all fields are filled
    if (empty($_POST["name"])) {
        $return_val['result'] = false;
        $return_val['err'] = "*Please fill data";
        return $return_val;
    }

    $name = $_POST["name"];
    $avatar = $_POST["avatar"];
    $coinId = $_POST["coinId"];


    if ($name == false) {
        $return_val['result'] = false;
        $return_val['err'] = "*User name should not contain special characters or space.";
        return $return_val;
    }

    $conn = connectToDB();
    if (!$conn) {
        $return_val['result'] = false;
        $return_val['err'] = "*Connection to database failed.";
        return $return_val;
    }

    $sql = "INSERT INTO coins(name, avatar, id)
        VALUES('$name', '$avatar', '$coinId')";

    if (!$conn->query($sql)) {
        $return_val['result'] = false;
        $return_val['err'] = "*Please select a different coin name, already in use";
        $logger->addLog(__FUNCTION__, "Registration Failed", '-');
        $logger->addLog(__FUNCTION__, $conn->error);
        return $return_val;
    }

    $sql = "ALTER TABLE userCoins ADD COLUMN $coinId FLOAT(24) DEFAULT 0";
    if (!$conn->query($sql)) {
        $return_val['result'] = false;
        $return_val['err'] = "*Please select a different coin name, already in use";
        $logger->addLog(__FUNCTION__, "Registration Failed", '-');
        $logger->addLog(__FUNCTION__, $conn->error);
        return $return_val;
    }

    $logger->addLog(__FUNCTION__, "Registration success : User $name was created.");
    /* $_POST["email"] = $email; */
    /* sendOTP(); */
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


function getCoinPrices() {
    // Return value
    $return_val = array(
        'result' => true, // success
        'err'    => "",   // err msg
        'type'   => "live",
        'coins'  => Array()
    );

    $conn = connectToDB();
    if (!$conn) {
        $return_val['result'] = false;
        $return_val['err'] = "*Connection to database failed.";
        return $return_val;
    }

    $sql = "SELECT id FROM coins";
    $result = $conn->query($sql);
    if (!$result) {
        $return_val['result'] = false;
        $return_val['err'] = "Error failed to get";
        return $return_val;
    }

    $coins = Array();

    $file_time = filemtime("prices.json");
    $date = new DateTime();
    $time_now = $date->getTimestamp();

    if (isset($_POST["firstFetch"]) || ($time_now - $file_time) < 3) {
        $coins = json_decode(file_get_contents("prices.json"), true);
        $return_val["type"] = "cached";
    }
    else {
        $coinData = json_decode(file_get_contents("https://api.wazirx.com/api/v2/tickers"), true);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $coinId = $row['id'];
                if (!$coinData["$coinId"]) {
                    $return_val['result'] = false;
                    $return_val['err'] = "Error failed to get";
                    return $return_val;
                }
                $coins["$coinId"] = $coinData["$coinId"];
            }
        }
        // Update cache every 5 seconds
        if (!$file_time || ($time_now - $file_time) > 5) {
            file_put_contents("prices.json", json_encode($coins));
        }

    }

    $return_val['coins'] = $coins;
    return $return_val;
}

function getCoinHistory() {
    // Return value
    $return_val = array(
        'result' => true, // success
        'err'    => "",   // err msg
        'history'  => Array()
    );

    $coin = $_POST["coin"];
    $period = $_POST["period"];
    $limit = $_POST["limit"];

    $data = json_decode(file_get_contents("https://x.wazirx.com/api/v2/k?market=$coin&period=$period&limit=$limit"), true);

    $return_val['history'] = $data;
    return $return_val;
}

function getCoinInfo() {
    // Return value
    $return_val = array(
        'result' => true, // success
        'err'    => "",   // err msg
        'info'  => array(),
        'investors' => array()
    );

    $coinId = $_POST["coin"];

    $conn = connectToDB();
    if (!$conn) {
        $return_val['result'] = false;
        $return_val['err'] = "*Connection to database failed.";
        return $return_val;
    }

    $sql = "SELECT c.*, uc.username, uc.$coinId, u.avatar AS userAvatar FROM coins c 
        LEFT JOIN
        userCoins uc ON uc.$coinId != 0
        LEFT JOIN
        users u ON uc.username = u.name
        WHERE id = '$coinId'";
    $result = $conn->query($sql);
    if (!$result) {
        $return_val['result'] = false;
        $return_val['err'] = "Error failed to get";
        return $return_val;
    }

    /* $row = $result->fetch_assoc(); */
    $flag = false;
    $investors = array();
    while ($row = $result->fetch_assoc()) {
        if (!$flag) {
            $return_val['info'] = $row;
            $flag = true;
        }

        array_push($investors, array(
            'username' => $row['username'],
            'count' => $row["$coinId"], 
            'avatar' => $row['userAvatar']
        ));
    }
    $return_val['investors'] = $investors;
    return $return_val;
}

// ------------------Execution starts here-----------------
$_POST = json_decode(file_get_contents('php://input'), true);
if (empty($_POST["type"])) {
    echo json_encode(showInvalidRequest("Type not specified"));
}

$type = $_POST["type"];

switch ($type) {
case 'register':
    echo json_encode(registerCoin());
    break;

case 'list':
    echo json_encode(getCoinList());
    break;

case 'prices':
    echo json_encode(getCoinPrices());
    break;

case 'history':
    echo json_encode(getCoinHistory());
    break;

case 'info':
    echo json_encode(getCoinInfo());
    break;

default:
    echo json_encode(showInvalidRequest("INVALID_TYPE $type"));
    break;
}

?>
