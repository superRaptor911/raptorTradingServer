<?php
include_once('database.php');
include_once('utility.php');
include("source/transactionManagement.php");

// Function to register user
function addTransaction() {
    // Return value
    $return_val = array(
        'result' => true, // success
        'err'    => "",   // err msg
    );

    // Verufy that it's admin
    verifyAdmin();

    $requiredValues = array("username", "coinName", "coinPrice", "coinCount", "fee", "transtype", "hash");
    checkPostRequiredValues($requiredValues);

    // Get Values
    $username = $_POST["username"];
    $coinName = $_POST["coinName"];
    $coinPrice = $_POST["coinPrice"];
    $coinCount = $_POST["coinCount"];
    $fee = $_POST["fee"];
    $transtype = $_POST["transtype"];

    if ($transtype == 1) {
        buyCoin($username, $coinName, $coinCount, $coinPrice, $fee);
    }
    else {
        sellCoin($username, $coinName, $coinCount, $coinPrice, $fee);
    }

    return $return_val;
}


// Function to Transfer Fund
function transferFund() {
    // Return value
    $return_val = array(
        'result' => true, // success
        'err'    => "",   // err msg
    );

    // Verufy that it's admin
    verifyAdmin();

    $requiredValues = array("username", "amount", "transtype", "fee");
    checkPostRequiredValues($requiredValues);

    $username = $_POST["username"];
    $amount = $_POST["amount"];
    $transtype = $_POST["transtype"];
    $fee = $_POST["fee"];

    $donation = 0;
    if (isset($_POST["donation"])) {
        $donation = $_POST["donation"];
    }

    if ($transtype == 1) {
        depositFund($username, $amount, $donation, $fee);
    }
    else {
        withdrawFund($username, $amount, $donation, $fee);
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

    $conn = connectToDBEnhanced();
    $condition = "";
    if (isset($_POST["username"])) {
        $username = $_POST["username"];
        if ($username != "") {
            $condition = "WHERE u.name = '$username'";
        }
    }

    $sql = "SELECT t.*, u.avatar AS userAvatar, c.avatar AS coinAvatar, c.id AS coinId FROM transactions t
        LEFT JOIN
        users u ON t.username=u.name
        LEFT JOIN
        coins c ON t.coin=c.name
        $condition
        GROUP BY t.id";

    $result = executeSql($conn, $sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            array_push($return_val['trans'], $row);
        }
    }

    return $return_val;
}


function getWalletInfo() {
    // Return value
    $return_val = array(
        'result' => true, // success
        'err'    => "",   // err msg
        'wallet'  => Array()
    );

    $username = $_POST["username"];

    $conn = connectToDBEnhanced();

    $sql = "SELECT * FROM wallet
        WHERE username= '$username'";

    $result = executeSql($conn, $sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $return_val['wallet'] = $row;
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

    $conn = connectToDBEnhanced();

    // Get Investment data
    $sql = "SELECT i.*, u.avatar AS userAvatar, w.amount AS amount FROM investments i
        LEFT JOIN
            users u ON i.username = u.name 
        LEFT JOIN
            wallet w ON i.username = w.username";

    $investments = array();
    $result = executeSql($conn, $sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $investments[$row["username"]] = $row;
        }
    }

    $finalData = array();
    // Get coins
    $sql = "SELECT * FROM userCoins";
    $result = executeSql($conn, $sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data = array();
            $coins = array();
            $username = $row['username'];
            $investment = $investments["$username"]["investment"];
            $userAvatar = $investments["$username"]["userAvatar"];
            $walletAmount = $investments["$username"]["amount"];

            foreach ($row as $key => $count) {
                if ($key != "username" && $count != 0) {
                    array_push($coins, array("coin" => $key, "count" => $count));
                }
            }

            $data = array(
                "username" => $username,
                "userAvatar" => $userAvatar,
                "investment" => $investment,
                "amount" => $walletAmount,
                "coins" => $coins
            );
            array_push($finalData, $data);
        }
    }

    $return_val["data"] = $finalData;
    return $return_val;
}

function getFundTransferHistory() {
    // Return value
    $return_val = array(
        'result' => true, // success
        'err'    => "",   // err msg
        'history'  => Array()
    );

    $conn = connectToDBEnhanced();
    $condition = "users u ON u.name = f.username";
    if (isset($_POST["username"])) {
        $username = $_POST["username"];
        if ($username != "") {
            $condition = "users u ON u.name = '$username' 
                        WHERE username= '$username'";
        }
    }

    $sql = "SELECT f.*, u.avatar AS userAvatar FROM fundTransferHistory f
        LEFT JOIN
        $condition
        ";

    $result = executeSql($conn, $sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            array_push($return_val['history'], $row);
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
case 'addTransaction':
    echo json_encode(addTransaction());
    break;

case 'list':
    echo json_encode(getTransactionList());
    break;

case 'walletInfo':
    echo json_encode(getWalletInfo());
    break;

case 'investmentNcoins':
    echo json_encode(getInvestmentsPlusCoins());
    break;

case 'fundTransfer':
    echo json_encode(transferFund());
    break;

case 'fundTransferHistory':
    echo json_encode(getFundTransferHistory());
    break;

default:
    echo json_encode(MSG_InvalidRequest());
    break;
}

?>
