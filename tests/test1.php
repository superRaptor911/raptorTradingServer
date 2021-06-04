<?php

include_once('../source/transactionManagement.php');
include_once('../source/userManagement.php');
include_once('../source/coinManagement.php');

$GLOBALS["database"] = "testDB";


function cleanup() {
    $conn = connectToDBEnhanced();

    $tables = array(
        "users",
        " transactions",
        " fundTransferHistory",
        " wallet",
        " coins",
        " userCoins",
        " investments",
        " donations"
    );

    foreach ($tables as $table) {
        $sql = "delete from $table";
        executeSql($conn, $sql);
    }

    $sql = "DROP TABLE userCoins";
    executeSql($conn, $sql);

    $sql = " CREATE TABLE userCoins (
            username varchar(64),
            FOREIGN KEY (username) REFERENCES users(name) ON DELETE CASCADE ON UPDATE CASCADE)";

    executeSql($conn, $sql);
}

function displayTable($table) {
    echo "\nDisplaying $table\n";
    $conn = connectToDBEnhanced();

    $sql = "select * from $table";
    $result = $conn->query($sql);
    if (!$result) {
        throw new Exception(TXT_SqlError($conn));
    }

    $data = array();

    while ($row = $result->fetch_assoc()) {
        array_push($data, $row);
    }

    var_dump($data);
}

// User Creation Test 1
function test1() {
    echo "Starting Test 1\n";
    echo "Creating Users 1\n";
    createUser("Raptor", "gggsadsad", "adkadkahdkjadkas");
    createUser("Killer", "gsadsad", "adkadkahdkjadkas");
    createUser("Bichoo", "asdgggsadsad", "adkadkahdkjadkas");

    displayTable("users");

    echo "\nChecking fund transfer";
    depositFund("raptor", 500, 10, 5);
    depositFund("raptor", 500, 0, 5);
    withdrawFund("raptor", 500, 0, 0);

    displayTable("wallet");
    displayTable("fundTransferHistory");

    echo "-------------------Test 1 complete cleaning up\n";
    cleanup();
}

function test2() {
    echo "Starting Test 2\n";
    createUser("Raptor", "Mp5 asas", "exess");

    addCoin("BITCOIN", "bit", "akaskhdkhad");
    addCoin("ether", "eth", "adsaddkaskhdkhad");
    addCoin("raptonium", "rpt", "akhad");

    depositFund("Raptor", 500, 0, 0);
    buyCoin("Raptor", "BITCOIN", 0.1, 500, 10); // 60
    buyCoin("Raptor", "BITCOIN", 0.2, 500, 10); // 110 + 60
    buyCoin("Raptor", "ether", 2, 50, 0); // 110 + 60 + 100
    sellCoin("Raptor", "ether", 1, 100, 10); // 110 + 60 + 100 -90

    withdrawFund("Raptor", 300, 10, 5);

    displayTable("transactions");
    displayTable("userCoins");
    displayTable("wallet");
}


function mainFunc() {
    cleanup();
    test1();
    test2();
}

mainFunc();

?>
