<?php

// Remove 'Value' and 'Effect' keys from equipment.json

$path = storage_path('app/json/equipment.json');
$data = json_decode(file_get_contents($path), true);

$count = 0;
foreach ($data as $key => &$entry) {
    unset($entry['Value'], $entry['Effect']);
    $count++;
}

file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "✅ Removed 'Value' & 'Effect' from {$count} entries in equipment.json\n";
