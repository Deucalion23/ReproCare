<?php
/**
 * Convert MySQL dump (reprocare.sql) to SQLite database
 */

$sqlFile = __DIR__ . '/../reprocare (1).sql';
if (!file_exists($sqlFile)) {
    $sqlFile = __DIR__ . '/../reprocare.sql';
}
$sqliteFile = __DIR__ . '/../database/reprocare_seed.sqlite';

if (!file_exists($sqlFile)) {
    die("reprocare.sql not found!\n");
}

if (file_exists($sqliteFile)) {
    unlink($sqliteFile);
}

$pdo = new PDO('sqlite:' . $sqliteFile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec('PRAGMA foreign_keys = OFF;');

$content = file_get_contents($sqlFile);

// Remove MySQL specific comments and settings
$content = preg_replace('/^--.*$/m', '', $content);
$content = preg_replace('/^\/\*!.*?\*\/;/m', '', $content);
$content = preg_replace('/SET\s+SQL_MODE.*?;\n/i', '', $content);
$content = preg_replace('/SET\s+time_zone.*?;\n/i', '', $content);
$content = preg_replace('/START\s+TRANSACTION;\n/i', '', $content);
$content = preg_replace('/COMMIT;\n/i', '', $content);

// Split statements by semicolon followed by optional whitespace and newline
$statements = preg_split('/;\s*$/m', $content);

$tablesCreated = 0;
$rowsInserted = 0;
$errors = [];

foreach ($statements as $stmt) {
    $stmt = trim($stmt);
    if (empty($stmt)) continue;

    // Check if CREATE TABLE
    if (preg_match('/^CREATE TABLE\s+[`"]?(\w+)[`"]?/i', $stmt, $m)) {
        $tableName = $m[1];
        
        $cleaned = $stmt;
        $cleaned = preg_replace('/AUTO_INCREMENT=\d+/i', '', $cleaned);
        $cleaned = preg_replace('/ENGINE=\w+/i', '', $cleaned);
        $cleaned = preg_replace('/DEFAULT\s+CHARSET=\w+/i', '', $cleaned);
        $cleaned = preg_replace('/COLLATE=\w+/i', '', $cleaned);
        $cleaned = preg_replace('/COLLATE\s+\w+/i', '', $cleaned);
        $cleaned = preg_replace('/CHARACTER\s+SET\s+\w+/i', '', $cleaned);
        $cleaned = preg_replace('/COMMENT\s*\'[^\']*\'/i', '', $cleaned);
        $cleaned = preg_replace('/current_timestamp\(\)/i', 'CURRENT_TIMESTAMP', $cleaned);
        $cleaned = preg_replace('/\bauto_increment\b/i', 'AUTOINCREMENT', $cleaned);
        $cleaned = preg_replace('/\bunsigned\b/i', '', $cleaned);
        $cleaned = preg_replace('/\bbigint\(\d+\)/i', 'INTEGER', $cleaned);
        $cleaned = preg_replace('/\bint\(\d+\)/i', 'INTEGER', $cleaned);
        $cleaned = preg_replace('/\btinyint\(\d+\)/i', 'INTEGER', $cleaned);
        $cleaned = preg_replace('/\bsmallint\(\d+\)/i', 'INTEGER', $cleaned);
        $cleaned = preg_replace('/\bmediumint\(\d+\)/i', 'INTEGER', $cleaned);
        $cleaned = preg_replace('/\benum\([^)]+\)/i', 'TEXT', $cleaned);
        $cleaned = preg_replace('/\blongtext\b/i', 'TEXT', $cleaned);
        $cleaned = preg_replace('/\bmediumtext\b/i', 'TEXT', $cleaned);
        $cleaned = preg_replace('/\bjson\b/i', 'TEXT', $cleaned);
        $cleaned = preg_replace('/ON\s+UPDATE\s+CURRENT_TIMESTAMP(\(\))?/i', '', $cleaned);
        $cleaned = preg_replace('/CHECK\s*\(\s*json_valid\([^)]*\)\s*\)/i', '', $cleaned);
        $cleaned = preg_replace('/\bdouble\b/i', 'REAL', $cleaned);
        $cleaned = preg_replace('/\bdecimal\(\d+,\s*\d+\)/i', 'REAL', $cleaned);

        // Remove KEY / INDEX / CONSTRAINT lines inside CREATE TABLE
        $lines = explode("\n", $cleaned);
        $cleanLines = [];
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (preg_match('/^(KEY|UNIQUE KEY|CONSTRAINT|FULLTEXT)\b/i', $trimmed)) {
                continue;
            }
            $cleanLines[] = $line;
        }
        $cleaned = implode("\n", $cleanLines);
        $cleaned = preg_replace('/,\s*\n\s*\)/m', "\n)", $cleaned);

        // SQLite: Handle PRIMARY KEY (`id`)
        if (preg_match('/PRIMARY KEY\s*\([`"]?id[`"]?\)/i', $cleaned)) {
            $cleaned = preg_replace('/PRIMARY KEY\s*\([`"]?id[`"]?\),?/i', '', $cleaned);
            $cleaned = preg_replace('/[`"]?id[`"]?\s+INTEGER(\s+NOT\s+NULL)?(\s+AUTOINCREMENT)?/i', '`id` INTEGER PRIMARY KEY AUTOINCREMENT', $cleaned);
            $cleaned = preg_replace('/,\s*\n\s*\)/m', "\n)", $cleaned);
        }

        try {
            $pdo->exec($cleaned);
            $tablesCreated++;
        } catch (Exception $e) {
            $errors[] = "Error creating $tableName: " . $e->getMessage() . "\nSQL: " . substr($cleaned, 0, 200);
        }
    }
    // Check if INSERT INTO
    elseif (preg_match('/^INSERT INTO\s+[`"]?(\w+)[`"]?/i', $stmt, $m)) {
        $cleaned = str_replace(['current_timestamp()', 'CURRENT_TIMESTAMP()'], 'CURRENT_TIMESTAMP', $stmt);
        
        // SQLite escape: replace \' with '' (except inside strings where it was escaped)
        // In MySQL dump, apostrophe is escaped as \'
        // In SQLite, it must be ''
        $cleaned = str_replace("\\'", "''", $cleaned);
        $cleaned = str_replace('\\"', '"', $cleaned);
        $cleaned = str_replace('\\r\\n', "\n", $cleaned);
        $cleaned = str_replace('\\n', "\n", $cleaned);

        try {
            $pdo->exec($cleaned);
            $rowsInserted++;
        } catch (Exception $e) {
            $errors[] = "Error inserting into {$m[1]}: " . $e->getMessage() . "\nSQL snippet: " . substr($cleaned, 0, 150);
        }
    }
}

echo "Conversion complete!\n";
echo "Tables created: $tablesCreated\n";
echo "Insert statements executed: $rowsInserted\n";
echo "Errors count: " . count($errors) . "\n";
if (!empty($errors)) {
    echo "Sample errors:\n" . implode("\n", array_slice($errors, 0, 5)) . "\n";
}
