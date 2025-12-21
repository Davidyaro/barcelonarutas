<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $mysqli = new mysqli('localhost', 'root', '', 'barcelonarutas', 3306);
    echo "OK: conexión correcta a MySQL y a la BD barcelonarutas";
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage();
}
