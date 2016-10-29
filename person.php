<?php

require_once 'login.php';

// Problem: Файл, который содержит клас должен иметь такое-же название.
// Theory: Найти стандарты написание кода РHP
class Person
{
    // Problem: Дублирование переменных "index.php:4"
    // Theory: https://refactoring.guru/ru/smells/duplicate-code
    // Decision: Вынести подключения в отдельную сущность (файл)
    public $db_hostname;
    public $db_username;
    public $db_password;
    public $db_database;
    
    // Remark: Нет ничего плохого в том, что имена переменных будут немного длиннее, но понятней
    // Remark: Принято или id или <table-name>_id (user_id (таблица user), person_id (таблица person))
    // Remark: + 1-й вариант - сразу видно поле PRIMARY_KEY (ключи одинаковы)
    // Remark: + 2-й вариант - удобно соотносить таблицы, если есть FOREIGN_KEY (имя публичного и форинг ключа одинаковы)
    public $uniq_id;
    public $soc_id;
    public $phys_id;
    
    // Remark: Инициализация данных в описании класса - плохой тон.
    public $passport_data = [
        'firstname' => '',
        'lastname'  => '',
        'age'       => '',
        'married'   => '',
        'city'      => '',
        'works'     => '',
        'vk'        => '',
        'email'     => '',
        'phone'     => '',
    ];
    
    // Remark: Если что-то сокращаешь на одном уровне - то сокращай или все или ничего... Иначе выглядит очень нелогично. (будто данные разного назначения)
    public $soc = [
        // Remark: "temp" ("tmp","stub","service", ... ) - это негасно зарезервированные слова практически для каждого языка
        // temp, tmp - временные данные
        // service - клас - сервис (глобальный клас)
        // stub - объект-заглушка
        // Remark: имя должно отображать назначене переменной: browser - посто браузер (непонятно, как он впринципе относится к Person)
        'temp'      => '',
        'sociotype' => '',
        'browser'   => '',
    ];
    public $phys = [
        'hair_color' => '',
        'height'     => '',
    ];
    
    // Remark: аналогично, только еще хуже... любые абстрактные названия практически всегда противопоказаны
    // Поле "таблица" - ничего не значит. Тот, кто будет править твой код тебя возненавидет.
    public $table = [];
    
    // Problem: Ошибочное применение конструктора
    // Theory: Конструктор нужен для иницализации объекта (прямо или через другие методы)/запуска первоочередных методов.
    // Передача доступов в объект - следствие крайне плохой архитектуры (см. дальше)
    public function __construct($db_hostname, $db_username, $db_password, $db_database)
    {
        $this->db_hostname = $db_hostname;
        $this->db_username = $db_username;
        $this->db_password = $db_password;
        $this->db_database = $db_database;
    }
    
    public function sql_push_user()
    {
        // Problem: Создание нового экзампляра "mysqli" при каждом добалении пользователя в то время, когда можно для всего изспользовать только один.
        // Нерационально используются ресурсы.
        // Theory: Прочитать про Singleton. (понять зачем его использовать (пока не думать над тем, почему он хреновый (а он хреновый!!!))
        $connection = new mysqli($this->db_hostname, $this->db_username, $this->db_password, $this->db_database);
        if ($connection->connect_error) return $connection->connect_error;
        
        //  Вставка в social з перевіркою на повтори
        $query = "SELECT soc_id FROM social WHERE temp='{$this->soc['temp']}' AND sociotype='{$this->soc['sociotype']}' "
            . "AND browser='{$this->soc['browser']}'";
        $result = $connection->query($query);
        if (!$result) return $connection->error;
        if ($x = $result->fetch_assoc()['soc_id']) {
            $this->soc_id = $x;
        } else {
            $query = "INSERT INTO social (sociotype, temp, browser) VALUES ('{$this->soc['sociotype']}', "
                . "'{$this->soc['temp']}', '{$this->soc['browser']}')";
            $result = $connection->query($query);
            if (!$result) return $connection->error;
            $this->soc_id = $connection->insert_id;
        }
        
        // Problem: Запросы принято выносить в константы класса. (в данном контексте)
        // Theory: По факту запросы вообще не должны фигурировать в бизнес-логике. Но это уже дебри паттернов.
        //  Вставка в physical з перевіркою на повтори
        $query = "SELECT phys_id FROM physical WHERE hair_color ='{$this->phys['hair_color']}' AND height='{$this->phys['height']}'";
        $result = $connection->query($query);
        // Remark: Всегда используй длинный синтаксис if (expr){}. Вместе со всеми скобками (если что-то забудешь - долго искать)
        // Remark: Старайся делать, чтобы в функции был только один return.
        if (!$result) return $connection->error;
        // Remark: Присваивание в if - красиво, но лучше написать лишнюю строку чем потом завтыкать.
        if ($x = $result->fetch_assoc()['phys_id']) {
            $this->phys_id = $x;
        } else {
            $query = "INSERT INTO physical (hair_color, height) VALUES ('{$this->phys['hair_color']}', '{$this->phys['height']}')";
            $result = $connection->query($query);
            if (!$result) return $connection->error;
            $this->phys_id = $connection->insert_id;
        }
        
        // Problem: Защита от инекций.
        // Theory: Prepared statements - очень нужно, основа всех ORM
        $query = "INSERT INTO users (firstname, lastname, age, married, city, works, vk, email, phone, soc_id, phys_id) "
            . "VALUES('{$this->passport_data['firstname']}', '{$this->passport_data['lastname']}', '{$this->passport_data['age']}', "
            . "'{$this->passport_data['married']}', '{$this->passport_data['city']}', '{$this->passport_data['works']}', "
            . "'{$this->passport_data['vk']}', '{$this->passport_data['email']}', '{$this->passport_data['phone']}', "
            . "'$this->soc_id', '$this->phys_id')";
        $result = $connection->query($query);
        if (!$result) return $connection->error;
        
        $this->uniq_id = $connection->insert_id;
        
        // Problem: Закрытие и открытие соединений всегда требует обработки ошибок!!!!! try/catch (так как всегда сервер может просто не работать)
        // Theory: try/catch - понять, когда нужно обрабатывать ошибки  так, а когда ошибки - это часть логики программы.
        $connection->close();
        // Problem: Говнокод. Возвращаемое значение должно быть унифицированным. 0 - ничего не значит.
        return 0;
    }
    
    public function sql_pull()
    {
        $connection = new mysqli($this->db_hostname, $this->db_username, $this->db_password, $this->db_database);
        if ($connection->connect_error) return $connection->connect_error;
        
        $query = "SELECT users.*, social.sociotype, social.temp, social.browser, physical.hair_color, physical.height "
            . "FROM users INNER JOIN social USING (soc_id) "
            . "INNER JOIN physical USING (phys_id)";
        $result = $connection->query($query);
//        echo '<pre>';
//        print_r($connection);
//        echo '</pre>';
        if (!$result) return $connection->error;
        
        $rows = $result->num_rows;
        
        for ($j = 0; $j < $rows; $j++) {
            $result->data_seek($j);
            $this->table[] = $result->fetch_assoc();
        }
        $connection->close();
        return $this->table;
    }
    
    public function sql_initiate($db_name)
    {
        $connection = new mysqli($this->db_hostname, $this->db_username, $this->db_password);
        if ($connection->connect_error) return $connection->connect_error;
        
        // Remark: есть .sql файлы... которые можно загружать и выполнять в PHP.
        // Problem: Опять же работа с базой - не в классе Person
        
        $query = <<<_END

CREATE DATABASE IF NOT EXISTS `$db_name` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `$db_name`;

CREATE TABLE IF NOT EXISTS `physical` (
  `phys_id` smallint(6) NOT NULL,
  `hair_color` char(5) DEFAULT NULL,
  `height` smallint(5) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `social` (
  `soc_id` smallint(6) NOT NULL,
  `sociotype` varchar(10) DEFAULT NULL,
  `temp` varchar(10) DEFAULT NULL,
  `browser` varchar(7) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `users` (
  `uniq_id` smallint(5) unsigned NOT NULL,
  `firstname` varchar(32) DEFAULT NULL,
  `lastname` varchar(32) DEFAULT NULL,
  `age` tinyint(3) unsigned DEFAULT NULL,
  `married` varchar(10) DEFAULT NULL,
  `city` varchar(32) DEFAULT NULL,
  `works` varchar(30) DEFAULT NULL,
  `vk` varchar(60) DEFAULT NULL,
  `email` varchar(60) DEFAULT NULL,
  `phone` char(10) DEFAULT NULL,
  `soc_id` smallint(6) DEFAULT NULL,
  `phys_id` smallint(6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `physical`
  ADD PRIMARY KEY (`phys_id`);

ALTER TABLE `social`
  ADD PRIMARY KEY (`soc_id`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`uniq_id`),
  ADD KEY `users_social_soc_id_fk` (`soc_id`),
  ADD KEY `users_physical_phys_id_fk` (`phys_id`);


ALTER TABLE `physical`
  MODIFY `phys_id` smallint(6) NOT NULL AUTO_INCREMENT;

ALTER TABLE `social`
  MODIFY `soc_id` smallint(6) NOT NULL AUTO_INCREMENT;

ALTER TABLE `users`
  MODIFY `uniq_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT;

ALTER TABLE `users`
  ADD CONSTRAINT `users_physical_phys_id_fk` FOREIGN KEY (`phys_id`) REFERENCES `physical` (`phys_id`),
  ADD CONSTRAINT `users_social_soc_id_fk` FOREIGN KEY (`soc_id`) REFERENCES `social` (`soc_id`);
_END;
        $result = $connection->multi_query($query);
        if (!$result) return $connection->error;
        
        return 'База ініціалізована, підр! Не забудь ввести її в login.php!!!!';
    }
}

// Remark: КОМЕНТАРИИ ПО БАЗЕ ДАННЫХ
