<?php
require_once __DIR__ . '/config/db.php';

$mysqldumpPath = 'C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysqldump.exe';

$backupDir = __DIR__ . '/storage';
if (!file_exists($backupDir)) {
    mkdir($backupDir, 0777, true);
}

$backupFileName = 'backup_' . $db . '_' . date("Y-m-d_H-i-s") . '.sql';
$backupFilePath = $backupDir . '/' . $backupFileName;

$command = "\"$mysqldumpPath\" --user=$user --password=$password --host=$host $db > \"$backupFilePath\"";

exec($command, $output, $result);

if ($result === 0 && file_exists($backupFilePath)) {
    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="' . $backupFileName . '"');
    readfile($backupFilePath);
} else {
    echo "Backup gagal! Error code: $result";
}
?>

// Setup Task Reschedule Windows
// Untuk menjadwalkan backup otomatis, Anda dapat menggunakan Task Scheduler di Windows.
// Beriku langkah-langkahnya:

// 1. Buka Task Scheduler.
// Cari "Task Scheduler" di menu Start dan buka aplikasinya.

// 2. Buat Task Baru.
// Klik "Create Basic Task" di panel sebelah kanan.
// Name : pdtbank_backup
// Description : Backup otomatis database pdtbank setiap hari.

// 3. Atur Trigger
// Pilih "Daily" untuk backup harian
// Start: atur 00:00 (misalkan ingin backup setiap tengah malam)
// Recur every: 1 days

// 4. Atur Action
// Pilih "Start a program".
// Program/script: C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysqldump.exe
// Add Arguments (optional): backup.php
// Start in (optional): C:\laragon\www\pdtbank\

// 5. Selesai
// Klik "Finish" untuk menyelesaikan pembuatan task.