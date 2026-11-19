<?php
$clave = "admin123";
$hash = password_hash($clave, PASSWORD_DEFAULT);
echo "<h2>Tu nuevo hash es:</h2>";
echo "<textarea rows='3' cols='80'>" . $hash . "</textarea>";
echo "<p>Copia ese texto y pégalo en tu base de datos.</p>";
?>