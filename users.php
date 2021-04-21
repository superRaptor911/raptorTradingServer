<?php
include('database.php');
include('utility.php');

// Function to register user
function registerUser() {
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
    $email = $_POST["email"];

    $hash = $_POST['hash'];
    if (!verifyUser($hash)) {
        $return_val['result'] = false;
        $return_val['err'] = "Permission Denied";
        return $return_val;
    }

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

    $sql = "INSERT INTO users(name, avatar, email)
        VALUES('$name', '$avatar', '$email')";

    if (!$conn->query($sql)) {
        $return_val['result'] = false;
        $return_val['err'] = "*Please select a different user name or email is already in use";
        $logger->addLog(__FUNCTION__, "Registration Failed", '-');
        $logger->addLog(__FUNCTION__, $conn->error);
        return $return_val;
    }

    $logger->addLog(__FUNCTION__, "Registration success : User $name was created.");
    /* $_POST["email"] = $email; */
    /* sendOTP(); */
    return $return_val;
}


function createTables($username) {
    // Return value
    $return_val = array(
        'result' => true, // success
        'err'    => "",   // err msg
    );

    $conn = connectToDB();
    if (!$conn) {
        $return_val['result'] = false;
        $return_val['err'] = "*Connection to database failed.";
        return $return_val;
    }

    $sql = "INSERT INTO wallet(username, amount) VALUES('$username', 0)";
    if (!$conn->query($sql)) {
        $return_val['result'] = false;
        $return_val['err'] = "Error :" . $conn->error;
        return $return_val;
    }

    $sql = "INSERT INTO userCoins(username) VALUES('$username')";
    if (!$conn->query($sql)) {
        $return_val['result'] = false;
        $return_val['err'] = "Error :" . $conn->error;
        return $return_val;
    }

    $sql = "INSERT INTO investments(username) VALUES('$username')";
    if (!$conn->query($sql)) {
        $return_val['result'] = false;
        $return_val['err'] = "Error :" . $conn->error;
        return $return_val;
    }
    return $return_val;
}

function getUserInfo() {
    // Return value
    $return_val = array(
        'result' => true, // success
        'err'    => "",   // err msg
        'userInfo'  => array()
    );
    if (empty($_POST["name"])) {
        $return_val['result'] = false;
        $return_val['err'] = "*name not set";
        return $return_val;
    }
    $name = $_POST["name"];

    $conn = connectToDB();
    if (!$conn) {
        $logger = new Logger();
        $logger->addLog(__FUNCTION__, "*Connection to database failed.");
        $return_val['result'] = false;
        $return_val['err'] = "*Connection to database failed.";
        return $return_val;
    }
    // SQL DB
    $sql = "SELECT * FROM users WHERE name='$name'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $return_val['userInfo'] = $row;
    }
    return $return_val;
}

// Function to verify user using name and pass
function authuorizeUser() {
    // Return value
    $return_val = array(
        'result' => true, // success
        'err'    => ""   // err msg
    );

    $name = $_POST["name"];
    $hash = $_POST["hash"];
    $return_val['result'] = verifyUser($name, $hash);
    return $return_val;
}

function sendOTP() {
    // Return value
    $return_val = array(
        'result' => true, // success
        'err'    => "",   // err msg
        'msg'    => 'OTP SENT'
    );

    $email = $_POST["email"];

    $conn = connectToDB();
    if (!$conn) {
        $logger = new Logger();
        $logger->addLog(__FUNCTION__, "*Connection to database failed.");
        $return_val['result'] = false;
        $return_val['err'] = "*Connection to database failed.";
        return $return_val;
    }

    $otp = getRandomString(6);

    sendMail("raptor.inc2018@gmail.com", $email, "Hackclub Cucek OTP", "Your OTP : $otp");

    $sql = "INSERT INTO otp(email, otp) VALUES('$email', '$otp') 
        ON DUPLICATE KEY UPDATE otp='$otp'";
    $result = $conn->query($sql);
    if (!$result) {
        $return_val['result'] = false;
        $return_val['err'] = "SQL FAILED: " . $conn->error;
        return $return_val;
    }
    return $return_val;
}


function getUserList() {
    // Return value
    $return_val = array(
        'result' => true, // success
        'err'    => "",   // err msg
        'users'  => Array()
    );

    $conn = connectToDB();
    if (!$conn) {
        $return_val['result'] = false;
        $return_val['err'] = "*Connection to database failed.";
        return $return_val;
    }

    $sql = "SELECT * FROM users";
    $result = $conn->query($sql);
    if (!$result) {
        $return_val['result'] = false;
        $return_val['err'] = "Failed to get user list";
        return $return_val;
    }
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
           array_push($return_val['users'], $row);
        }
    }

    return $return_val;
}

function updateUser() {
    // Return value
    $return_val = array(
        'result' => true, // success
        'err'    => "",   // err msg
    );

    $oldName = $_POST["oldName"];
    $name = $_POST["name"];
    $avatar = $_POST["avatar"];
    $email = $_POST["email"];

    $hash = $_POST['hash'];
    if (!verifyUser($hash)) {
        $return_val['result'] = false;
        $return_val['err'] = "Permission Denied";
        return $return_val;
    }

    $conn = connectToDB();
    if (!$conn) {
        $logger = new Logger();
        $logger->addLog(__FUNCTION__, "*Connection to database failed.");
        $return_val['result'] = false;
        $return_val['err'] = "*Connection to database failed.";
        return $return_val;
    }

    // SQL DB
    $sql = "UPDATE users 
        SET 
            name = '$name',
            email = '$email',
            avatar = '$avatar'
        WHERE
            name = '$oldName'";

    $result = $conn->query($sql);
    if (!$result) {
        $return_val['result'] = false;
        $return_val['err'] = "SQL FAILED: " . $conn->error;
        return $return_val;
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
case 'register':
    echo json_encode(registerUser());
    break;

case 'info':
    echo json_encode(getUserInfo());
    break;

case 'auth':
    echo json_encode(authuorizeUser());
    break;

case 'list':
    echo json_encode(getUserList());
    break;

case 'update':
    echo json_encode(updateUser());
    break;

default:
    echo json_encode(showInvalidRequest("INVALID_TYPE $type"));
    break;
}

?>
