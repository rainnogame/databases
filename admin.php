<?php
require_once 'person.php';

$table = new Person($db_hostname, $db_username, $db_password, $db_database);
$table = $table->sql_pull();

if (!empty($table)) {
    // Problem: Не писать логику вывода html в php... должно быть наоборот.
    // Remark: для того, чтобы вывести все, что угодно в php можно использовать синтаксис:
    // ================================================== //
    ?>
        Тут пишем то, что хотим вывести в html синтаксисе
    <?
    // все, что будет между закрытым и открытым тегом обработается как через "echo"
    // ================================================== //
    echo '<table border="1" cellspacing="2" width="100%">';

    echo '<tr>';
    foreach ($table[0] as $key => $item) {
        echo "<th>$key</th>";
    }
    echo '</tr>';

    foreach ($table as $item) {
        echo '<tr>';
        foreach ($item as $value) {
            echo "<td>$value</td>";
        }
        echo '</tr>';
    }

    echo '</table>';
}
//echo '<pre>';
//print_r($table);
//echo '</pre>';
