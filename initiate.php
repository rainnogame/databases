<?php

require_once 'person.php';

if(!empty($_POST['database'])) {
    $initiate = new Person($db_hostname, $db_username, $db_password, $db_database);
    echo $initiate->sql_initiate($_POST['database']);
}
else echo "Назву БД введи, Е!";