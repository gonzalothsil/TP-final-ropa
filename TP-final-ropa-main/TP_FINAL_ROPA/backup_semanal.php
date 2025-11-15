<?php
$backupDir = __DIR__ . "/backups";
if (!file_exists($backupDir)) mkdir($backupDir, 0777, true);

$dbHost = "localhost";
$dbUser = "root";
$dbPass = "";
$dbName = "pilchex_db";


$fecha = date("Y-m-d_H-i-s");
$backupFile = "$backupDir/backup_$fecha.sql";


$mysqldump = "C:\\xampp\\mysql\\bin\\mysqldump.exe";
$comando = "\"$mysqldump\" -u $dbUser " . ($dbPass ? "-p$dbPass " : "") . "$dbName > \"$backupFile\"";

// Ejecutar
system($comando, $resultado);

if ($resultado === 0) {
    echo "✅ Backup completado: $backupFile";
} else {
    echo "❌ Error al generar el backup.";
}
?>
