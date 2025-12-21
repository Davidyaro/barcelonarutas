<?php
$mysqli = new mysqli('127.0.0.1', 'root', '', '', 3306);
if ($mysqli->connect_error) die($mysqli->connect_error);

$r = $mysqli->query("SELECT @@port as port, @@hostname as host, @@version as version")->fetch_assoc();
echo "PHP->MySQL port={$r['port']} host={$r['host']} version={$r['version']}\n";

$res = $mysqli->query("SHOW DATABASES LIKE 'barcelonarutas'");
echo "DB exists from PHP? " . ($res->num_rows ? "YES" : "NO") . "\n";
