<?php
include('database.php');
include('utility.php');

function getDonationList() {
    // Return value
    $return_val = array(
        'result' => true, // success
        'err'    => "",   // err msg
        'donations'  => Array()
    );

    $conn = connectToDB();
    if (!$conn) {
        $return_val['result'] = false;
        $return_val['err'] = "*Connection to database failed.";
        return $return_val;
    }

    $condition = "";
    if (isset($_POST["username"])) {
        $username = $_POST["username"];
        if ($username != "") {
            $condition = "WHERE u.name = '$username'";
        }
    }

    $sql = "SELECT d.*, u.avatar AS userAvatar FROM donations d
        LEFT JOIN
        users u ON d.username=u.name
        $condition";
        

    $result = $conn->query($sql);
    if (!$result) {
        $return_val['result'] = false;
        $return_val['err'] = "Error failed to get " . $conn->error;
        return $return_val;
    }
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            array_push($return_val['donations'], $row);
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
case 'list':
    echo json_encode(getDonationList());
    break;

default:
    echo json_encode(showInvalidRequest("INVALID_TYPE $type"));
    break;
}
?>
