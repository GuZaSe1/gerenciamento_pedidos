<?php
$host = '';
$db   = '';
$user = '';
$pass = '';
$charset = '';

// Data Source Name (DSN) - Define a conexão
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// Tratamento de Erros
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Permite o uso do try-catch
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Erro de conexão" . $e->getMessage());

}
