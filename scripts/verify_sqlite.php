<?php
$pdo = new PDO('sqlite:' . __DIR__ . '/../database/reprocare_seed.sqlite');
$tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
echo "Total tables: " . count($tables) . "\n";
echo "Tables:\n" . implode(', ', $tables) . "\n\n";

echo "Check specific tables:\n";
foreach (['users', 'sessions', 'cache', 'checkups', 'pregnancies'] as $t) {
    echo "- $t: " . (in_array($t, $tables) ? 'EXISTS' : 'MISSING') . "\n";
}
