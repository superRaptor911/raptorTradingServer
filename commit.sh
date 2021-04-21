#!/bin/bash

echo "Enter commit message => "
read msg

mv database.php logs/database.php.old
cp logs/database.php database.php

git add . && git commit -m "$msg"
rm database.php
mv logs/database.php.old database.php
