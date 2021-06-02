<?php

function getRandomString($n) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';

    for ($i = 0; $i < $n; $i++) {
        $index = rand(0, strlen($characters) - 1);
        $randomString .= $characters[$index];
    }

    return $randomString;
}

function sendMail($from, $to, $subject, $message) {
    $headers = "From:" . $from;
    mail($to,$subject,$message, $headers);
}

function isPostRequredValuesSet($requiredValues) {
    foreach ($requiredValues as $value) {
        if (!isset($_POST["$value"])) {
            return false;
        }
    }
    return true;
}

?>
