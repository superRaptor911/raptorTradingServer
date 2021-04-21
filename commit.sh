#!/bin/bash

echo "Enter commit message => "
read msg

mv database.php database.php.old
cp logs/database.php database.php

git add . && git commit -m "$msg"
rm database.php
mv database.php.old database.php
