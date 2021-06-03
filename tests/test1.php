<?php

include('../source/transactionManagement.php');
include('../source/userManagement.php');

$GLOBALS["db"] = "testDB";


function cleanup() {
    global $db;
    $conn = connectToDBEnhanced($db);

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

        $result = $conn->query($sql);
        if (!$result) {
            throw new Exception(TXT_SqlError($conn));
        }
    }
}

function displayTable($table) {
    echo "\nDisplaying $table\n";
    global $db;
    $conn = connectToDBEnhanced($db);

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

    echo "Test 1 complete cleaning up\n";
    cleanup();
}




function mainFunc() {
    cleanup();
    test1();
}

mainFunc();

?>
