#!/usr/bin/env php
<?php
$backupDir = __DIR__ . '/../../backups/';
if (!is_dir($backupDir)) mkdir($backupDir, 0777, true);
$filename = $backupDir . 'cygnusx_' . date('Y-m-d_H-i-s') . '.sql';
exec("mysqldump -u root -p'' cygnusxstore > $filename 2>&1", $output, $return);
if ($return === 0) {
    array_map('unlink', glob($backupDir . '*.sql', GLOB_BRACE));
    echo "Backup successful: $filename\n";
} else {
    echo "Backup failed: " . implode("\n", $output) . "\n";
}
