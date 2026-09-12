<?php
// db.php
$host = 'localhost';
$db_name = 'app_db';
$username = 'root'; // Ajustar según el entorno (ej: root)
$password = ''; // Ajustar según el entorno (ej: vacío en XAMPP)

try {
    $conn = new PDO("mysql:host=" . $host . ";dbname=" . $db_name, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $exception) {
    echo "Connection error: " . $exception->getMessage();
}
?>
