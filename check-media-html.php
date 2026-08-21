<?php
$html = file_get_contents('storage/app/media-debug.html');

// Check for any remaining inline JS that embeds raw paths
preg_match_all('/@click="[^"]*\$wire\.[a-zA-Z]+\([^)]*\)[^"]*"/', $html, $m1);
echo "Alpine @click with wire calls: " . count($m1[0]) . PHP_EOL;
foreach ($m1[0] as $x) {
    if (str_contains($x, "copyPath('") || str_contains($x, "deleteFile('") || str_contains($x, "selectFile('")) {
        echo "BROKEN inline: " . substr($x, 0, 150) . PHP_EOL;
    }
}

// Check wire:click with path args
preg_match_all('/wire:click="[^"]*\([^)]*\)[^"]*"/', $html, $m2);
echo "wire:click with parens: " . count($m2[0]) . PHP_EOL;
foreach ($m2[0] as $x) {
    if (str_contains($x, "copyPath('") || str_contains($x, "deleteFile('") || str_contains($x, "selectFile('")) {
        echo "BROKEN wire:click: " . substr($x, 0, 150) . PHP_EOL;
    }
}

// Check for x-data with js() that uses @js
preg_match_all('/x-data="\{[^}]*@js\(/', $html, $m3);
echo "x-data with @js: " . count($m3[0]) . PHP_EOL;

// Count copyPath usage
echo "copyPath count: " . substr_count($html, 'copyPath') . PHP_EOL;
echo "selectFile count: " . substr_count($html, 'selectFile') . PHP_EOL;
echo "deleteFile count: " . substr_count($html, 'deleteFile') . PHP_EOL;

echo "DONE" . PHP_EOL;
