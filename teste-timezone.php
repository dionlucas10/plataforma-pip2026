<?php
// Fuso atual do PHP
echo "PHP timezone: " . date_default_timezone_get() . "<br>";
echo "PHP agora: " . date('Y-m-d H:i:s') . "<br>";
echo "Server timezone (date): " . shell_exec('date') . "<br>";
echo "Server timezone (env): " . shell_exec('cat /etc/timezone') . "<br>";
echo "Server time (PHP): " . date('Y-m-d H:i:s') . "<br>";
echo "UTC agora: " . gmdate('Y-m-d H:i:s') . "<br>";
echo "Diferença UTC: " . date('P') . "<br>"; // ex: -03:00 ou +00:00

// Fuso do banco de dados
$config = require __DIR__ . '/app/config/db.php';
$pdo = new PDO(
    "mysql:host={$config['host']};dbname={$config['dbname']};port={$config['port']}",
    $config['user'], $config['pass']
);

$stmt = $pdo->query("SELECT @@global.time_zone, @@session.time_zone, NOW()");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo "MySQL global timezone: " . $row['@@global.time_zone'] . "<br>";
echo "MySQL session timezone: " . $row['@@session.time_zone'] . "<br>";
echo "MySQL NOW(): " . $row['NOW()'] . "<br>";
?>