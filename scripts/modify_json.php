<?php

// Convert pet_spawn_areas.json from {area_id: [pet_ids]} to [{area_id, pet_ids}]
$path = storage_path('app/json/pet_spawn_areas.json');
$data = json_decode(file_get_contents($path), true);

$converted = [];
foreach ($data as $areaId => $petIds) {
    $converted[] = [
        'area_id' => $areaId,
        'pet_ids' => $petIds,
    ];
}

file_put_contents($path, json_encode($converted, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "✅ Converted pet_spawn_areas.json to flat array: " . count($converted) . " entries\n";
