<?php
include('database.php');
include('utility.php');

// Function to register user
function addTransaction() {
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

    $username = $_POST["username"];
    $coinName = $_POST["coinName"];
    $coinPrice = $_POST["coinPrice"];
    $coinCount = $_POST["coinCount"];

    $conn = connectToDB();
    if (!$conn) {
        $return_val['result'] = false;
        $return_val['err'] = "*Connection to database failed.";
        return $return_val;
    }

    $sql = "INSERT INTO transactions(username, coin, coinCount, cost)
        VALUES('$username', '$coinName', '$coinCount', '$coinPrice')";

    if (!$conn->query($sql)) {
        $return_val['result'] = false;
        $return_val['err'] = "Add transaction failed";
        $logger->addLog(__FUNCTION__, "Registration Failed", '-');
        $logger->addLog(__FUNCTION__, $conn->error);
        return $return_val;
    }

    return $return_val;
}

function getTransactionList() {
    // Return value
    $return_val = array(
        'result' => true, // success
        'err'    => "",   // err msg
        'trans'  => Array()
    );

    $conn = connectToDB();
    if (!$conn) {
        $return_val['result'] = false;
        $return_val['err'] = "*Connection to database failed.";
        return $return_val;
    }

    $sql = "SELECT t.*, u.avatar AS userAvatar, c.avatar AS coinAvatar, c.id AS coinId FROM transactions t
        LEFT JOIN
            users u ON t.username=u.name
        LEFT JOIN
            coins c ON t.coin=c.name
        GROUP BY t.id";

    $result = $conn->query($sql);
    if (!$result) {
        $return_val['result'] = false;
        $return_val['err'] = "Error failed to get";
        return $return_val;
    }
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            array_push($return_val['trans'], $row);
        }
    }

    return $return_val;
}

function getTransactionInfo() {
    // Return value
    $return_val = array(
        'result' => true, // success
        'err'    => "",   // err msg
        'trans'  => Array()
    );

    $username = $_POST["username"];

    $conn = connectToDB();
    if (!$conn) {
        $return_val['result'] = false;
        $return_val['err'] = "*Connection to database failed.";
        return $return_val;
    }

    $sql = "SELECT t.*, u.avatar AS userAvatar, c.avatar AS coinAvatar, c.id AS coinId FROM transactions t
        LEFT JOIN
            users u ON t.username=u.name
        LEFT JOIN
            coins c ON t.coin=c.name
        WHERE u.name = '$username'
        GROUP BY t.id";

    $result = $conn->query($sql);
    if (!$result) {
        $return_val['result'] = false;
        $return_val['err'] = "Error failed to get";
        return $return_val;
    }
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            array_push($return_val['trans'], $row);
        }
    }

    return $return_val;
}

function getInvestmentsPlusCoins() {
    // Return value
    $return_val = array(
        'result' => true, // success
        'err'    => "",   // err msg
        'data'  => Array()
    );

    $conn = connectToDB();
    if (!$conn) {
        $return_val['result'] = false;
        $return_val['err'] = "*Connection to database failed.";
        return $return_val;
    }

    // Get Investment data
    $sql = "SELECT i.*, u.avatar AS userAvatar FROM investments i
            LEFT JOIN
                users u ON i.username = u.name ";
    
    $investments = array();
    $result = $conn->query($sql);
    if (!$result) {
        $return_val['result'] = false;
        $return_val['err'] = "SQL ERROR: " .$conn->error;
        return $return_val;
    }
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $investments[$row["username"]] = $row;
        }
    }

    $finalData = array();
    // Get coins
    $sql = "SELECT * FROM userCoins";
    $result = $conn->query($sql);
    if (!$result) {
        $return_val['result'] = false;
        $return_val['err'] = "SQL ERROR: " .$conn->error;
        return $return_val;
    }
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data = array();
            $coins = array();
            $username = $row['username'];
            $investment = $investments["$username"]["investment"];
            $userAvatar = $investments["$username"]["userAvatar"];

            foreach ($row as $key => $count) {
                if ($key != "username" && $count != 0) {
                    array_push($coins, array("coin" => $key, "count" => $count));
                }
            }

            $data = array(
                "username" => $username,
                "userAvatar" => $userAvatar,
                "investment" => $investment,
                "coins" => $coins
            );
            array_push($finalData, $data);
        }
    }

    $return_val["data"] = $finalData;
    return $return_val;
}

// ------------------Execution starts here-----------------
$_POST = json_decode(file_get_contents('php://input'), true);
if (empty($_POST["type"])) {
    echo json_encode(showInvalidRequest("Type not specified"));
}

$type = $_POST["type"];

switch ($type) {
    case 'add':
        echo json_encode(addTransaction());
        break;

    case 'list':
        echo json_encode(getTransactionList());
        break;

    case 'info':
        echo json_encode(getTransactionInfo());
        break;

    case 'investmentNcoins':
        echo json_encode(getInvestmentsPlusCoins());
        break;

    default:
        echo json_encode(showInvalidRequest("INVALID_TYPE $type"));
        break;
}

?>
