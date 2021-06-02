#!/bin/bash

echo "--------------SERVER SETUP--------------------------"
echo "Enter your sql user name"
read sql_user

echo "Enter your sql password"
read sql_pass

echo "Enter a secret password of ur choice (will be used as webapp admin pass)"
read admin_pass


echo "
<?php
    
function getSqlUsername() {
    return \"$sql_user\";
}

function getSqlPassword() {
    return \"$sql_pass\";
}

function getAdminPassword() {
    return \"$admin_pass\";
}

?>
" > secret.php
