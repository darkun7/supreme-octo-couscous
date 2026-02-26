<?php
use App\Models\GameCollection;
use App\Http\Livewire\ImportExport;

$collection = GameCollection::where('slug', 'skills')->first();
$component = new ImportExport();
$component->collection = $collection;
$response = $component->export();
ob_start();
$response->sendContent();
$content = ob_get_clean();
echo substr($content, 0, 500);
