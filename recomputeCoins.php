<?php
include('database.php');
include('utility.php');

echo "Computing userCoin table\n";
$conn = connectToDB();
if (!$conn) {
    echo "*Connection to database failed.";
    exit();
}

$userCoinTable = array();
$userInvestmentTable = array();

$sql = "SELECT t.*, c.id AS coinId FROM transactions t
    LEFT JOIN
        coins c ON t.coin=c.name
    WHERE t.transStatus = 1
    GROUP BY t.id";


$result = $conn->query($sql);

if (!$result) {
    echo "Error::" . $conn->error;
    exit();
}
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $user = $row["username"];
        $coin = $row["coinId"];
        $coinCount = $row["coinCount"];
        $coinCost = $row["cost"];

        if (!isset($userCoinTable["$user"])) {
            $userCoinTable["$user"] = array();
        }
        if (!isset($userCoinTable["$user"]["$coin"])) {
            $userCoinTable["$user"]["$coin"] = 0;
        }
        $userCoinTable["$user"]["$coin"] += $coinCount;


        if (!isset($userInvestmentTable["$user"])) {
            $userInvestmentTable["$user"] = 0;
        }
        $userInvestmentTable["$user"] += $coinCount * $coinCost;
    }
}

echo "\nCoinTable\n";
var_dump($userCoinTable);
echo "\nInvestmentTable\n";
var_dump($userInvestmentTable);

$sql = "DELETE FROM userCoins";
$result = $conn->query($sql);
if (!$result) {
    echo "Error::" . $conn->error;
    exit();
}

foreach($userCoinTable as $user => $coins) {
    $sql_1 = "INSERT INTO userCoins(username ";
    $sql_2 = " VALUES('$user'";

    foreach($coins as $coin => $value) {
        $sql_1 = $sql_1 . ", $coin";
        $sql_2 = $sql_2 . ", '$value'";
    }
    $sql_1 = $sql_1 . ")";
    $sql_2 = $sql_2 . ")";
    $sql = $sql_1 . $sql_2;

    echo "\n\n$sql";
    $result = $conn->query($sql);
    if (!$result) {
        echo "Error::" . $conn->error;
        exit();
    }
}

echo "\n\n----Done------\n\n";

$sql = "DELETE FROM investments";
$result = $conn->query($sql);
if (!$result) {
    echo "Error::" . $conn->error;
    exit();
}

foreach($userInvestmentTable as $user => $investment) {
    $sql = "INSERT INTO investments(username , investment)
            VALUES ('$user', $investment)";

    echo "\n\n$sql";
    $result = $conn->query($sql);
    if (!$result) {
        echo "Error::" . $conn->error;
        exit();
    }
}

echo "\n\n----Done------";
?>
