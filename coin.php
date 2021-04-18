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
    $coinData = json_decode(file_get_contents("https://api.wazirx.com/api/v2/tickers"), true);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $coinId = $row['id'];
            $coins["$coinId"] = $coinData["$coinId"];
        }
    }

    $return_val['coins'] = $coins;
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

    default:
        echo json_encode(showInvalidRequest("INVALID_TYPE $type"));
        break;
}

?>
