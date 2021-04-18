#!/bin/bash

echo "--------------SERVER SETUP--------------------------"
echo "Enter your sql user name"
read sql_user

echo "Enter your sql password"
read sql_pass

echo "Enter a secret password of ur choice (will be used as webapp admin pass)"
read admin_pass

cat database.php | sed "s/my_fcuking_username/$sql_user/; s/my_fcuking_db_password/$sql_pass/; s/my_fcuking_admin_password/$admin_pass/" > temp.txt
rm database.php && mv temp.txt database.php
cat sql.sql | sed "s/my_fcuking_username/$sql_user/" > temp.txt
rm sql.sql && mv temp.txt sql.sql
