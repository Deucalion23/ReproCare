<?php
$lines = file(__DIR__ . '/../reprocare (1).sql');
foreach ($lines as $i => $l) {
    if (strpos($l, 'TABLE `users`') !== false) {
        echo ($i + 1) . ": " . $l;
        for ($j = 1; $j <= 45; $j++) {
            if (isset($lines[$i + $j])) {
                echo $lines[$i + $j];
            }
        }
        break;
    }
}
