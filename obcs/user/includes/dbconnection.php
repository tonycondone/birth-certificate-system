<?php
require_once __DIR__ . '/../../config.php';
$conn = new mysqli(
    env('DB_HOST', 'localhost'),
    env('DB_USER', 'root'),
    env('DB_PASS', '1212'),
    env('DB_NAME', 'obcs')
);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// DB credentials.
define('DB_HOST','localhost');
define('DB_USER','root');
define('DB_PASS','');
define('DB_NAME','obcsdb');
// Establish database connection.
try
{
$dbh = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME,DB_USER, DB_PASS,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
}
catch (PDOException $e)
{
exit("Error: " . $e->getMessage());
}
?>