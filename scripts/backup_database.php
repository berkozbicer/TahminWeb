<?php

/**
 * Database Backup Script (PHP Version)
 * 
 * Windows veya PHP ile çalıştırılabilir versiyon
 * 
 * Kullanım:
 * php scripts/backup_database.php
 */

$projectPath = dirname(__DIR__);
$backupDir = $projectPath . '/storage/backups';
$retentionDays = 30;
$date = date('Ymd_His');
$envFile = $projectPath . '/.env';

// .env dosyasından database bilgilerini oku
if (!file_exists($envFile)) {
    die("Error: .env file not found\n");
}

$env = parse_ini_file($envFile);
$dbName = $env['DB_DATABASE'] ?? 'at_yarislari_tahmin';
$dbUser = $env['DB_USERNAME'] ?? 'root';
$dbPass = $env['DB_PASSWORD'] ?? '';
$dbHost = $env['DB_HOST'] ?? '127.0.0.1';

// Backup directory oluştur
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

$backupFile = $backupDir . "/backup_{$dbName}_{$date}.sql.gz";

// Database connection test
try {
    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName}",
        $dbUser,
        $dbPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("Error: Database connection failed - " . $e->getMessage() . "\n");
}

// mysqldump komutu oluştur
$command = sprintf(
    'mysqldump -h %s -u %s -p%s %s | gzip > %s',
    escapeshellarg($dbHost),
    escapeshellarg($dbUser),
    escapeshellarg($dbPass),
    escapeshellarg($dbName),
    escapeshellarg($backupFile)
);

// Backup al
echo "Starting backup...\n";
exec($command, $output, $returnVar);

if ($returnVar !== 0) {
    die("Error: Backup failed with return code {$returnVar}\n");
}

// Backup dosyası kontrolü
if (!file_exists($backupFile) || filesize($backupFile) === 0) {
    die("Error: Backup file is empty or missing\n");
}

echo "Backup completed: {$backupFile}\n";
echo "File size: " . number_format(filesize($backupFile) / 1024, 2) . " KB\n";

// Eski backup'ları sil
$files = glob($backupDir . "/backup_{$dbName}_*.sql.gz");
$cutoffTime = time() - ($retentionDays * 24 * 60 * 60);
$deletedCount = 0;

foreach ($files as $file) {
    if (filemtime($file) < $cutoffTime) {
        unlink($file);
        $deletedCount++;
    }
}

if ($deletedCount > 0) {
    echo "Deleted {$deletedCount} old backup(s) older than {$retentionDays} days\n";
}

echo "Backup process completed successfully!\n";


