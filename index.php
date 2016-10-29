<?php
require_once 'person.php';

$db_hostname = 'localhost';
$db_username = 'root';
$db_password = '';
$db_database = 'very_simple_orm';

$firstname = sanitize($_POST['firstname']);
$lastname = sanitize($_POST['lastname']);
$age = sanitize($_POST['age']);
$city = sanitize($_POST['city']);
$height = sanitize($_POST['height']);
$sociotype[sanitize($_POST['sociotype'])] = 'selected';
$phone = sanitize($_POST['phone']);
$email = sanitize($_POST['email']);
$vk = sanitize($_POST['vk']);
$fam_stat = '';
$works = array();
$works_obj = array();
$hair_color = '';
$temp = '';
$browser = '';
$passport = array();
$soc = array();
$phys = array();

// Дохуїще перевірок на корректність введеної інформації
if (!empty($firstname)) if (!preg_match('/^[a-zA-Zа-яА-Я][a-zA-Zа-яА-Я\s]+/', $firstname)) $firstname_check = "Ім'я введене неправильно";
if (!empty($lastname)) if (!preg_match('/^[a-zA-Zа-яА-Я][a-zA-Zа-яА-Я\s]+/', $firstname)) $lastname_check = 'Прізвище введене неправильно';
if (!empty($city)) if (!preg_match('/^[a-zA-Zа-яА-Я][a-zA-Zа-яА-Я\s-]+/', $city)) $city_check = 'Назва міста введена неправильно';
if (!empty($phone)) if (!preg_match('/^\(0\d{2}\)\d{3}-\d{2}-\d{2}$/', $phone)) $phone_check = 'Введіть номер телефон згідно шаблону';
if (!empty($vk)) if(!preg_match('/^vk.com\/[a-zA-Z0-9_\.-]+/', $vk)) $vk_check = 'Введено невірний профіль';
if (!empty($age)) {
    if (!is_numeric($age)) $age_check = 'Вік має бут числом';
    else if ($age > 150 || $age < 0) $age_check =  'Ваш вік не може бути меншим 0, або більше 150(кому ви брешете?)';
}
if (!empty($height)) {
    if (!is_numeric($height)) $height_check = 'Ріст має бут числом';
    else if ($height > 250 || $height < 5) $height_check =  'Ваш ріст не може бути меншим 50 см, або більше 250 см(кому ви брешете?)';
}
if (!empty($email)) {
    if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9\._\-&!?=#]*@/', $email)) {
        $email_check = 'Ви ввели неправильну електронну адресу';
    } else {
        $domain = preg_replace('/^[a-zA-Z0-9][a-zA-Z0-9\._\-&!?=#]*@/', '', $email);
        if (!checkdnsrr($domain)) $email_check = 'Ви ввели неправильну електронну адресу';
    }
}

// Заповлення колонок, які вже були заповнені, щоб не вводити повторно в разі помилки
if(!empty($_POST['fam_stat'])) $fam_stat[sanitize($_POST['fam_stat'])] = 'checked';
if(!empty($_POST['hair_color'])) $hair_color[sanitize($_POST['hair_color'])] = 'checked';
if(!empty($_POST['temp'])) $temp[sanitize($_POST['temp'])] = 'checked';
if(!empty($_POST['browser'])) $browser[sanitize($_POST['browser'])] = 'checked';
if (!empty($_POST['works'])) {
    foreach ($_POST['works'] as $item) {
        $works[sanitize($item)] = 'checked';
    }
}




if (!empty($firstname) && !empty($lastname) && !empty($city) && !empty($phone) && !empty($vk) && !empty($age) && !empty($height) && !empty($email)
    && empty($firstname_check) && empty($lastname_check) && empty($city_check) && empty($phone_check) && empty($vk_check) && empty($age_check)
    && empty($height_check) && empty($email_check) && !empty($fam_stat) && !empty($hair_color) && !empty($temp) && !empty($browser) && !empty($works))
{
    foreach ($works as $key=>$item) $works_obj[] = $key;

    $passport = array(
        'firstname' => $firstname,
        'lastname' => $lastname,
        'age' => $age,
        'married' => key($fam_stat),
        'city' => $city,
        'works' => implode(' ', $works_obj),
        'vk' => $vk,
        'email' => $email,
        'phone' => preg_replace('/[\(\)\-]/', '', $phone));

    $soc = array(
        'sociotype' => key($sociotype),
        'temp' => key($temp),
        'browser' => key($browser));

    $phys = array(
        'hair_color' => key($hair_color),
        'height' => $height);

    $user = new Person($db_hostname, $db_username, $db_password, $db_database);

    $user->passport_data = $passport;
    $user->soc = $soc;
    $user->phys = $phys;

    $user->sql_push_user($db_hostname, $db_username, $db_password, $db_database);
}

echo <<<_END
<form action="initiate.php" method="post">
    <input type="text" size="24" name="database" placeholder="Придумай собі назву бази">
    <input type="submit" value="Ініціалізувати">
</form>
_END;

echo "<a href='admin.php'>Адміночка, таблиця з даними короч</a>";
// Виведення блядської форми
echo <<<_END
<form action="index.php" method="post"><pre>
Ім'я             <input type="text" name="firstname" value="$firstname" maxlength="32"> $firstname_check
Прізвище         <input type="text" name="lastname" value="$lastname" maxlength="32"> $lastname_check
Вік              <input type="text" name="age" value="$age"> $age_check
Місто проживання <input type="text" name="city" value="$city"> $city_check
Сімейний статус  <input type="radio" name="fam_stat" value="notmarried" {$fam_stat['notmarried']}> Неодружений<input type="radio" name="fam_stat" value="married" {$fam_stat['married']}> Одружений

Займані посади   <input type="checkbox" name="works[]" value="programmer" {$works['programmer']}> Програміст <input type="checkbox" name="works[]" value="freak" {$works['freak']}> Далбайоб
                 <input type="checkbox" name="works[]" value="grower" {$works['grower']}> Гровер     <input type="checkbox" name="works[]" value="narco" {$works['narco']}> Наркоман
                 
Колір волосся    <input type="radio" name="hair_color" value="black" {$hair_color['black']}> Чорний  <input type="radio" name="hair_color" value="brown" {$hair_color['brown']}> Коричневий
                 <input type="radio" name="hair_color" value="light" {$hair_color['light']}> Світлий <input type="radio" name="hair_color" value="other" {$hair_color['other']}> Інший
                 
Ріст             <input type="text" name="height" value="$height" > см $height_check
Соціотип         <select name="sociotype" size="1">
    <option {$sociotype['not chosen']} value="not chosen">Не обрано</option>
    <optgroup label="Альфа квадра">
        <option {$sociotype['donkihot']} value="donkihot">Дон Кихот</option>
        <option {$sociotype['duma']} value="duma">Дюма</option>
        <option {$sociotype['gugo']} value="gugo">Гюго</option>
        <option {$sociotype['rob']} value="rob">Робеспъер</option>
    </optgroup>
    <optgroup label="Бета квадра">
        <option {$sociotype['hamlet']} value="hamlet">Гамлет</option>
        <option {$sociotype['maks']} value="maks">Максим Горький</option>
        <option {$sociotype['zhukov']} value="zhukov">Жуков</option>
        <option {$sociotype['esenin']} value="esenin">Есенин</option>
    </optgroup>
    <optgroup label="Гамма квадра">
        <option {$sociotype['napoleon']} value="napoleon">Наполеон</option>
        <option {$sociotype['balzak']} value="balzak">Бальзак</option>
        <option {$sociotype['jack']} value="jack">Джек Лондон</option>
        <option value="dreiser">Драйзер</option>
    </optgroup>
    <optgroup label="Дельта квадра">
        <option {$sociotype['shtir']} value="shtir">Штирлиц</option>
        <option {$sociotype['dost']} value="dost">Достоевский</option>
        <option {$sociotype['huxley']} value="huxley">Гексли</option>
        <option {$sociotype['gaben']} value="gaben">Габен</option>
    </optgroup>
</select>
Тип темпераменту <input type="radio" name="temp" value="sang" {$temp['sang']}> Сангвиник  <input type="radio" name="temp" value="holer" {$temp['holer']}> Холерик
                 <input type="radio" name="temp" value="melan" {$temp['melan']}> Меланхолик <input type="radio" name="temp" value="flegm" {$temp['flegm']}> Флегматик

Улюблений браузер <input type="radio" name="browser" value="safari" {$browser['safari']}> Safari            <input type="radio" name="browser" value="chrome" {$browser['chrome']}> Google Chrome
                  <input type="radio" name="browser" value="ie" {$browser['ie']}> Internet Explorer <input type="radio" name="browser" value="firefox" {$browser['firefox']}> Mozilla Firefox

Номер телефону   <input type="text" name="phone" value="$phone" placeholder="(0xx)xxx-xx-xx"> $phone_check
Email            <input type="email" name="email" value="$email"> $email_check
Профіль VK       <input type="url" name="vk" value="$vk" placeholder="vk.com/xxxxxx"> $vk_check

<b>Всі дані мають бути обов'язково заповнені</b>

<input type="submit" value="Відправити">
</pre></form>
_END;

//Sanitizing data to prevent damage
function sanitize($var)
{
    $var = stripslashes($var);
    $var = htmlentities($var);
    $var = strip_tags($var);
    return $var;
}

