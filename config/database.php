<?php

$host = "localhost";
$dbname = "applytrack_db";
$username = "root";
$password = "";

try{
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

}catch(PDOEXCEPTION $e){
    error_log($e->getMessage());
    die("Database connection failed...");
}

?>