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

    if (!isset($_POST["username"]) || !isset($_POST["coinName"]) || !isset($_POST["coinPrice"]) 
        || !isset($_POST["coinCount"]) || !isset($_POST["fee"]) || !isset($_POST["transtype"]) || !isset($_POST["hash"])) {

        $return_val['result'] = false;
        $return_val['err'] = "Please Fill Fields";
        return $return_val;
    }

    $username = $_POST["username"];
    $coinName = $_POST["coinName"];
    $coinPrice = $_POST["coinPrice"];
    $coinCount = $_POST["coinCount"];
    $fee = $_POST["fee"];
    $transtype = $_POST["transtype"];
    $hash = $_POST["hash"];
    
    $_POST["externalTransfer"] = 0;

    // Switch transfer type 
    if ($transtype == 0) {
        // Sell coin
        $_POST["transtype"] = 1;
        $result = transferCoins($username, $coinName, $coinCount, $transtype);
        if ($result['result'] == false) {
            return $result;
        }
    }
    else {
        $_POST["transtype"] = 0;
    }
    
    // Transfer Fund
    $result = transferFund();
    if ($result['result'] == false) {
        return $result;
    }
    
    // Update Transaction history
    $conn = connectToDB();
    if (!$conn) {
        $return_val['result'] = false;
        $return_val['err'] = "*Connection to database failed.";
        return $return_val;
    }

    $sql = "INSERT INTO transactions(username, coin, coinCount, cost, fee, transType, time)
        VALUES('$username', '$coinName', '$coinCount', '$coinPrice', $fee, $transtype, NOW())";

    if (!$conn->query($sql)) {
        $return_val['result'] = false;
        $return_val['err'] = "Add transaction failed";
        $logger->addLog(__FUNCTION__, "Registration Failed", '-');
        $logger->addLog(__FUNCTION__, $conn->error);
        return $return_val;
    }

    // Add coins
    if ($transtype == 1) {
        return transferCoins($username, $coinName, $coinCount, $transtype);
    }

    return $return_val;
}


function transferFund() {
    // Return value
    $return_val = array(
        'result' => true, // success
        'err'    => "",   // err msg
    );

    $hash = $_POST['hash'];
    if (!verifyUser($hash)) {
        $return_val['result'] = false;
        $return_val['err'] = "Permission Denied";
        return $return_val;
    }

    if (empty($_POST["username"]) || empty($_POST["amount"]) || !isset($_POST["transtype"]) || !isset($_POST["externalTransfer"])) {
        $return_val['result'] = false;
        $return_val['err'] = "Please Fill Fields";
        return $return_val;
    }

    $username = $_POST["username"];
    $amount = $_POST["amount"];
    $transtype = $_POST["transtype"];
    $externalTransfer = $_POST["externalTransfer"];
    $fee = $_POST["fee"];

    $conn = connectToDB();
    if (!$conn) {
        $return_val['result'] = false;
        $return_val['err'] = "*Connection to database failed.";
        return $return_val;
    }

    // GET user wallet
    $sql = "SELECT * FROM wallet WHERE username='$username'";
    $result = $conn->query($sql);
    if (!$result) {
        $return_val['result'] = false;
        $return_val['err'] = "SQL ERROR: " .$conn->error;
        return $return_val;
    }
    // Check if usr exist?
    if ($result->num_rows != 1) {
        $return_val['result'] = false;
        $return_val['err'] = "ERROR: User not found " .$username;
        return $return_val;
    }

    $row = $result->fetch_assoc();
    $curBalance = $row["amount"];
    // Low balance check
    if ($transtype == 0 && ($curBalance - $amount) < 0) {
        $return_val['result'] = false;
        $return_val['err'] = "ERROR: INSUFFICIENT FUND";
        return $return_val;
    }

    if ($transtype == 0) {
        $curBalance -= $amount;
    }
    elseif ($transtype == 1) {
        $curBalance += $amount;
    }
    else {
        $return_val['result'] = false;
        $return_val['err'] = "ERROR: INVALID Trans type";
        return $return_val;
    }
    // Deduct fees
    $curBalance -= $fee;

    // Add history
    $sql = "INSERT INTO fundTransferHistory(username, amount, transType, fee, externalTransfer, time)
        VALUES('$username', $amount, $transtype, $fee, $externalTransfer, NOW())";
    $result = $conn->query($sql);
    if (!$result) {
        $return_val['result'] = false;
        $return_val['err'] = "SQL ERROR: " .$conn->error;
        return $return_val;
    }

    // Update Wallet
    $sql = "UPDATE wallet 
        SET
        amount=$curBalance
        WHERE
        username='$username'";
    $result = $conn->query($sql);
    if (!$result) {
        $return_val['result'] = false;
        $return_val['err'] = "SQL ERROR: " .$conn->error;
        return $return_val;
    }

    // Modify investment table if external investment
    if ($externalTransfer == 1) {
        if ($transtype == 0) {
            $amount = -1 * $amount;
        }

        $sql = "UPDATE investments
            SET investment = investment + $amount 
            WHERE username = '$username'";

        $result = $conn->query($sql);
        if (!$result) {
            $return_val['result'] = false;
            $return_val['err'] = "SQL ERROR: " .$conn->error;
            return $return_val;
        }
    }

    return $return_val;
}

function transferCoins($username, $coinName, $coinCount, $transtype) {
    // Return value
    $return_val = array(
        'result' => true, // success
        'err'    => "",   // err msg
    );

    // GET COIN count
    $conn = connectToDB();
    if (!$conn) {
        $return_val['result'] = false;
        $return_val['err'] = "*Connection to database failed.";
        return $return_val;
    }

    // GET user wallet
    $sql = "SELECT uc.*, c.id FROM userCoins uc 
        LEFT JOIN
            coins c ON c.name = '$coinName'
        WHERE uc.username='$username'";

    $result = $conn->query($sql);
    if (!$result) {
        $return_val['result'] = false;
        $return_val['err'] = "Error failed to get";
        return $return_val;
    }
    if ($result->num_rows != 1) {
        $return_val['result'] = false;
        $return_val['err'] = "Error failed to get coin count";
        return $return_val;
    }

    $row = $result->fetch_assoc();
    $coinId = $row['id'];
    $coinsLeft = $row["$coinId"]; 

    if ($transtype == 0) {
        $coinsLeft = $coinsLeft - $coinCount;
        if ($coinsLeft < 0) {
            $return_val['result'] = false;
            $return_val['err'] = "You dont have enough coins to sell!";
            return $return_val;
        }
    }
    else {
        $coinsLeft = $coinsLeft + $coinCount;
    }

    // Update coins
    $sql = "UPDATE userCoins
        SET 
            $coinId = $coinsLeft
        WHERE
            username='$username'";

    $result = $conn->query($sql);
    if (!$result) {
        $return_val['result'] = false;
        $return_val['err'] = "Error failed to update coins " . $conn->error;
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


function getWalletInfo() {
    // Return value
    $return_val = array(
        'result' => true, // success
        'err'    => "",   // err msg
        'wallet'  => Array()
    );

    $username = $_POST["username"];

    $conn = connectToDB();
    if (!$conn) {
        $return_val['result'] = false;
        $return_val['err'] = "*Connection to database failed.";
        return $return_val;
    }

    $sql = "SELECT * FROM wallet
        WHERE username= '$username'";

    $result = $conn->query($sql);
    if (!$result) {
        $return_val['result'] = false;
        $return_val['err'] = "Error failed to get";
        return $return_val;
    }
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

function getFundTransferHistory() {
    // Return value
    $return_val = array(
        'result' => true, // success
        'err'    => "",   // err msg
        'history'  => Array()
    );

    $username = $_POST["username"];

    $conn = connectToDB();
    if (!$conn) {
        $return_val['result'] = false;
        $return_val['err'] = "*Connection to database failed.";
        return $return_val;
    }

    $sql = "SELECT f.*, u.avatar AS userAvatar FROM fundTransferHistory f
        LEFT JOIN
        users u ON u.name = '$username'
        WHERE username= '$username'";

    $result = $conn->query($sql);
    if (!$result) {
        $return_val['result'] = false;
        $return_val['err'] = "Error failed to get";
        return $return_val;
    }
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

case 'info':
    echo json_encode(getTransactionInfo());
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
    echo json_encode(showInvalidRequest("INVALID_TYPE $type"));
    break;
}

?>
